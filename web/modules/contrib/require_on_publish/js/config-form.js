(function ($, Drupal) {

  Drupal.behaviors.toggleRequiredOnPublish = {
    attach: function (context) {
      // If required is checked AND require_on_publish is also checked,
      // uncheck require_on_publish.
      $('input[name=required]').on('change', function () {
        if ($(this).prop('checked') && $('input[name=require_on_publish]').prop('checked')) {
          $('input[name=require_on_publish]').prop('checked', false);
        }
      });

      // If require_on_publish is checked AND require is also checked,
      // uncheck require.
      $('input[name=require_on_publish]').on('change', function () {
        if ($(this).prop('checked') && $('input[name=required]').prop('checked')) {
          $('input[name=required]').prop('checked', false);
        }
      });
    }
  };

}(jQuery, Drupal))
