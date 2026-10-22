var sf_options

jQuery(function ($) {
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
  $('.sf-feed-acf-fields').multi({
    'non_selected_header': sf_options.unselected_acf,
    'selected_header': sf_options.selected_acf,
    'search_placeholder': sf_options.search
  });
});
