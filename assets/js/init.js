var sf_options

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

})(jQuery);