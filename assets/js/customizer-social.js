/* Customizer control: sortable list of social media links (platform, URL, icon). */
(function (api, $) {
  "use strict";

  var MAX_ITEMS = 50;

  api.controlConstructor.tailbase_social_links = api.Control.extend({
    ready: function () {
      var control = this;
      var $list = control.container.find(".tailbase-social-list");
      var $add = $('<button type="button" class="button">Add link</button>');
      var items = [];

      try {
        items = JSON.parse(control.setting.get()) || [];
      } catch (e) {
        items = [];
      }

      function save() {
        control.setting.set(JSON.stringify(items));
      }

      function field(label, key, index, placeholder) {
        var $input = $('<input type="text" class="widefat">')
          .val(items[index][key] || "")
          .attr("placeholder", placeholder || "")
          .on("input", function () {
            items[index][key] = this.value;
            save();
          });

        return $("<p>").append($("<label>").text(label).append($input));
      }

      function move(from, to) {
        items.splice(to, 0, items.splice(from, 1)[0]);
        save();
        render();
      }

      function render() {
        $list.empty();

        $.each(items, function (index, item) {
          var $item = $('<div class="tailbase-social-item"></div>').css({
            border: "1px solid #c3c4c7",
            background: "#fff",
            padding: "8px 10px",
            marginBottom: "8px",
          });
          var $actions = $(
            '<p style="display:flex;gap:4px;margin:0 0 6px"></p>',
          );

          $actions.append(
            $('<strong style="flex:1"></strong>').text(
              item.platform || "New link",
            ),
            $(
              '<button type="button" class="button button-small" title="Move up">↑</button>',
            )
              .prop("disabled", index === 0)
              .on("click", function () {
                move(index, index - 1);
              }),
            $(
              '<button type="button" class="button button-small" title="Move down">↓</button>',
            )
              .prop("disabled", index === items.length - 1)
              .on("click", function () {
                move(index, index + 1);
              }),
            $(
              '<button type="button" class="button button-small button-link-delete">Remove</button>',
            ).on("click", function () {
              items.splice(index, 1);
              save();
              render();
            }),
          );

          $item.append(
            $actions,
            field("Platform", "platform", index, "Facebook"),
            field("URL", "url", index, "https://"),
            field(
              "Icon (file name from assets/icons, or URL)",
              "icon",
              index,
              "facebook",
            ),
          );
          $list.append($item);
        });
      }

      $add.on("click", function () {
        if (items.length >= MAX_ITEMS) {
          return;
        }
        items.push({ platform: "", url: "", icon: "" });
        save();
        render();
      });

      control.container.append($add);
      render();
    },
  });
})(wp.customize, jQuery);
