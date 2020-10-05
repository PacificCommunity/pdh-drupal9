(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.spc = {
        attach: function(context, settings) {

            // Secondary Datasep pages header dropdown
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
            });

            //Search input box onEnter event
            $('.global-search-form #ckan-search').unbind().on('keypress',function(e) {
                console.log(e.which);
                if(e.which == 13 || e.which == 124) {
                    e.preventDefault();
                    window.location.href = "https://pacificdata.org/data/dataset?extras_thematic_area_string&q="+ $(this).val();
                }
            });

        }
    };
})(jQuery, Drupal, drupalSettings);
