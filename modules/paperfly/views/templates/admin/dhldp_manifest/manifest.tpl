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
<div class="panel ">
    <div class="panel-heading">{l s='Manifest' mod='dhldp'}</div>
    <form action="{$currentIndex|escape:'html':'UTF-8'}&amp;manifest{$table|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" method="post" class="form-horizontal" id="getmanifest">
        <div class="alert alert-info">
            <p>{l s='With "Get manifest" operation a end-of-day  reports are available for a specific day or period.' mod='dhldp'}</p>
            <p>{l s='The PAPERFLY business customer portal automatically closes all stored shipments every day at 18:00 or you use "Do manifest" operation for every created label.' mod='dhldp'}</p>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Manifest date:' mod='dhldp'}</label>
            <div class="col-lg-9">
                <input type="text" name="manifestDate" id="manifestDatepicker" value="{$manifestDate|escape}" class="datepicker fixed-width-sm">
            </div>
            <hr>
            {foreach $orders as $order}
            <div class="panel">
                <!-- Default panel contents -->
                <div class="panel-heading">{$order['product_name']|escape:'htmlall':'UTF-8'}</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p><b>Order Reference: </b> {$order['reference']|escape:'htmlall':'UTF-8'}</p>
                            <p><b>Payment: </b> {$order['payment']|escape:'htmlall':'UTF-8'}</p>
                            <p><b>Amount: </b> {$order['total_paid']|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                </div>
                
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
            {/foreach}
            {$orders|@var_dump}


        </div>
        <div class="panel-footer">
            <button name="getManifest" type="submit" class="btn btn-primary">
                {l s='Get manifest' mod='dhldp'}
            </button>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        if ($("form#getmanifest .datepicker").length > 0)
            $("form#getmanifest .datepicker").datepicker({
                prevText: '',
                nextText: '',
                dateFormat: 'yy-mm-dd'
            });
    });
</script>