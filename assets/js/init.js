
(function ($, undefined) {

  $('.statuses_actions').multi({
    'non_selected_header': sf_options.unselected_orders,
    'selected_header': sf_options.selected_orders,
    'search_placeholder': sf_options.search
  });
  $('.categories').multi({
    'non_selected_header': sf_options.unselected_categories,
    'selected_header': sf_options.selected_categories,
    'search_placeholder': sf_options.search
  });


  $("#default_shipping_zone").on('change', function () {
    var zone_id = $(this).val();
    var selected_zone_id = $("#selected_shipping_zone").val();

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        ajax_nounce: sf_options.ajax_nounce,
        action: 'sf_get_default_shipping_method',
        zone_id: zone_id,
        selected_zone_id: selected_zone_id
      },
      success: function (data) {
        $('#default_shipping_method, select[id^=matching_shipping_]')
            .find('option')
            .remove()
            .end()
            .append('<option value="">-</option>')
        $("#default_shipping_method option:first, select[id^=matching_shipping_] option:first").prop('selected', 'selected');
        if ('0' === data) {
          return;
        }

        if (zone_id !== selected_zone_id) {
          var shipping_methods = data.data.shipping_methods;
          if (shipping_methods.length <= 0) {
            return;
          }

          set_shipping_methods(shipping_methods);

          return;
        } else {
          var shipping_methods = data.data.shipping_methods;
          var default_shipping_method = data.data.default_shipping_method;
          var matching_shipping_methods = data.data.matching_shipping_methods;
          if (shipping_methods.length <= 0) {
            return;
          }

          set_shipping_methods(shipping_methods);

          if (Object.keys(default_shipping_method).length > 0) {
            $('#default_shipping_method').val(JSON.stringify(default_shipping_method));
          }

          if (Object.keys(matching_shipping_methods).length > 0) {
            $.each(matching_shipping_methods, function (id_carrier, item) {
              let select_id = 'matching_shipping_' + id_carrier;
              $('#' + select_id).val(item);
            });
          }
          return;
        }


      }
    });


    function set_shipping_methods(shipping_methods) {
      $('#default_shipping_method')
          .find('option')
          .remove();
      $.each(shipping_methods, function (i, item) {
        $('#default_shipping_method')
            .append($("<option></option>")
                .attr("value", JSON.stringify(item))
                .text(item.method_title));
      });

      $('select[id^=matching_shipping_]')
          .find('option')
          .remove();
      $('select[id^=matching_shipping_]').each(function (index, element) {
        var self = this;
        var id = $(this).attr('id');
        var id_carrier = id.replace("matching_shipping_", "");
        $.each(shipping_methods, function (i, item) {
          item.sf_shipping = parseInt(id_carrier);
          $(self)
              .append($("<option></option>")
                  .attr("value", JSON.stringify(item))
                  .text(item.method_title));
        });
      });
    }

  })
})(jQuery);