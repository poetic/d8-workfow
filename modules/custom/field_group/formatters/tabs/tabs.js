(function ($) {

  'use strict';

  Drupal.FieldGroup = Drupal.FieldGroup || {};
  Drupal.FieldGroup.Effects = Drupal.FieldGroup.Effects || {};

  /**
   * Implements Drupal.FieldGroup.processHook().
   */
  Drupal.FieldGroup.Effects.processTabs = {
    execute: function (context, settings, group_info) {

      if (group_info.context === 'form') {

        // Add required fields mark to any element containing required fields
        var direction = group_info.settings.direction;
        $(context).find('[data-' + direction + '-tabs-panes] details').once('fieldgroup-effects').each(function () {

          if ($(this).is('.required-fields') && $(this).find('[required]').length > 0) {
            if (typeof $(this).data(direction + 'Tab') !== 'undefined') {
              $(this).data(direction + 'Tab').link.find('strong:first').addClass('form-required');
            }
          }

          if ($('.error', $(this)).length) {
            $(this).data(direction + 'Tab').link.parent().addClass('error');
            Drupal.FieldGroup.setGroupWithfocus($(this));
            $(this).data(direction + 'Tab').focus();
          }

        });

      }
    }
  };

})(jQuery, Modernizr);
