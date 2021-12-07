$(document).ready(function(){

    function trackECourierOrders(ecourier_orders) {

        var ecourierOrder = ecourier_orders.pop();

        if( typeof ecourierOrder != 'undefined' ) {

            $.ajax({
                url: ecourier_ajax,
                type: 'GET',
                dataType: 'json',
                data: {
                    controller : 'AdminECourier',
                    action : 'trackECourierOrder',
                    data: ecourierOrder,
                    ajax : true,
                },
                success: function(response){
                    $('#trackingOutput').append('\nTracking completed for '+'OrderId:' + ecourierOrder.id_order + ' \tReference:' + ecourierOrder.reference);

                    trackECourierOrders(ecourier_orders);
                }
            })
        } else {
            $('#trackingOutput').append('\nAll Trackings are completed. Pelase reload the page to get update');
        }

    }
    
    $('#trackECourierOrders').on('click', function(e){
        e.preventDefault();
        $(this).prop('disabled', true);

        $.ajax({
            url: ecourier_ajax,
            type: 'GET',
            dataType: 'json',
            data: {
                controller : 'AdminECourier',
                action : 'getECourierOrders',
                ajax : true,
            },
            success: function(response){

                $('#trackingOutput').slideDown().append('Tracker running....\n');
                var POrders = response.data;

                trackECourierOrders(POrders);

            }
        })
 
    });
 
 });