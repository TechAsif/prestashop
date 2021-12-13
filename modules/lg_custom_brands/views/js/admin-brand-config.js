$(document).ready(function () {
  $(function () {
    var ajaxHandler = null;
    
    function updateMyBrands(brand2ul, brandType) {
      var values = [];
      $("#"+brand2ul+" li").each(function (index) {
        values.push($(this).attr("id"));
      });


      // Stop previous ajax-request
      if (ajaxHandler) {
        clearTimeout(ajaxHandler);
      }

      // Start a new ajax-request in X ms
      ajaxHandler = setTimeout(function() {
        $.ajax({
          url: custom_brand_ajax,
          type: "GET",
          dataType: "json",
          data: {
            controller: "AdminCustomBrand",
            action: "setCustomBrands",
            brandType: brandType,
            ajax: true,
            data: values,
          },
          success: function (response) {
            $.simplyToast('success', response.message, {ele: '.lg-brand'});
          },
        });
      }, 500);
    }
    $("#custom_brands1, #custom_brands2").sortable({
      connectWith: ".custom_brands_sortable",
      update: function (event, ui) {
        updateMyBrands('custom_brands2', 'LG_CUSTOM_BRAND_IDS');
      }, //end update
    });
    $("#top_brands1, #top_brands2").sortable({
      connectWith: ".top_brands_sortable",
      update: function (event, ui) {
        updateMyBrands('top_brands2', 'LG_TOP_BRAND_IDS');
      }, //end update
    });
    $("#featured_brands1, #featured_brands2").sortable({
      connectWith: ".featured_brands_sortable",
      update: function (event, ui) {
        updateMyBrands('featured_brands2', 'LG_FEATURED_BRAND_IDS');
      }, //end update
    });
    $("#populer_brands1, #populer_brands2").sortable({
      connectWith: ".populer_brands_sortable",
      update: function (event, ui) {
        updateMyBrands('populer_brands2', 'LG_POPULER_BRAND_IDS');
      }, //end update
    });
  });

});
