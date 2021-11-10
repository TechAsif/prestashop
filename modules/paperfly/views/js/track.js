$(document).ready(function(){

    function trackPaperflyOrders(paperfly_orders) {

        var paperflyOrder = paperfly_orders.pop();

        if( typeof paperflyOrder != 'undefined' ) {

            $.ajax({
                url: paperfly_ajax,
                type: 'GET',
                dataType: 'json',
                data: {
                    controller : 'AdminPaperfly',
                    action : 'trackPaperflyOrder',
                    data: paperflyOrder,
                    ajax : true,
                },
                success: function(response){
                    $('#trackingOutput').append('\nTracking completed for '+'OrderId:' + paperflyOrder.id_order + ' \tReference:' + paperflyOrder.reference);

                    trackPaperflyOrders(paperfly_orders);
                }
            })
        } else {
            $('#trackingOutput').append('\nAll Trackings are completed. Pelase reload the page to get update');
        }

    }
    
    $('#trackPaperflyOrders').on('click', function(e){
        e.preventDefault();
        $(this).prop('disabled', true);

        $.ajax({
            //  url: paperfly_ajax,
            url: paperfly_ajax,
            type: 'GET',
            dataType: 'json',
            data: {
                controller : 'AdminPaperfly',
                action : 'getPaperflyOrders',
                ajax : true,
            },
            success: function(response){

                console.log('dddd: ', response);

                $('#trackingOutput').slideDown().append('Tracker running....\n');
                var POrders = response.data;

                trackPaperflyOrders(POrders);

                // for (const PaperFlyOrder in response.data) {
                //     if (Object.hasOwnProperty.call(response.data, PaperFlyOrder)) {
                //         const element = response.data[PaperFlyOrder];
                        
                //     }
                // }
            }
        })
 
    });
 
 });