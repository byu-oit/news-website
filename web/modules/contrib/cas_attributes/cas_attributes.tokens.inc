<?php

/**
 * @file
 * Token module integration.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info_alter().
 */
function cas_attributes_token_info_alter(&$data) {
  // Alter existing token definition provided by CAS to add the dynamic
  // attribute token.
  $data['tokens']['cas']['attribute'] = [
    'name' => t('Attribute'),
    'description' => t('A CAS attribute of the logged in user. Replace ? with the name of the attribute, in all lowercase. If the specified attribute has multiple values, they are combined into one string. You can specify which value you want using an array modifier (:first, :last, :count, etc). Requires the "Sitewide token support" feature of the CAS Attributes module.'),
    'dynamic' => TRUE,
  ];
}

/**
 * Implements hook_tokens().
 */
function cas_attributes_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $sanitize = !empty($options['sanitize']);
  $replacements = [];
  $token_service = \Drupal::token();

  if ($type == 'cas') {
    // Find all attribute tokens formatted like [cas:attribute:?].
    if ($attribute_tokens = $token_service->findWithPrefix($tokens, 'attribute')) {
      $casAttributes = [];
      // Attributes may have been explicitly passed into the token replacer,
      // so check for them there first. Otherwise, fallback to checking the
      // session data. The main CAS module stores all attributes in the session
      // when login is finalized.
      if (!empty($data['cas_attributes'])) {
        $casAttributes = $data['cas_attributes'];
      }
      else {
        $session = \Drupal::request()->getSession();
        if ($session->has('cas_attributes')) {
          $casAttributes = $session->get('cas_attributes');
        }
      }

      // Change attribute names to lower case. All other case of token names
      // in Drupal is lowercase, so this allows users to keep with that
      // same convention.
      $casAttributes = array_change_key_case($casAttributes, CASE_LOWER);

      foreach ($attribute_tokens as $attribute => $originalToken) {
        // All attributes in CAS are stored as arrays, even though in most cases
        // they will they have one item. If the user did not provide a specific
        // array modifier to the token, assume that we should just join all
        // items of the array into a single string.
        // See "Array tokens." in token.tokens.inc.
        if (strpos($attribute, ':') === FALSE) {
          $attribute .= ':join';
        }

        // Extract the array token from the end of the attribute token.
        list($attribute, $arrayToken) = explode(':', $attribute, 2);

        $attribute = mb_strtolower($attribute);
        if (isset($casAttributes[$attribute])) {
          $casAttribute = $casAttributes[$attribute];
          if (!is_array($casAttribute)) {
            $casAttribute = [$casAttribute];
          }
          // Invoke the token generator to provide a replacement for the array
          // token.
          $replacements += $token_service->generate('array', [$arrayToken => $originalToken], ['array' => $casAttribute], $options, $bubbleable_metadata);
        }
        elseif ($attribute == '?') {
          // If the question mark token was used, then just output information
          // about all available attributes.
          $keys = array_keys($casAttributes);
          if ($sanitize) {
            $keys = array_map(['SafeMarkup', 'checkPlain'], $keys);
          }
          $replacements[$originalToken] = t('Available attributes: %keys', ['%keys' => implode(', ', $keys)]);
        }
      }
    }
  }

  return $replacements;
}
