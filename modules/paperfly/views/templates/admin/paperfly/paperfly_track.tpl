{**
* DHL Deutschepost
*
* @author    silbersaiten <info@silbersaiten.de>
* @copyright 2020 silbersaiten
* @license   See joined file licence.txt
* @category  Module
* @support   silbersaiten <support@silbersaiten.de>
* @version   1.0.0
* @link      http://www.silbersaiten.de
*}

<div class="panel-group" id="accordion" >
    <div class="panel">
        <div class="panel-heading">Update Paperfly Orders</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <button id="trackPaperflyOrders">Track Paperfly Orders</button>
                    <p><pre id="trackingOutput" style="display: none;"></pre></p>
                </div>
                <div class="col-md-6"></div>
            </div>
        </div>
    </div>
</div>


{foreach $orders as $order}
<div class="panel-group" id="accordion" >
    <div class="panel">
        <div class="panel-heading">
            <h4>
                <a role="button" data-toggle="collapse" href="#{$order['reference']|escape:'htmlall':'UTF-8'}" >
                    Order ID: {$order['id_order']|escape:'htmlall':'UTF-8'}
                    &nbsp;&nbsp;&nbsp;&nbsp; Reference: {$order['reference']|escape:'htmlall':'UTF-8'}
                </a>
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <p><b>Order Reference: </b> {$order['reference']|escape:'htmlall':'UTF-8'}</p>
                    <p><b>Payment: </b> {$order['payment']|escape:'htmlall':'UTF-8'}</p>
                    <p><b>Amount: </b> {$order['total_paid']|escape:'htmlall':'UTF-8'}</p>
                    <p>
                        <b>Products: </b>
                        
                        <table class="table">                        
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $order['products'] as $product}
                            <tr>
                                <td>{$product['product_id']|escape:'htmlall':'UTF-8'}</td>
                                <td>{$product['product_name']|escape:'htmlall':'UTF-8'}</td>
                                <td>{$product['product_quantity']|escape:'htmlall':'UTF-8'}</td>
                                <td>{$product['product_price']|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                            {/foreach}
                        </tbody>
                        </table>
                    </p>
                </div>
                <div class="col-md-6"></div>
            </div>
        </div>
        <div id="{$order['reference']|escape:'htmlall':'UTF-8'}" class="panel-collapse collapse">
        {if count($order['tracking_data']) > 0 } 
            <!-- Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $order['tracking_data'] as $track}
                    <tr>
                        <td>{$track['tracking_event_key']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$track['tracking_event_value']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$track['date_add']|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            {else}
                {l s='No tracking data found' mod='dhldp'}
                <p><b>Paperfly Status: </b> {$order['api_response_status_message']|escape:'htmlall':'UTF-8'}</p>
            {/if}
        </div>
    </div>
</div>

{/foreach}
{* {$orders|@var_dump} *}
