(function ($) {
  var initialized = false;
  Drupal.behaviors.educationDashboardSearchAutocomplete = {
    attach: function (context, settings) {
      const tags = settings.spc_education_dashboard_ac.tags;
      function getKeyByValue(object, value) {
        return Object.keys(object).find(function (key) {
          return object[key] === value;
        });
      }
      $("#education-dashboard-search").autocomplete({
        source: Object.keys(tags),
        select: function (event, ui) {
          let itemValue = ui.item.value;
          let itemKey = tags[itemValue];

          $([document.documentElement, document.body]).animate(
            {
              scrollTop: $("#" + itemKey).offset().top,
            },
            100
          );
        },
      });
    },
  };
})(jQuery);
