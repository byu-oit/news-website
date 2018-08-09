INTRODUCTION
------------

The Block Token module allows users to specify the blocks
that will have the tokens generated for use
within text formats using Token Filter module.


REQUIREMENTS
------------

This module requires the following modules:

 * Token (https://drupal.org/project/token)
 * Token Filter (https://drupal.org/project/token_filter)

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

 * Enable "Replace Tokens" filter for specific text formats available on
   Administration » Configuration » Content authoring » Text Formats

 * Edit the blocks: check the "Create the token for this block" checkbox
   for the block on edit screen.

 * Put the token of enabled block.
   E.g. [block_token:system:navigation], to the content field
   formatted with the text format with token replacement.
