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
<script type="text/javascript">
    var dhl_products_params={};
    var cur_dhl_product_params={};
</script>
{capture assign=priceDisplayPrecisionFormat}{'%.'|cat:$smarty.const._PS_PRICE_DISPLAY_PRECISION_|cat:'f'|escape:'htmlall':'UTF-8'}{/capture}
{if ! $order_list}
  <p class="warning warn">{l s='There are no orders that use any of dhl carriers in your selection' mod='dhldp'}</p>
{else}
    {if isset($general_errors) && count($general_errors) > 0}
        <div class="alert alert-danger"><ul>
                {foreach from=$general_errors item=general_error}
                    <li>{$general_error|escape:'html':'UTF-8'}</li>
                {/foreach}
            </ul></div>
    {/if}
    {if isset($general_confirmations) && count($general_confirmations) > 0}
        <div class="alert alert-success"><ul>
                {foreach from=$general_confirmations item=general_confirmation}
                    <li>{$general_confirmation|escape:'html':'UTF-8'}</li>
                {/foreach}
            </ul></div>
    {/if}
<div class="row dhl_panel">
  <div class="col-lg-12">
  <form method="post" class="{if !$is177}form-inline{/if}" action="{$smarty.server.REQUEST_URI}">
  {foreach from=$order_list item=order_line}
      <script type="text/javascript">
          dhl_products_params[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]={$order_line.dhl_products|json_encode};
          cur_dhl_product_params[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]={};
      </script>
  <div id="ordercarrier-{$order_line.id_order_carrier|escape:'html':'UTF-8'}" class="panel"{if isset($error_order_line) && in_array($order_line['id_order'], $error_order_line)} style="border-color: red;"{elseif isset($warning_order_line) && in_array($order_line['id_order'], $warning_order_line)} style="border-color: orange;"{elseif isset($success_order_line) && in_array($order_line['id_order'], $success_order_line)} style="border-color: #00ff00;"{else}{/if}>
    <div class="panel-heading">{l s='Order #' mod='dhldp'} <span class="badge">{$order_line.id_order|escape:'html':'UTF-8'}</span>
        {l s='Order Ref.' mod='dhldp'} <span class="badge">{$order_line.reference|escape:'html':'UTF-8'}</span>
		{l s='Customer' mod='dhldp'} <span class="badge">{$order_line.customer|escape:'html':'UTF-8'}</span>
		{l s='Country' mod='dhldp'} <span class="badge">{$order_line.country|escape:'html':'UTF-8'}</span>
        <a class="button" href="?tab=AdminOrders&amp;id_order={$order_line['id_order']|intval}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">
            {l s='View order' mod='dhldp'}
        </a>
	</div>
      {if isset($orders_errors[$order_line['id_order']]) && count($orders_errors[$order_line['id_order']]) > 0}
          <div class="alert alert-danger"><ul>
          {foreach from=$orders_errors[$order_line['id_order']] item=order_error}
              <li>{$order_error|escape:'html':'UTF-8'}</li>
          {/foreach}
          </ul></div>
      {/if}
      {if isset($orders_confirmations[$order_line['id_order']]) && count($orders_confirmations[$order_line['id_order']]) > 0}
          <div class="alert alert-success"><ul>
                  {foreach from=$orders_confirmations[$order_line['id_order']] item=order_confirmation}
                      <li>{$order_confirmation|escape:'html':'UTF-8'}</li>
                  {/foreach}
              </ul></div>
      {/if}
      {if isset($orders_warnings[$order_line['id_order']]) && count($orders_warnings[$order_line['id_order']]) > 0}
          <div class="alert alert-warning"><ul>
                  {foreach from=$orders_warnings[$order_line['id_order']] item=order_warning}
                      <li>{$order_warning|escape:'html':'UTF-8'}</li>
                  {/foreach}
              </ul></div>
      {/if}
    {if isset($order_line.dhl_assigned) && $order_line.dhl_assigned == true}
    <table class="table std" cellspacing="0" cellpadding="0" style="width:100%;">
		<thead>
			  <tr>
                <th>{l s='ID' mod='dhldp'}</th>
                <th>{l s='Date' mod='dhldp'}</th>
				<th>{l s='Product / Shipment date' mod='dhldp'}</th>
				<th>{l s='Parameters' mod='dhldp'}</th>
				<th>{l s='Options' mod='dhldp'}</th>
				<th>{l s='Action' mod='dhldp'}</th>
			  </tr>
		  </thead>
        {if isset($order_line.labels)}
          <tbody>
            {if count($order_line.labels) > 1}<tr><td colspan="6"><a href="#" id="toggleAllDHLLabelsForOrder">{l s='Show/hide all labels for this order' mod='dhldp'}</a></td></tr>{/if}
            {foreach from=$order_line.labels item=carrier name=dhl_label}
              <tr{if !$smarty.foreach.dhl_label.last} class="hiddenLabel" style="display: none;"{/if}>
                <td>{$carrier.id_dhldp_label|escape:'htmlall':'UTF-8'}</td>
                <td>{$carrier.date_add|escape:'htmlall':'UTF-8'}</td>
                <td>
                    {*{$order_line.carrier_name|escape:'html':'UTF-8'}<br>*}
                    {$carrier.product_name|escape:'htmlall':'UTF-8'}<br>
                    {if isset($carrier.is_return) && $carrier.is_return == 1}
                        <span class="badge badge-warning">{l s='Return label' mod='dhldp'}</span><br>
                    {/if}
                    {if isset($carrier.with_return) && $carrier.with_return == 1}
                        <span class="badge badge-warning">{l s='With return label' mod='dhldp'}</span><br>
                    {/if}
                    {if $carrier.shipment_date != '0000-00-00 00:00:00'}
                        {strftime('%Y-%m-%d', strtotime($carrier.shipment_date, false))}
                    {/if}
                </td>
                <td>
                    <label>{l s='Weight' mod='dhldp'}</label>: {Tools::ps_round($carrier.packages[0]['weight'], 1)|escape:'htmlall':'UTF-8'} {l s='kg' mod='dhldp'};
                    <label>{l s='Length' mod='dhldp'}</label>: {$carrier.packages[0]['length']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'} x
                    <label>{l s='Width' mod='dhldp'}</label>: {$carrier.packages[0]['width']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'} x
                    <label>{l s='Height' mod='dhldp'}</label>: {$carrier.packages[0]['height']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'};
                </td>
                  <td>
                      <div id="dhl_options">
                          {if isset($carrier.options_decoded['DeclaredValueOfGoods'])}
                          <div>
                              <label>{l s='Declared value' mod='dhldp'}</label>
                              <div class="input-group fixed-width-x2 pull-right">
                                  {$carrier.options_decoded['DeclaredValueOfGoods']|escape:'htmlall':'UTF-8'} {$carrier.options_decoded['DeclaredValueOfGoodsCurrency']|escape:'htmlall':'UTF-8'}
                              </div>
                          </div>
                          {/if}
                          {if isset($carrier.options_decoded['COD']['CODAmount'])}
                          <div>
                              <label>{l s='COD amount' mod='dhldp'}</label>
                              <div class="input-group fixed-width-x2 pull-right">
                                  {$carrier.options_decoded['COD']['CODAmount']|escape:'htmlall':'UTF-8'} {$carrier.options_decoded['COD']['CODCurrency']|escape:'htmlall':'UTF-8'}
                              </div>
                          </div>
                          {/if}
                          {if isset($carrier.options_decoded['HigherInsurance']['InsuranceAmount'])}
                          <div>
                              <label>{l s='Insurance amount' mod='dhldp'}</label>
                              <div class="input-group fixed-width-x2 pull-right">
                                  {$carrier.options_decoded['HigherInsurance']['InsuranceAmount']|escape:'htmlall':'UTF-8'} {$carrier.options_decoded['HigherInsurance']['InsuranceCurrency']|escape:'htmlall':'UTF-8'}
                              </div>
                          </div>
                          {/if}
                          {if isset($carrier.options_decoded['CheckMinimumAge']['MinimumAge']) && $carrier.options_decoded['CheckMinimumAge']['MinimumAge'] > 0}
                          <div>
                              <label>{l s='Minimum age' mod='dhldp'}</label>
                              <div class="input-group fixed-width-x2 pull-right">
                                  {$carrier.options_decoded['CheckMinimumAge']['MinimumAge']|escape:'htmlall':'UTF-8'} {l s='years' mod='dhldp'}
                              </div>
                          </div>
                          {/if}
                      </div>
                  </td>
                <td>
                    <label>{l s='Shipment number' mod='dhldp'}</label>: {$carrier.shipment_number|escape:'htmlall':'UTF-8'}<br>
                    {if $carrier.label_url}
                        {if $smarty.foreach.dhl_label.last}
                            <input type="hidden" name="printLabel[{$carrier.id_order_carrier|escape:'html':'UTF-8'}][label_url]" value="{$carrier.label_url|escape:'html':'UTF-8'}" />
                        {/if}

                        <a href="{$carrier.label_url|escape:'html':'UTF-8'}" class="btn btn-primary" target="_blank"><i class="icon-print"></i> {l s='Print label' mod='dhldp'}</a>
                    {/if}
                    {if $carrier.return_label_url != ''}
                        <a class="btn btn-primary" href="{$carrier.return_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print return label' mod='dhldp'}</a>
                    {/if}
                    {if $carrier.export_label_url != ''}
                        <a class="btn btn-primary" href="{$carrier.export_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print export doc' mod='dhldp'}</a>
                    {/if}
                    {if $carrier.cod_label_url != ''}
                        <a class="btn btn-primary" href="{$carrier.cod_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print COD doc' mod='dhldp'}</a>
                    {/if}
                    {if $carrier.tracking_url}
                    <a class="btn btn-default" href="{$carrier.tracking_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-search"></i> {l s='Tracking' mod='dhldp'}</a>
                    {/if}
                </td>
            </tr>
         {/foreach}
        {/if}
        <tr>
            <td colspan="3">
                <input type="hidden" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][order_id]" value="{$order_line.id_order|escape:'html':'UTF-8'}" />
                <input type="hidden" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][order_reference]" value="{$order_line.reference|escape:'html':'UTF-8'}" />
                <input type="hidden" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][id_order_carrier]" value="{$order_line.id_order_carrier|escape:'html':'UTF-8'}" />
                <input type="hidden" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][id_carrier]" value="{$order_line.id_carrier|escape:'html':'UTF-8'}" />
                <input type="hidden" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][id_address]" value="{$order_line.id_address_delivery|escape:'html':'UTF-8'}" />
                <div class="row">
                {$order_line.carrier_name|escape:'html':'UTF-8'}
                </div>
                <div class="row">
                <select id="dhl_product_code" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][dhl_product_code]" class="fixed-width-lg" style="display: inline-block;">
                    {foreach from=$order_line.dhl_products key=product_index item=product_data}
                        <option value="{$product_data.fullcode|escape:'html':'UTF-8'}"{if (isset($smarty.post.carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]['dhl_product_code'])) && ($smarty.post.carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]['dhl_product_code'] == $product_data.fullcode)} selected="selected"{elseif (isset($order_line.default_dhl_product_code) && $order_line.default_dhl_product_code == $product_data.fullcode)} selected="selected"{/if}>{$product_data.fullname|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
                </div>
                <div class="row">
                    <input class="form-control datepicker" type="text" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][dhl_shipment_date]"
                           value="{if isset($smarty.post.carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]['dhl_shipment_date'])}{$smarty.post.carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}]['dhl_shipment_date']|escape:'htmlall':'UTF-8'}{else}{$shipment_date|escape:'htmlall':'UTF-8'}{/if}" maxlength="10"/>
                    <span class="help-block">{l s='yyyy-mm-dd' mod='dhldp'}</span>
                </div>
            </td>
            <td align="left" colspan="3">
                <div id="dhl_params" class="row">
                    <div class="col-lg-3">
                        <label for="width">{l s='Weight' mod='dhldp'}</label>
                        <div class="input-group">
                            <input type="text" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][weight]" size="3"
                                   value="{$order_line.input_default_values.weight|escape:'html':'UTF-8'}" />
                            <span class="input-group-addon">{l s='kg' mod='dhldp'}</span>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="width">{l s='Width' mod='dhldp'}</label>
			            <div class="input-group">
                            <input type="text" size="3" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][width]"
                            value="{$order_line.input_default_values.width|escape:'html':'UTF-8'}" />
                            <span class="input-group-addon">{l s='cm' mod='dhldp'}</span>
					   </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="height">{l s='Height' mod='dhldp'}</label>
                        <div class="input-group">
                        <input type="text" size="3" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][height]"
                               value="{$order_line.input_default_values.height|escape:'html':'UTF-8'}" />
                            <span class="input-group-addon">{l s='cm' mod='dhldp'}</span>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="depth">{l s='Depth' mod='dhldp'}</label>
                        <div class="input-group">
                            <input type="text" size="3" name="carrier[{$order_line.id_order_carrier|escape:'html':'UTF-8'}][length]"
                                   value="{$order_line.input_default_values.depth|escape:'html':'UTF-8'}" />
                            <span class="input-group-addon">{l s='cm' mod='dhldp'}</span>
                        </div>
                    </div>
                </div>
                <div class="row param-footer">
                    <div class="col-lg-12">
                        <button type="button" class="button btn btn-default pull-right" id="submitDhlAdditServices" name="submitDhlAdditServices"><i class="icon-angle-double-down"></i> {l s='Additional services' mod='dhldp'} </button>
                        <button  type="button" class="button btn btn-default" id="submitDhlUpdateAddress" name="submitDhlUpdateAddress"><i class="icon-angle-double-down"></i> {l s='Update delivery address' mod='dhldp'} </button>
                        <button  type="button" class="button btn btn-default" id="submitDhlExportDocuments" name="submitDhlExportDocuments"><i class="icon-angle-double-down"></i> {l s='Export documents' mod='dhldp'} </button>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                {assign var=address value=$order_line.address nocache}
                {assign var=addit_services value=$order_line.addit_services nocache}
                {assign var=export_docs value=$order_line.export_docs nocache}

                {include file="$self/views/templates/hook/additional-services.tpl"}
                {include file="$self/views/templates/hook/export-documents.tpl"}
                {include file="$self/views/templates/hook/update-address.tpl"}

            </td>
        </tr>
        </tbody>
    </table>
    {else}
        <span class="alert-warning">{l s='no dhl carrier' mod='dhldp'}</span>
    {/if}
	</div>
  {/foreach}
  <div class="panel">
	  <p class="alert alert-info">{l s='The labels will be generated only for those orders that don\'t have them generated already, the existing labels will not change' mod='dhldp'}</p>
	  <p>
		<button type="submit" class="btn btn-success" name="generateMultipleLabels" id="generateMultipleLabels">{l s='Generate labels' mod='dhldp'}</button>
		<button type="submit" class="btn btn-primary" name="printMultipleLabels" id="printMultipleLabels"><i class="icon-print"></i> {l s='Print last labels' mod='dhldp'}</button>
	  </p>
  </div>
  </form>
  </div>
</div>
{/if}