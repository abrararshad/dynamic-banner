/**
 * @file
 * Javascript for the dynamic_banner module default library (bxslider)
 */

(function ($, Drupal) {

    'use strict';

    /**
     * Behaviors for setting summaries on content type form.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches summary behaviors on content type edit forms.
     */
    Drupal.behaviors.dynamicBanner = {
        attach: function (context) {
            var $context = $(context);
            var container = $context.find('.bxslider');
            $(container).bxSlider({
                mode: 'fade',
                captions: true
            });
        }
    };

})(jQuery, Drupal);
