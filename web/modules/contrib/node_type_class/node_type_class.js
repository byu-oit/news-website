/**
 * @file
 * Show summaries of selected options within tabs.
 */

(function ($) {
  "use strict";

  Drupal.behaviors.nodeTypeClass = {
    attach: function (context) {

      // Display save draft settings summary on the node options fieldet.
      $('#edit-node-type-class-fieldset', context).drupalSetSummary(function (context) {
        var vals = [];
        vals.push(Drupal.checkPlain($('#edit-node-type-class', context).val()));
        return vals.join(', ');
      });
    }
  };

})(jQuery);
