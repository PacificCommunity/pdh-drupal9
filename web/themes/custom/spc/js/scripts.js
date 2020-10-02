(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.spc = {
        attach: function(context, settings) {

            $('.pane-dashboard-for-sdp .button').unbind().click(function() {
                let dropdown = $('.pane-dashboard-for-sdp .dropdown-menu');
                if (dropdown.hasClass('hidden')) {
                    dropdown.removeClass('hidden');
                    $('#block-spc-local-tasks').css('z-index', '-9999');
                }
                else {
                    dropdown.addClass('hidden');
                    $('#block-spc-local-tasks').css('z-index', '9999');
                }
            })

        }
    };
})(jQuery, Drupal, drupalSettings);

