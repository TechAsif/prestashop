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
<div class="row dhldp_dp_panel">
	<div class="col-lg-12">
		<div class="panel dhldp_dp_order_panel {if $is177}card card-body{/if}">
			<div class="panel-heading">
				<i class="icon-truck"></i> {l s='Deutschepost delivery labels' mod='dhldp'} <span class="pull-right">{$module_name|escape:'htmlall':'UTF-8'} v.{$module_version|escape:'htmlall':'UTF-8'}</span>
			</div>
		
			{if isset($deutschepost_errors) && $deutschepost_errors|@count}
				{foreach from=$deutschepost_errors item=error}
			<p class="alert alert-danger">{$error|escape:'htmlall':'UTF-8'}</p>
				{/foreach}
			{/if}

			{if isset($labels) && is_array($labels) && count($labels)}
			<button class="btn btn-default" name="showAllDPLabels" id="showAllDPLabels"><i class="icon-history"></i> {l s='Show all labels' mod='dhldp'} ({count($labels)|escape:'htmlall':'UTF-8'})</button>
			<div id="allDPLabels" style="display:none;">
				<div class="table-responsive">
					<table class="table std" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>{l s='ID' mod='dhldp'}</th>
								<th>{l s='Date' mod='dhldp'}</th>
								<th>{l s='Product' mod='dhldp'}</th>
								<th>{l s='Information' mod='dhldp'}</th>
								<th>{l s='Action' mod='dhldp'}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$labels item=label}
							<tr>
								<td>{$label.id_dhldp_dp_label|escape:'htmlall':'UTF-8'}</td>
								<td>{$label.date_add|escape:'htmlall':'UTF-8'}</td>
								<td>{$label.product|escape:'htmlall':'UTF-8'}<br>
									{$label.product_name|escape:'htmlall':'UTF-8'}<br>
								</td>
								<td>
									<label>{l s='Voucher ID' mod='dhldp'}</label>: {$label.dp_voucher_id|escape:'htmlall':'UTF-8'}<br>
                                    {if $label.dp_track_id != ''}<label>{l s='Track ID' mod='dhldp'}</label>: {$label.dp_track_id|escape:'htmlall':'UTF-8'}<br>{/if}
									<label>{l s='Order ID' mod='dhldp'}</label>: {$label.dp_order_id|escape:'htmlall':'UTF-8'}<br>
									<label>{l s='Total' mod='dhldp'}</label>: {displayPrice price=$label.total}<br>
									<label>{l s='Remained wallet ballance' mod='dhldp'}</label>: {displayPrice price=$label.wallet_ballance}<br>
									<label>{l s='Note' mod='dhldp'}</label>: {$label.additional_info|escape:'htmlall':'UTF-8'}<br>
                                    <label>{l s='Label format' mod='dhldp'}</label>: {$label.label_format|escape:'htmlall':'UTF-8'}<br>
                                    {if $label.label_format == 'pdf'}
                                        <label>{l s='Page format' mod='dhldp'}</label>: {$label.page_format_name|escape:'htmlall':'UTF-8'}<br>
                                        <label>{l s='Label position' mod='dhldp'}</label>: {$label.label_position_name|escape:'htmlall':'UTF-8'}<br>
                                    {/if}
								</td>
								<td>
									<a class="btn btn-primary" href="{$label.dp_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print label' mod='dhldp'}</a>
                                    {if $label.manifest_link != ''}<a class="btn btn-primary" href="{$label.manifest_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-info"></i> {l s='Manifest / Shipping list' mod='dhldp'}</a>{/if}
									{if $label.dp_track_link != ''}<a class="btn btn-primary" href="{$label.dp_track_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-info"></i> {l s='Tracking' mod='dhldp'}</a>{/if}
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
								<th>{l s='Product' mod='dhldp'}</th>
								<th>{l s='Note' mod='dhldp'}</th>
                                <th>{l s='Label print options' mod='dhldp'}</th>
								<th>{l s='Actions' mod='dhldp'}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{$carrier.carrier_name|escape:'htmlall':'UTF-8'}
								</td>
								<td>
									<select name="product" class="fixed-width-lg {if $is177}custom-select{/if}" style="display: inline-block;">
									{foreach from=$deutcshepost_products key=deutcshepost_product_index item=deutcshepost_product}
										<option value="{$deutcshepost_product.code|escape:'htmlall':'UTF-8'}"{if ((isset($last_label.product) && $deutcshepost_product.code == $last_label.product) || (!isset($last_label.product) && $predef_deutschepost_product==$deutcshepost_product.code))} selected{/if}>{$deutcshepost_product.name|escape:'htmlall':'UTF-8'}
 - {$deutcshepost_product.price|escape:'htmlall':'UTF-8'} Euro</option>
									{/foreach}
									</select>
								</td>
								<td>
									<div class="input-group fixed-width-x2">
										<textarea name="additional_info">{if isset($last_label.additional_info)}{$last_label.additional_info|escape:'htmlall':'UTF-8'}{/if}</textarea> {l s='Max. 80 characters' mod='dhldp'}
									</div>
								</td>
                                <td>
                                    <label class="control-label">{l s='Label format' mod='dhldp'}</label> : {$def_label_format|escape:'htmlall':'UTF-8'}<br>
                                    {if $def_label_format == 'pdf'}
                                        <label class="control-label">{l s='Page format' mod='dhldp'}</label> : {$def_page_format_name|escape:'htmlall':'UTF-8'}<br>
                                        <label class="control-label">{l s='Label position' mod='dhldp'}</label> :<br>
                                                {if isset($smarty.post.label_position_page)}
                                                    {assign var=sel_label_format_page value=$smarty.post.label_position_page}
                                                    {assign var=sel_label_format_col value=$smarty.post.label_position_col}
                                                    {assign var=sel_label_format_row value=$smarty.post.label_position_row}
                                                {elseif isset($last_label.label_format) && $last_label.label_format == 'pdf'}
                                                    {assign var=sel_label_format_page value=$last_label.label_position_detail.page}
                                                    {assign var=sel_label_format_col value=$last_label.label_position_detail.col}
                                                    {assign var=sel_label_format_row value=$last_label.label_position_detail.row}
                                                {else}
                                                    {assign var=sel_label_format_page value=$def_label_position_page}
                                                    {assign var=sel_label_format_col value=$def_label_position_col}
                                                    {assign var=sel_label_format_row value=$def_label_position_row}
                                                {/if}
                                                <table  class="table std" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td>{l s='Page' mod='dhldp'}</td>
                                                        <td><input type="text" name="label_position_page" class="fixed-width-xs" value="{$sel_label_format_page|escape:'htmlall':'UTF-8'}"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>{l s='Column' mod='dhldp'}</td>
                                                        <td><input type="text" name="label_position_col" class="fixed-width-xs" value="{$sel_label_format_col|escape:'htmlall':'UTF-8'}"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>{l s='Row' mod='dhldp'}</td>
                                                        <td><input type="text" name="label_position_row" class="fixed-width-xs" value="{$sel_label_format_row|escape:'htmlall':'UTF-8'}"></td>
                                                    </tr>
                                                </table>
                                    {/if}
                                </td>
								<td>
									{if isset($last_label['id_dhldp_dp_label'])}
										<label>{l s='Voucher ID' mod='dhldp'}</label>: {$last_label.dp_voucher_id|escape:'htmlall':'UTF-8'}<br>
                                        {if $last_label.dp_track_id != ''}<label>{l s='Track ID' mod='dhldp'}</label>: {$last_label.dp_track_id|escape:'htmlall':'UTF-8'}<br>{/if}
										<label>{l s='Order ID' mod='dhldp'}</label>: {$last_label.dp_order_id|escape:'htmlall':'UTF-8'}<br>
										<label>{l s='Total' mod='dhldp'}</label>: {displayPrice price=$last_label.total}<br>
										<label>{l s='Remained wallet ballance' mod='dhldp'}</label>: {displayPrice price=$last_label.wallet_ballance}<br>
										<label>{l s='Note' mod='dhldp'}</label>: {$last_label.additional_info|escape:'htmlall':'UTF-8'}<br>

										<a class="btn btn-primary" href="{$last_label.dp_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-print"></i> {l s='Print label' mod='dhldp'}</a>
                                        {if $last_label.manifest_link != ''}<a class="btn btn-primary" href="{$last_label.manifest_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-info"></i> {l s='Manifest / Shipping list' mod='dhldp'}</a>{/if}
										{if $last_label.dp_track_link != ''}<a class="btn btn-primary" href="{$last_label.dp_track_link|escape:'htmlall':'UTF-8'}" target="_blank"><i class="icon-info"></i> {l s='Tracking' mod='dhldp'}</a>{/if}
										<input type="submit" class="btn btn-success" name="submitDPLabelRequest" value="{l s='Generate new label' mod='dhldp'}" />
										{*<button type="submit" class="btn btn-danger" name="submitDeutschepostLabelReturnRequest"><i class="icon-reply"></i> {l s='Return label' mod='dhldp'}</button>*}
									{else}
										<input type="submit" class="btn btn-success" name="submitDPLabelRequest" value="{l s='Generate label' mod='dhldp'}" />
									{/if}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>