/**
 * @file
 * UX improvements for the facet edit form.
 */

(function ($) {

  "use strict";

  Drupal.behaviors.facetsEditForm = {
    attach: function (context, settings) {
      $('.facet-source-field-wrapper select').change(function () {

        var default_name = $(this).find('option:selected').text();
        $('#edit-name').val(default_name);
        setTimeout(function () { $('#edit-name').trigger('change'); }, 100);

      })
    }
  };

})(jQuery);
