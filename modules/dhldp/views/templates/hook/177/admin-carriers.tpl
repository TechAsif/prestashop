{**
* DHL Deutschepost
*
* @author    silbersaiten <info@silbersaiten.de>
* @copyright 2021 silbersaiten
* @license   See joined file licence.txt
* @category  Module
* @support   silbersaiten <support@silbersaiten.de>
* @version   1.0.11
* @link      http://www.silbersaiten.de
*}
<script type="text/javascript">
    var dhldp_dhl_products_params={$dhldp_dhl_products_params|json_encode};
    var dhldp_cur_dhl_product_params={};
</script>
{capture assign=priceDisplayPrecisionFormat}{'%.'|cat:$smarty.const._PS_PRICE_DISPLAY_PRECISION_|cat:'f'|escape:'htmlall':'UTF-8'}{/capture}
<div class="row dhldp_dhl_order_block">
	<div class="col-lg-12">
		<div class="panel dhldp_dhl_order_panel card card-body" >
			<div class="panel-heading{if $is177} card-header{/if}">
				<i class="icon-truck"></i> {l s='DHL Delivery Labels' mod='dhldp'} <span class="pull-right">{$module_name|escape:'htmlall':'UTF-8'} v.{$module_version|escape:'htmlall':'UTF-8'}</span>
			</div>

			{if isset($dhl_errors) && $dhl_errors|@count}
				<div class="alert alert-danger"><ul>
				{foreach from=$dhl_errors item=dhl_error}
					<li>{$dhl_error|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
				</ul></div>
			{/if}

			{if isset($dhl_confirmations) && $dhl_confirmations|@count}
				<div class="alert alert-success"><ul>
				{foreach from=$dhl_confirmations item=dhl_confirmation}
					<li>{$dhl_confirmation|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul></div>
			{/if}

			{if isset($dhl_warnings) && $dhl_warnings|@count}
				<div class="alert alert-warning"><ul>
				{foreach from=$dhl_warnings item=dhl_warning}
					<li>{$dhl_warning|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul></div>
			{/if}

			{if isset($labels) && is_array($labels) && count($labels)}
			<button class="btn btn-default" name="showAllDHLDPDhlLabels" id="showAllDHLDPDhlLabels"><i class="icon-history"></i> {l s='Show all labels' mod='dhldp'} ({count($labels)|escape:'htmlall':'UTF-8'})</button>
			<div id="sectionAllDHLDPDhlLabels" style="display:none;">
				<div class="table-responsive">
					<table class="table std" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>{l s='ID' mod='dhldp'}</th>
								<th>{l s='Date' mod='dhldp'}</th>
								<th>{l s='Product / Shipment date' mod='dhldp'}</th>
								<th>{l s='Parameters' mod='dhldp'}</th>
								<th>{l s='Services' mod='dhldp'}</th>
								<th>{l s='Action' mod='dhldp'}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$labels item=label}
							<tr>
								<td>{$label.id_dhldp_label|escape:'htmlall':'UTF-8'}</td>
								<td>{$label.date_add|escape:'htmlall':'UTF-8'}</td>
								<td>
									{$label.product_name|escape:'htmlall':'UTF-8'}<br>
                                    {if isset($label.is_return) && $label.is_return == 1}
                                        <span class="badge badge-warning">{l s='Return label' mod='dhldp'}</span><br>
                                    {/if}
									{if isset($label.with_return) && $label.with_return == 1}
										<span class="badge badge-warning">{l s='With return label' mod='dhldp'}</span><br>
									{/if}
									{if $label.shipment_date != '0000-00-00 00:00:00'}
										{strftime('%Y-%m-%d', strtotime($label.shipment_date))}
									{/if}
								</td>
								<td>
									<label>{l s='Weight' mod='dhldp'}</label>: {Tools::ps_round($label.packages[0]['weight'], 1)|escape:'htmlall':'UTF-8'} {l s='kg' mod='dhldp'}<br>
									<label>{l s='Length' mod='dhldp'}</label>: {$label.packages[0]['length']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'}<br>
									<label>{l s='Width' mod='dhldp'}</label>: {$label.packages[0]['width']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'}<br>
									<label>{l s='Height' mod='dhldp'}</label>: {$label.packages[0]['height']|escape:'htmlall':'UTF-8'} {l s='cm' mod='dhldp'}<br>
								</td>
								<td>
                                    <div id="dhl_options">
										{if isset($label.options_decoded['DeclaredValueOfGoods'])}
                                        <div>
                                            <label>{l s='Declared value' mod='dhldp'}</label>
                                            <div class="input-group money-type">
                                                {$label.options_decoded['DeclaredValueOfGoods']|escape:'htmlall':'UTF-8'} {$label.options_decoded['DeclaredValueOfGoodsCurrency']|escape:'htmlall':'UTF-8'}
                                            </div>
                                        </div>
										{/if}
										{if isset($label.options_decoded['COD']['CODAmount'])}
                                        <div>
                                            <label>{l s='COD amount' mod='dhldp'}</label>
                                            <div class="input-group money-type">
												{$label.options_decoded['COD']['CODAmount']|escape:'htmlall':'UTF-8'} {$label.options_decoded['COD']['CODCurrency']|escape:'htmlall':'UTF-8'}
                                            </div>
                                        </div>
										{/if}
										{if isset($label.options_decoded['HigherInsurance']['InsuranceAmount'])}
                                        <div>
                                            <label>{l s='Insurance amount' mod='dhldp'}</label>
                                            <div class="input-group money-type">
												{$label.options_decoded['HigherInsurance']['InsuranceAmount']|escape:'htmlall':'UTF-8'} {$label.options_decoded['HigherInsurance']['InsuranceCurrency']|escape:'htmlall':'UTF-8'}
                                            </div>
                                        </div>
										{/if}
                                        {if isset($label.options_decoded['CheckMinimumAge']['MinimumAge']) && $label.options_decoded['CheckMinimumAge']['MinimumAge'] > 0}
                                        <div>
                                            <label>{l s='Check minimum age' mod='dhldp'}</label>
                                            <div class="input-group money-type">
                                                {$label.options_decoded['CheckMinimumAge']['MinimumAge']|escape:'htmlall':'UTF-8'} {l s='years' mod='dhldp'}
                                            </div>
                                        </div>
                                        {/if}
                                    </div>
                                </td>
								<td>
									<form method="post" action="{$form_action|escape:'htmlall':'UTF-8'}" class="form-inline">
									<label>{l s='Shipment number' mod='dhldp'}</label>: {$label.shipment_number|escape:'htmlall':'UTF-8'}<br>
									<a class="btn btn-primary ml-3" href="{$label.label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print label' mod='dhldp'}</a>
									{if $label.return_label_url != ''}
										<a class="btn btn-primary ml-3" href="{$label.return_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print return label' mod='dhldp'}</a>
									{/if}
									{if $label.export_label_url != ''}
										<a class="btn btn-primary ml-3" href="{$label.export_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print export doc' mod='dhldp'}</a>
									{/if}
									{if $label.cod_label_url != ''}
										<a class="btn btn-primary ml-3" href="{$label.cod_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print COD doc' mod='dhldp'}</a>
									{/if}
										<input type="hidden" name="shipment_number" value="{$label.shipment_number|escape:'htmlall':'UTF-8'}" />
										{if $label.product_code != 'rp'}
                                        <input type="submit" class="btn btn-default ml-3" name="doDHLDPDhlManifest" value="{l s='Do manifest' mod='dhldp'}">
										<input type="submit" class="btn btn-danger ml-3" name="deleteDHLDPDhlLabel" value="{l s='Delete label' mod='dhldp'}">
										{/if}
                                        <a class="btn btn-default" href="{$label.tracking_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-search"></i> {l s='Tracking' mod='dhldp'}</a>
									</form>
								</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			{/if}

			<form method="post" action="{$form_action|escape:'htmlall':'UTF-8'}" class="form-inline">
				<input type="hidden" name="id_order_carrier" value="{$carrier.id_order_carrier|escape:'htmlall':'UTF-8'}" />
				<input type="hidden" name="id_carrier" value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" />
				<input type="hidden" name="id_address" value="{$id_address|escape:'htmlall':'UTF-8'}" />

				<div class="table-responsive">
					<table class="table std" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>{l s='Carrier' mod='dhldp'}</th>
								<th>{l s='Product / Shipment date' mod='dhldp'}</th>
								<th>{l s='Parameters and Services' mod='dhldp'}</th>
								<th>{l s='Actions' mod='dhldp'}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{$carrier.carrier_name|escape:'htmlall':'UTF-8'}
								</td>
								<td>
									<div class="{if !$is177}row{/if}">
										<select id="dhldp_dhl_product_code" name="dhl_product_code" class="fixed-width-lg{if $is177} custom-select{/if}" style="display: inline-block;">
										{foreach from=$dhl_products key=dhl_product_index item=dhl_product}
											<option value="{$dhl_product.fullcode|escape:'htmlall':'UTF-8'}"{if isset($smarty.post.dhl_product_code) && $dhl_product.fullcode == $smarty.post.dhl_product_code} selected="selected"{elseif ((isset($carrier.default_dhl_product_code) && $dhl_product.fullcode == $carrier.default_dhl_product_code))} selected="selected"{elseif ((isset($last_label.product_code) && $dhl_product.fullcode == $last_label.product_code))} selected="selected"{/if}>{$dhl_product.fullname|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
										</select>
									</div>
									<div class="{if !$is177}row{/if}">
										<div class="input-group datepicker">
										<input class="form-control datepicker" type="text" data-format="YYYY-MM-DD" name="dhl_shipment_date"
											   value="{if isset($smarty.post.dhl_shipment_date)}{$smarty.post.dhl_shipment_date|escape:'htmlall':'UTF-8'}{else}{$shipment_date|escape:'htmlall':'UTF-8'}{/if}" maxlength="10"/>
										</div><span class="help-block">{l s='yyyy-mm-dd' mod='dhldp'}</span>
									</div>
								</td>
								<td>
                                    <div id="dhldp_dhl_params" class="row">
                                        <div class="col-lg-3">
                                            <label for="dhl_weight_package" class="control-label col-lg-3 required">{l s='Weight' mod='dhldp'}</label>
                                            <div class="col-lg-9">											
                                                <div class="input-group money-type">
                                                    <input type="text" id="dhl_weight_package"  class="form-control" name="dhl_weight_package" size="3" value="{if isset($smarty.post.dhl_weight_package)}{$smarty.post.dhl_weight_package|escape:'htmlall':'UTF-8'}{elseif isset($last_label.product_code)}{Tools::ps_round($last_label.packages[0]['weight'], 1)|escape:'htmlall':'UTF-8'}{else}{$total_weight|escape:'htmlall':'UTF-8'}{/if}" />
                                                    <span class="input-group-text">{l s='kg' mod='dhldp'} </span>
                                                </div>
                                                <span class="help-block"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <label for="dhl_length" class="control-label col-lg-3">{l s='Length' mod='dhldp'}</label>
                                            <div class="col-lg-9">
                                                <div class="input-group money-type">
                                                    <input type="text" size="3"  class="form-control" id="dhl_length" name="dhl_length" value="{if isset($smarty.post.dhl_length)}{$smarty.post.dhl_length|escape:'htmlall':'UTF-8'}{elseif isset($last_label.product_code)}{$last_label.packages[0]['length']|escape:'htmlall':'UTF-8'}{else}{$package_length|escape:'htmlall':'UTF-8'}{/if}" />
                                                    <span class="input-group-text">{l s='cm' mod='dhldp'} </span>
                                                </div>
                                                <span class="help-block"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <label for="dhl_width" class="control-label col-lg-3">{l s='Width' mod='dhldp'}</label>
                                            <div class="col-lg-9">
                                                <div class="input-group money-type">
                                                    <input type="text" size="3"  class="form-control" id="dhl_width" name="dhl_width" value="{if isset($smarty.post.dhl_width)}{$smarty.post.dhl_width|escape:'htmlall':'UTF-8'}{elseif isset($last_label.product_code)}{$last_label.packages[0]['width']|escape:'htmlall':'UTF-8'}{else}{$package_width|escape:'htmlall':'UTF-8'}{/if}" />
                                                    <span class="input-group-text">{l s='cm' mod='dhldp'} </span>
                                                </div>
                                                <span class="help-block"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <label for="dhl_height" class="control-label col-lg-3">{l s='Height' mod='dhldp'}</label>
                                            <div class="col-lg-9">
                                                <div class="input-group money-type">
                                                    <input type="text" size="3" class="form-control" id="dhl_height" name="dhl_height" value="{if isset($smarty.post.dhl_height)}{$smarty.post.dhl_height|escape:'htmlall':'UTF-8'}{elseif isset($last_label.product_code)}{$last_label.packages[0]['height']|escape:'htmlall':'UTF-8'}{else}{$package_height|escape:'htmlall':'UTF-8'}{/if}" />
                                                    <span class="input-group-text">{l s='cm' mod='dhldp'} </span>
                                                </div>
                                                <span class="help-block"></span>
                                             </div>
                                        </div>
                                    </div>
                                    <div class="row param-footer">
                                        <div class="col-lg-12">
                                            <button type="button" class="button btn btn-default pull-right" id="submitDHLDPDhlAdditServices" name="submitDHLDPDhlAdditServices"><i class="icon-angle-double-down"></i> {l s='Additional services' mod='dhldp'} </button>
                                            <button  type="button" class="button btn btn-default" id="submitDHLDPDhlUpdateAddress" name="submitDHLDPDhlUpdateAddress"><i class="icon-angle-double-down"></i> {l s='Update delivery address' mod='dhldp'} </button>
                                            <button  type="button" class="button btn btn-default" id="submitDHLDPDhlExportDocuments" name="submitDHLDPDhlExportDocuments"><i class="icon-angle-double-down"></i> {l s='Export documents' mod='dhldp'} </button>
                                        </div>
                                    </div>
								</td>
								<td>
									{if isset($last_label['id_dhldp_label'])}
										{if isset($last_label.is_return) && $last_label.is_return == 1}
                                            <span class="badge badge-warning">{l s='Return label' mod='dhldp'}</span><br>
                                        {/if}
										<strong>{l s='Shipment number' mod='dhldp'}</strong>: {$last_label.shipment_number|escape:'htmlall':'UTF-8'}<br>
										<a class="btn btn-primary" href="{$last_label.label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print label' mod='dhldp'}</a>
										{if $last_label.return_label_url != ''}
											<a class="btn btn-primary" href="{$last_label.return_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print return label' mod='dhldp'}</a>
										{/if}
										{if $last_label.export_label_url != ''}
											<a class="btn btn-primary" href="{$last_label.export_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print export doc' mod='dhldp'}</a>
										{/if}
										{if $last_label.cod_label_url != ''}
											<a class="btn btn-primary" href="{$last_label.cod_label_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print COD doc' mod='dhldp'}</a>
										{/if}
										<input type="submit" class="btn btn-success" name="submitDHLDPDhlLabelRequest" value="{l s='Generate new label' mod='dhldp'}" /><br>
									{if isset($with_return) && $with_return == true}
									<input type="submit" class="btn btn-success" name="submitDhlLabelWithReturnRequest" value="{l s='Generate label with return' mod='dhldp'}" /><br>	{/if}
									<input type="hidden" name="shipment_number" value="{$last_label.shipment_number|escape:'htmlall':'UTF-8'}" />
										{if $last_label.product_code != 'rp'}
                                        <input type="submit" class="btn btn-default" name="doDHLDPDhlManifest" value="{l s='Do manifest' mod='dhldp'}">
										<input type="submit" class="btn btn-danger" name="deleteDHLDPDhlLabel" value="{l s='Delete label' mod='dhldp'}">
										{/if}
                                        {if isset($enable_return) && $enable_return == true}
                                        <button type="submit" class="btn btn-warning" name="submitDHLDPDhlLabelReturnRequest"><i class="icon-reply"></i> {l s='Return label' mod='dhldp'}</button><br>
                                        {/if}
                                        <a class="btn btn-default" href="{$label.tracking_url|escape:'html':'UTF-8'}" target="_blank"><i class="icon-search"></i> {l s='Tracking' mod='dhldp'}</a>
									{else}
										<input type="submit" class="btn btn-success" name="submitDHLDPDhlLabelRequest" value="{l s='Generate label' mod='dhldp'}" />
										{if isset($with_return) && $with_return == true}
										<br><input type="submit" class="btn btn-success" name="submitDHLDPDhlLabelWithReturnRequest" value="{l s='Generate label with return' mod='dhldp'}" />
										{/if}
									{/if}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			{if $is177}
				{include file="$self/views/templates/hook/177/additional-services.tpl"}
				{include file="$self/views/templates/hook/177/export-documents.tpl"}
			{else}
				{include file="$self/views/templates/hook/additional-services.tpl"}
				{include file="$self/views/templates/hook/export-documents.tpl"}
			{/if}
				{include file="$self/views/templates/hook/update-address.tpl"}
            </form>
		</div>
	</div>

</div>