<?php

namespace Drupal\cas_attributes\Subscriber;

use Drupal\cas\Event\CasPostLoginEvent;
use Drupal\cas\Event\CasPreLoginEvent;
use Drupal\cas\Event\CasPreRegisterEvent;
use Drupal\cas_attributes\Form\CasAttributesSettings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\cas\Service\CasHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a CasAttributesSubscriber.
 */
class CasAttributesSubscriber implements EventSubscriberInterface {

  /**
   * Settings object for CAS attributes.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $settings;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenService;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory to get module settings.
   * @param \Drupal\Core\Utility\Token $token_service
   *   The token service for token replacement.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack to get the current request.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token_service, RequestStack $request_stack) {
    $this->settings = $config_factory->get('cas_attributes.settings');
    $this->tokenService = $token_service;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CasHelper::EVENT_PRE_REGISTER][] = ['onPreRegister', -1];
    $events[CasHelper::EVENT_PRE_LOGIN][] = ['onPreLogin', 20];
    $events[CasHelper::EVENT_POST_LOGIN][] = ['onPostLogin'];
    return $events;
  }

  /**
   * Subscribe to the CasPreRegisterEvent.
   *
   * @param \Drupal\cas\Event\CasPreRegisterEvent $event
   *   The CasPreAuthEvent containing property information.
   */
  public function onPreRegister(CasPreRegisterEvent $event) {
    if ($this->settings->get('field.sync_frequency') !== CasAttributesSettings::SYNC_FREQUENCY_NEVER) {
      // Map fields.
      $field_mappings = $this->getFieldMappings($event->getCasPropertyBag()->getAttributes());
      if (!empty($field_mappings)) {
        $event->setPropertyValues($field_mappings);
      }
    }

    if ($this->settings->get('role.sync_frequency') !== CasAttributesSettings::SYNC_FREQUENCY_NEVER) {
      // Map roles.
      $roleMappingResults = $this->doRoleMapCheck($event->getCasPropertyBag()->getAttributes());

      if (empty($roleMappingResults['add']) && $this->settings->get('role.deny_registration_no_match')) {
        $event->setAllowAutomaticRegistration(FALSE);
      }
      else {
        // Add/remove roles from the user.
        $existingProperties = $event->getPropertyValues();
        $rolesForUser = [];
        if (!empty($existingProperties['roles'])) {
          $rolesForUser = $existingProperties['roles'];
        }

        $rolesForUser = array_diff($rolesForUser, $roleMappingResults['remove']);
        $rolesForUser = array_merge($rolesForUser, $roleMappingResults['add']);
        $rolesForUser = array_unique($rolesForUser);
        $event->setPropertyValue('roles', $rolesForUser);
      }
    }
  }

  /**
   * Subscribe to the CasPreLoginEvent.
   *
   * @param \Drupal\cas\Event\CasPreLoginEvent $event
   *   The CasPreAuthEvent containing account and property information.
   */
  public function onPreLogin(CasPreLoginEvent $event) {
    $account = $event->getAccount();

    // Map fields.
    if ($this->settings->get('field.sync_frequency') === CasAttributesSettings::SYNC_FREQUENCY_EVERY_LOGIN) {
      $field_mappings = $this->getFieldMappings($event->getCasPropertyBag()->getAttributes());
      if (!empty($field_mappings)) {
        // If field already has data, only set new value if configured to
        // overwrite existing data.
        $overwrite = $this->settings->get('field.overwrite');
        foreach ($field_mappings as $field_name => $field_value) {
          if ($overwrite || empty($account->get($field_name))) {
            $account->set($field_name, $field_value);
          }
        }
      }
    }

    // Map roles.
    $roleMappingResults = $this->doRoleMapCheck($event->getCasPropertyBag()->getAttributes());
    if ($this->settings->get('role.sync_frequency') === CasAttributesSettings::SYNC_FREQUENCY_EVERY_LOGIN) {
      foreach ($roleMappingResults['remove'] as $rid) {
        $account->removeRole($rid);
      }
      foreach ($roleMappingResults['add'] as $rid) {
        $account->addRole($rid);
      }
    }

    if (empty($roleMappingResults['add']) && $this->settings->get('role.deny_login_no_match')) {
      $event->setAllowLogin(FALSE);
    }
  }

  /**
   * Map fields to the pre-defined CAS token values.
   *
   * @param array $casAttributes
   *   The list of CAS attributes for the user logging in.
   */
  protected function getFieldMappings(array $casAttributes) {
    $mappings = unserialize($this->settings->get('field.mappings'));
    if (empty($mappings)) {
      return [];
    }

    $field_data = [];

    foreach ($mappings as $field_name => $attribute_token) {
      $result = trim($this->tokenService->replace(
        $attribute_token,
        ['cas_attributes' => $casAttributes],
        ['clear' => TRUE]
      ));
      $result = html_entity_decode($result);

      // Only update the fields if there is data to set.
      if (!empty($result)) {
        $field_data[$field_name] = $result;
      }
    }

    return $field_data;
  }

  /**
   * Determine which roles should be added/removed based on attributes.
   *
   * @param array $attributes
   *   The attributes associated with the user.
   *
   * @return array
   *   Array containing two keys:
   *   - add: the list of RIDs to add to the user
   *   - remove: the list of RIDs to remove from the user
   */
  protected function doRoleMapCheck(array $attributes = NULL) {
    $role_map = unserialize($this->settings->get('role.mappings'));
    if (empty($role_map)) {
      return [
        'add' => [],
        'remove' => [],
      ];
    }

    $rolesToAdd = [];
    $rolesToRemove = [];

    foreach ($role_map as $condition) {
      // Attribute not found; don't map role.
      if (!isset($attributes[$condition['attribute']])) {
        continue;
      }
      $attributeValue = $attributes[$condition['attribute']];
      if (!is_array($attributeValue)) {
        $attributeValue = [$attributeValue];
      }
      $valueToMatch = $condition['value'];

      $matched = FALSE;
      switch ($condition['method']) {
        case 'exact_single':
          $matched = $this->checkRoleMatchExactSingle($attributeValue, $valueToMatch);
          break;

        case 'exact_any':
          $matched = $this->checkRoleMatchExactAny($attributeValue, $valueToMatch);
          break;

        case 'contains_any':
          $matched = $this->checkRoleMatchContainsAny($attributeValue, $valueToMatch);
          break;

        case 'regex_any':
          $matched = $this->checkRoleMatchRegexAny($attributeValue, $valueToMatch);
        default:
      }

      if ($matched) {
        $rolesToAdd[] = $condition['rid'];
      }
      elseif ($condition['remove_without_match']) {
        $rolesToRemove[] = $condition['rid'];
      }
    }

    return [
      'add' => $rolesToAdd,
      'remove' => $rolesToRemove,
    ];
  }

  /**
   * Check if attributes match using the 'exact_single' method.
   *
   * This method checks that the the attribute values match exactly.
   * The attribute is expected to be a single value, and not a multi value
   * attribute.
   *
   * @param array $attributeValue
   *   The actual attribute value.
   * @param string $valueToMatch
   *   The attribute value to compare against.
   *
   * @return bool
   *   TRUE if there's a match, FALSE otherwise.
   */
  protected function checkRoleMatchExactSingle(array $attributeValue, $valueToMatch) {
    // The expectation for this method is that the attribute is not multi-value.
    if (count($attributeValue) > 1) {
      return FALSE;
    }

    $value = array_shift($attributeValue);
    if ($value === $valueToMatch) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if attributes match using the 'exact_any' method.
   *
   * This is the same as the 'exact_single' method, except it will check if any
   * of the elements in a multi-value attribute match the expected value
   * exactly.
   *
   * @param array $attributeValue
   *   The actual attribute value.
   * @param string $valueToMatch
   *   The attribute value to compare against.
   *
   * @return bool
   *   TRUE if there's a match, FALSE otherwise.
   */
  protected function checkRoleMatchExactAny(array $attributeValue, $valueToMatch) {
    foreach ($attributeValue as $value) {
      if ($value === $valueToMatch) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Check if attributes match using the 'contains_any' method.
   *
   * Works by checking if any item in attribute value contains the value to
   * match as a substring.
   *
   * @param array $attributeValue
   *   The actual attribute value.
   * @param string $valueToMatch
   *   The attribute value to compare against.
   *
   * @return bool
   *   TRUE if there's a match, FALSE otherwise.
   */
  protected function checkRoleMatchContainsAny(array $attributeValue, $valueToMatch) {
    foreach ($attributeValue as $value) {
      if (strpos($value, $valueToMatch) !== FALSE) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Check if attribuets match using the 'regex_any' method.
   *
   * Each item in attribute array is checked with a regex.
   *
   * @param array $attributeValue
   *   The actual attribute value.
   * @param string $regex
   *   The regular expression pattern.
   *
   * @return bool
   *   TRUE if there's a match, FALSE otherwise.
   */
  protected function checkRoleMatchRegexAny(array $attributeValue, $regex) {
    foreach ($attributeValue as $value) {
      if (@preg_match($regex, $value)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Save attributes to user session if sitewide token support is enabled.
   *
   * @param \Drupal\cas\Event\CasPostLoginEvent $casPostLoginEvent
   *   The post login event from CAS.
   */
  public function onPostLogin(CasPostLoginEvent $casPostLoginEvent) {
    if ($this->settings->get('sitewide_token_support')) {
      $session = $this->requestStack->getCurrentRequest()->getSession();
      $session->set('cas_attributes', $casPostLoginEvent->getCasPropertyBag()->getAttributes());
    }
  }

}
