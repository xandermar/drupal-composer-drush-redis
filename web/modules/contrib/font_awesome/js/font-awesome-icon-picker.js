/**
 * @file
 * Font Awesome icon picker behavior.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach the icon picker widget.
   */
  Drupal.behaviors.fontAwesomeIconPicker = {
    attach: function (context, settings) {
      var input = $('.font-awesome-icon-picker', context);
      input.each(function(index, iconpickerInput) {
        var initialValue = iconpickerInput.value;
        var $iconpickerInput = $(iconpickerInput);
        var iconpickerInputData = $iconpickerInput.data();
        $('<button type="button" class="btn btn-primary iconpicker-component"></button>').insertBefore(iconpickerInput);

        $iconpickerInput.iconpicker(iconpickerInputData);
        // Initializing the icon picker with a default value also changed the input value.
        // That is not the desired behavior since it will unadvertedly fill empty forms.
        if (!initialValue) {
          iconpickerInput.value = '';
        }
      });
    }
  };

} (jQuery, Drupal));
