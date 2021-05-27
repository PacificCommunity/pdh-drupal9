(function ($) {
  Drupal.behaviors.spcCkanSearchAutocomplete = {
    attach: function (context, settings) {
      let activePosition = 0;
      let requestId = null;
      let isPending = null;

      let suggestions = [];
      let queries = [];
      const delay = 400;
      const autocompleteInput = settings.search_autocomplete.autocompleteInput
      const suggestionBoxBlock = settings.search_autocomplete.suggestionBox
      const baseUrl = settings.search_autocomplete.baseUrl
      const el = $('.spc-home-banner-block .search');

      const input = $(autocompleteInput);
      if (!input.length) {
        console.error(
          '[search-autocomplete] input does not exist: %s',
          autocompleteInput
        );
      }

      const suggestionBox = $(suggestionBoxBlock);
      if (!suggestionBox.length) {
        console.error(
          '[search-autocomplete] suggestion box does not exist: %s',
          suggestionBoxBlock
        );
      }

      input.on({
        keyup: function (e) {
          if (~e.key.indexOf('Arrow')) {
            return;
          }
          cleanSchedule();
          queries.push(e.target.value);
          scheduleRequest();
        },
        keydown: function (e) {
          switch (e.key) {
            case 'ArrowDown':
              cycleSuggestions(+1);
              e.preventDefault();
              break;
            case 'ArrowUp':
              cycleSuggestions(-1);
              e.preventDefault();
              break;
            case 'Enter':
              if (activePosition) {
                e.preventDefault();
                pickActive();
              }
              break;
            default:
              return;
          }
        },
        blur: function () {
          cleanSchedule();
          // wait a bit if user wants to click on a link from the
          // suggestion box
          setTimeout(function () {
            dropSuggestionList();
          }, 600);
        }
      });


      function cleanSchedule () {
        clearTimeout(requestId);
        requestId = null;
      }
      function scheduleRequest () {
        if (isPending || !queries.length || isAdvancedEnabled()) {
          return;
        }

        requestId = setTimeout(function () {
          isPending = true;
          el.addClass('pending-suggestions');
          var q = queries.splice(0).pop();

          const request_url = baseUrl + "/data/api/action/spc_search_autocomplete?q=" + q;

          $.ajax({
            type: "GET",
            url: request_url,

            success: function (resp) {
              isPending = false;
              el.removeClass('pending-suggestions');
              if (requestId === null) {
                return;
              }
              buildSuggestionList(resp.result, q);
              scheduleRequest();
            },
            error: function (err) {
              isPending = false;
              el.removeClass('pending-suggestions');
              console.error(err);
              scheduleRequest();
            },
          });
        }, delay);
      }

      function cycleSuggestions (step) {
        setActive(activePosition + step);
      }
      function pickActive () {
        var idx = activePosition - 1;
        var suggestion = suggestions[idx];
        if (!suggestion) {
          console.error('Index %s is out of bounds: %o', idx, suggestions);
          return;
        }
        follow(suggestion);
      }
      function setActive (idx) {
        var cap = suggestions.length + 1;
        activePosition = idx === 0 ? 0 : ((idx % cap) + cap) % cap;
        suggestionBox.find('.selected').removeClass('selected');
        if (activePosition !== 0) {
          suggestionBox
            .find('.suggestions li a')
            .eq(activePosition - 1)
            .addClass('selected');
        }
      }

      function buildSuggestionList (data, q) {
        setActive(0);
        suggestions = [].concat(data.datasets).concat(data.categories);
        if (suggestions.length) {
          el.addClass('active-suggestions');
        } else {
          el.removeClass('active-suggestions');
        }
        for (var key in data) {
          suggestionBox
            .find('[data-section="' + key + '"] .suggestions')
            .children()
            .remove()
            .prevObject.append(
              data[key].map(function (item) {
                return $('<li>').append(
                  $('<a>', { html: formatLabel(key, q, item), href: item.href })
                );
              })
            );
        }
      }
      function dropSuggestionList () {
        setActive(0);
        suggestions = [];
        suggestionBox.find('.suggestions').children().remove();
        el.removeClass('active-suggestions');
      }

      function isAdvancedEnabled() {
        if ($('#edit-advanced .fieldset-wrapper').is(':hidden')) {
          return false;
        }
        return true;
      }
    },
  };

  function follow(suggestion) {
    window.location.href = suggestion.href;
  }

  function formatLabel(type, q, item) {
    var text = $('<span>', { html: item.label }).text();
    switch (type) {
      case 'datasets':
        return $('<span>', {
          html: text.replace(
            new RegExp(
              '(' + q.split(' ').filter(Boolean).join('|') + ')',
              'gi'
            ),
            '<strong>$1</strong>'
          ),
        });
        break;
      case 'categories':
        return $('<span>', { text: text }).append(
          $('<span>', { text: '(' + item.type + ')' }).addClass('muted')
        );
        break;
      default:
        return item.label;
    }
  }
})(jQuery);
