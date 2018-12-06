<?php

namespace Drupal\cas_attributes\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\user\RoleInterface;
use Drupal\Core\Url;

/**
 * CAS Attributes settings form.
 */
class CasAttributesSettings extends ConfigFormBase {

  const SYNC_FREQUENCY_NEVER = 0;
  const SYNC_FREQUENCY_INITIAL_REGISTRATION = 1;
  const SYNC_FREQUENCY_EVERY_LOGIN = 2;

  /**
   * The Entity Field Manager to provide field definitions.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a \Drupal\cas\Form\CasSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($config_factory);
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cas_attributes_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cas_attributes.settings');

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General Settings'),
      '#tree' => TRUE,
    ];
    $form['general']['sitewide_token_support'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sitewide token support'),
      '#description' => $this->t('When enabled, CAS attributes for the logged in user can be retrieved anywhere on your site using this token format: [cas:attribute:?], where ? is the attribute name in lowercase. This works by storing all CAS attributes in the user session, so if you have many attributes and many users, this may make your session storage size very large. <b>This is not required to use tokens for the user field mappings below.</b>'),
      '#default_value' => $config->get('sitewide_token_support'),
    ];

    $form['field'] = array(
      '#type' => 'details',
      '#title' => $this->t('User Field Mappings'),
      '#description' => $this->t('Configure settings for mapping CAS attribute values to user fields during login/registration.'),
      '#tree' => TRUE,
      '#open' => TRUE,
    );

    $form['field']['sync_frequency'] = array(
      '#type' => 'radios',
      '#title' => $this->t('When should field mappings be applied to the user?'),
      '#options' => array(
        self::SYNC_FREQUENCY_NEVER => $this->t('Never'),
        self::SYNC_FREQUENCY_INITIAL_REGISTRATION => $this->t('Initial registration only (requires "Auto register users" <a href="@link">CAS setting</a> be enabled).', ['@link' => Url::fromRoute('cas.settings')->toString()]),
        self::SYNC_FREQUENCY_EVERY_LOGIN => $this->t('Every login'),
      ),
      '#default_value' => $config->get('field.sync_frequency'),
    );

    $form['field']['overwrite'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Overwrite existing field values'),
      '#description' => $this->t('When checked, the field mappings below will always overwrite existing data on the user account.'),
      '#default_value' => $config->get('field.overwrite'),
    ];

    $form['field']['mappings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Fields'),
      '#description' => $this->t(
        'Optionally provide values for each user field below. To use a CAS attribute, insert a token in the format [cas:attribute:?], where ? is the attribute name in lowercase. <a href="@link">Browse available attribute tokens</a> for the currently logged in user. Note that attribute tokens will still work even if you have the "Sitewide token support" feature disabled (above).',
        ['@link' => Url::fromRoute('cas_attributes.available_attributes')->toString()]
      ),
      '#tree' => TRUE,
      '#open' => TRUE,
    );

    $savedFieldMappings = unserialize($config->get('field.mappings'));
    $form['field']['mappings']['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('The CAS module defaults this to the username your CAS server provided. Any value placed here will overwrite what the CAS module provides.'),
      '#size' => 60,
      '#default_value' => isset($savedFieldMappings['name']) ? $savedFieldMappings['name'] : '',
    );

    $form['field']['mappings']['mail'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('E-mail address'),
      '#description' => $this->t('The <a href="@link">settings page for the main CAS module</a> defines the default value for e-mail. Any value placed here will overwrite what the CAS module provides.', ['@link' => Url::fromRoute('cas.settings')->toString()]),
      '#size' => 60,
      '#default_value' => isset($savedFieldMappings['mail']) ? $savedFieldMappings['mail'] : '',
    );

    foreach ($this->entityFieldManager->getFieldDefinitions('user', 'user') as $name => $definition) {
      if (!empty($definition->getTargetBundle())) {
        if ($definition->getType() == 'string' || $definition->getType() == 'list_string') {
          $form['field']['mappings'][$name] = array(
            '#type' => 'textfield',
            '#title' => $definition->getLabel(),
            '#default_value' => isset($savedFieldMappings[$name]) ? $savedFieldMappings[$name] : '',
            '#size' => 60,
            '#description' => $this->t('The account field with name %field_name.', array('%field_name' => $definition->getName())),
          );
        }
      }
    }

    $form['role'] = array(
      '#type' => 'details',
      '#title' => $this->t('User Role Mappings'),
      '#description' => $this->t('Configure settings for assigning roles to users during login/registration based on CAS attribute values.'),
      '#tree' => TRUE,
      '#open' => TRUE,
    );

    $form['role']['sync_frequency'] = array(
      '#type' => 'radios',
      '#title' => $this->t('When should role mappings be applied to the user?'),
      '#options' => array(
        self::SYNC_FREQUENCY_NEVER => $this->t('Never'),
        self::SYNC_FREQUENCY_INITIAL_REGISTRATION => $this->t('Initial registration only (requires "Auto register users" <a href="@link">CAS setting</a> be enabled).', ['@link' => Url::fromRoute('cas.settings')->toString()]),
        self::SYNC_FREQUENCY_EVERY_LOGIN => $this->t('Every login'),
      ),
      '#default_value' => $config->get('role.sync_frequency'),
    );

    $form['role']['deny_login_no_match'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Deny login if no roles are mapped'),
      '#description' => $this->t('If enabled, users will not be able to login via CAS unless at least one role is assigned based on the mappings below.'),
      '#default_value' => $config->get('role.deny_login_no_match'),
    );

    $form['role']['deny_registration_no_match'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Deny registration if no roles are mapped'),
      '#description' => $this->t('If enabled, users will not be able to auto-register via CAS unless at least one role is assigned based on the mappings below.'),
      '#default_value' => $config->get('role.deny_registration_no_match'),
    );

    $form['role']['mappings'] = array(
      '#type' => 'details',
      '#title' => $this->t('CAS Role Mappings'),
      '#description' => $this->t("Each role mapping is a relationship between a role that is to be granted, an attribute name, an attribute value to match, and a method to use for comparison."),
      '#tree' => TRUE,
      '#open' => TRUE,
    );

    $existingRoleMappings = unserialize($config->get('role.mappings'));
    $roles_available = user_roles();
    unset($roles_available[RoleInterface::AUTHENTICATED_ID]);
    unset($roles_available[RoleInterface::ANONYMOUS_ID]);
    $roles_options = [];
    $count = empty($existingRoleMappings) ? 0 : count($existingRoleMappings);
    if (empty($existingRoleMappings)) {
      $existingRoleMappings = [];
    }
    foreach ($roles_available as $role_id => $role) {
      $roles_options[$role_id] = $role->label();
    }

    // Add existing mappings to the form.
    foreach ($existingRoleMappings as $index => $condition) {
      $form['role']['mappings'][$index] = $this->generateRoleMappingFormElements($roles_options, $condition);

      $form['role']['mappings'][$index]['delete'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Remove this mapping?'),
      );
    }

    // Always add a empty row to allow adding a new mapping.
    $form['role']['mappings'][$count] = $this->generateRoleMappingFormElements($roles_options);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Generate a form elements for describing a role mapping.
   *
   * @param array $roleOptions
   *   The available roles to map to.
   * @param array $existingData
   *   Default data for each form element, if available.
   *
   * @return array
   *   The form elements for the mapping.
   */
  protected function generateRoleMappingFormElements(array $roleOptions, array $existingData = []) {
    $elements = [
      '#type' => 'fieldset',
      '#title' => $this->t('Role Mapping'),
    ];
    $elements['rid'] = [
      '#type' => 'select',
      '#title' => $this->t('Role to Assign'),
      '#options' => $roleOptions,
    ];
    $elements['attribute'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attribute Name'),
      '#description' => $this->t('See a <a href="@link">list of available attributes</a> for the currently logged in user (do not provide a token here, use the actual attribute name).', ['@link' => Url::fromRoute('cas_attributes.available_attributes')->toString()]),
      '#size' => 30,
    ];
    $elements['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attribute Value'),
      '#size' => 30,
    ];
    $elements['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Comparison Method'),
      '#options' => [
        'exact_single' => $this->t('Exact (Single)'),
        'exact_any' => $this->t('Exact (Any)'),
        'contains_any' => $this->t('Contains'),
        'regex_any' => $this->t('Regex'),
      ],
      '#description' => $this->t("
        The 'Exact (Single)' method passes if the attribute value has one value only and it matches the given string exactly.
        The 'Exact (Any)' method passes if any item in the attribute value array matches the given string exactly.
        The 'Contains' method passes if any item in the attribute value array contains the given string within it anywhere.
        The 'Regex' method passes if any item in the attribute value array passes the regular expression provided.
      "),
    ];
    $elements['remove_without_match'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remove role from user if match fails?'),
      '#description' => $this->t('Note that if checked, this means that is this role has been manually assigned to the user, it will still be removed.'),
    );

    if (!empty($existingData)) {
      foreach ($existingData as $key => $val) {
        $elements[$key]['#default_value'] = $val;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cas_attributes.settings');

    $config->set('sitewide_token_support', $form_state->getValue(['general', 'sitewide_token_support']));

    $field_data = $form_state->getValue('field');
    $config
      ->set('field.sync_frequency', $field_data['sync_frequency'])
      ->set('field.overwrite', $field_data['overwrite']);
    $config->set('field.mappings', serialize($field_data['mappings']));

    $role_data = $form_state->getValue('role');
    $role_map = [];
    // Filter out invalid mappings before saving.
    foreach ($role_data['mappings'] as $mapping) {
      // Ignore any mappings that have the delete flag.
      if (isset($mapping['delete']) && $mapping['delete']) {
        continue;
      }
      // Ignore any mappings that have incomplete data.
      if (empty($mapping['attribute']) || empty($mapping['value'])) {
        continue;
      }
      $role_map[] = $mapping;
    }

    $config
      ->set('role.sync_frequency', $role_data['sync_frequency'])
      ->set('role.deny_login_no_match', $role_data['deny_login_no_match'])
      ->set('role.deny_registration_no_match', $role_data['deny_registration_no_match'])
      ->set('role.mappings', serialize($role_map));

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return array('cas_attributes.settings');
  }

}
