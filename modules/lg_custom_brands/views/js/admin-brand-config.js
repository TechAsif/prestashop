$(document).ready(function () {
  $(function () {
    var ajaxHandler = null;
    
    function updateCustomBrands() {
      var values = [];
      $("#sortable2 li").each(function (index) {
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
            ajax: true,
            data: values,
          },
          success: function (response) {
            $.simplyToast('success', response.message, {ele: '.lg-brand'});
          },
        });
      }, 300);
    }
    $("#sortable1, #sortable2").sortable({
      connectWith: ".connectedSortable",
      update: function (event, ui) {
        updateCustomBrands();
      }, //end update
    });
  });

  $("#trackFlingexOrders").on("click", function (e) {
    e.preventDefault();
    $(this).prop("disabled", true);

    $.ajax({
      url: flingex_ajax,
      type: "GET",
      dataType: "json",
      data: {
        controller: "AdminFlingex",
        action: "getFlingexOrders",
        ajax: true,
      },
      success: function (response) {
        $("#trackingOutput").slideDown().append("Tracker running....\n");
        var POrders = response.data;

        trackFlingexOrders(POrders);
      },
    });
  });
});
