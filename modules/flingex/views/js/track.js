$(document).ready(function(){

    function trackFlingexOrders(flingex_orders) {

        var flingexOrder = flingex_orders.pop();

        if( typeof flingexOrder != 'undefined' ) {

            $.ajax({
                url: flingex_ajax,
                type: 'GET',
                dataType: 'json',
                data: {
                    controller : 'AdminFlingex',
                    action : 'trackFlingexOrder',
                    data: flingexOrder,
                    ajax : true,
                },
                success: function(response){
                    $('#trackingOutput').append('\nTracking completed for '+'OrderId:' + flingexOrder.id_order + ' \tReference:' + flingexOrder.reference);

                    trackFlingexOrders(flingex_orders);
                }
            })
        } else {
            $('#trackingOutput').append('\nAll Trackings are completed. Pelase reload the page to get update');
        }

    }
    
    $('#trackFlingexOrders').on('click', function(e){
        e.preventDefault();
        $(this).prop('disabled', true);

        $.ajax({
            url: flingex_ajax,
            type: 'GET',
            dataType: 'json',
            data: {
                controller : 'AdminFlingex',
                action : 'getFlingexOrders',
                ajax : true,
            },
            success: function(response){

                $('#trackingOutput').slideDown().append('Tracker running....\n');
                var POrders = response.data;

                trackFlingexOrders(POrders);

            }
        })
 
    });
 
 });