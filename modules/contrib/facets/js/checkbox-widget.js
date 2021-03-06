/**
 * @file
 * Transforms links into checkboxes.
 */

(function ($) {

  "use strict";

  Drupal.facets = Drupal.facets || {};
  Drupal.behaviors.facetsCheckboxWidget = {
    attach: function (context, settings) {
      Drupal.facets.makeCheckboxes();
    }
  };

  /**
   * Turns all facet links into checkboxes.
   */
  Drupal.facets.makeCheckboxes = function () {
    // Find all checkbox facet links and give them a checkbox.
    var $links = $('.js-facets-checkbox-links .facet-item a');
    $links.once('facets-checkbox-transform').each(Drupal.facets.makeCheckbox);
  };

  /**
   * Replace a link with a checked checkbox.
   */
  Drupal.facets.makeCheckbox = function () {
    var $link = $(this);
    var active = $link.hasClass('is-active');
    var description = $link.html();
    var href = $link.attr('href');
    var id = $link.data('drupal-facet-item-id');

    var checkbox = $('<input type="checkbox" class="facets-checkbox" id="' + id + '" data-facetsredir="' + href + '" />');
    var label = $('<label for="' + id + '">' + description + '</label>');

    checkbox.change(function (e) {
      Drupal.facets.disableFacet($link.parents('.js-facets-checkbox-links'));
      window.location.href = $(this).data('facetsredir');
    });

    if (active) {
      checkbox.attr('checked', true);
      label.find('.facet-deactivate').remove();
    }

    $link.before(checkbox).before(label).hide();

  };

  /**
   * Disable all facet checkboxes in the facet and apply a 'disabled' class.
   */
  Drupal.facets.disableFacet = function ($facet) {
    $facet.addClass('facets-disabled');
    $('input.facets-checkbox').click(Drupal.facets.preventDefault);
    $('input.facetapi-checkbox', $facet).attr('disabled', true);
  };

  /**
   * Event listener for easy prevention of event propagation.
   */
  Drupal.facets.preventDefault = function (e) {
    e.preventDefault();
  }

})(jQuery);
