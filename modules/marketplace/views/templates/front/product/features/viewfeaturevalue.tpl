{*
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*}

{extends file=$layout}
{block name='content'}
{if isset($smarty.get.success_attr)}
	<div class="error">
		{if $smarty.get.success_attr == 1}
			<div class="alert alert-success">{l s='Feature value added successfully.' mod='marketplace'}</div>
		{else if $smarty.get.success_attr == 2}
			<div class="alert alert-success">{l s='Feature value updated successfully.' mod='marketplace'}</div>
		{else if $smarty.get.success_attr == 3}
			<div class="alert alert-success">{l s='Feature value deleted successfully.' mod='marketplace'}</div>
		{/if}
	</div>
{/if}
{if isset($smarty.get.error_attr)}
	<div class="error">
		{if $smarty.get.error_attr == 1}
			<div class="alert alert-danger">{l s='This feature value is already in use you cannot edit or delete it.' mod='marketplace'}</div>
		{/if}
	</div>
{/if}
{if $logged}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color|escape:'html':'UTF-8'};">
				<span style="color:{$title_text_color|escape:'html':'UTF-8'};">{if isset($feature_name)}{$feature_name|escape:'html':'UTF-8'}{/if}</span>
			</div>
			<div class="wk-mp-right-column">
				<div class="wk_product_list">
					<p class="wk_text_right">
						<a href="{$link->getModuleLink('marketplace', 'addfeaturevalue')|escape:'html':'UTF-8'}">
							<button class="btn btn-primary btn-sm" type="button">
								<i class="material-icons">&#xE145;</i>
								{l s='Add Value' mod='marketplace'}
							</button>
						</a>
					</p>
					<div class="table-responsive">
						<table class="table table-striped" {if !(isset($empty_list))}id="wk_datatable_list"{/if}>
							<thead>
								<tr>
									<th>{l s='#' mod='marketplace'}</th>
									<th>{l s='ID' mod='marketplace'}</th>
									<th>{l s='Values' mod='marketplace'}</th>
									<th>{l s='Actions' mod='marketplace'}</th>
								</tr>
							</thead>
							{if !(isset($empty_list))}
								{assign var=num value=1}
								{foreach $value_set as $value_set_each}
									<tr>
										<td>{$num|escape:'html':'UTF-8'}</td>
										<td>{$value_set_each['id']|escape:'html':'UTF-8'}</td>
										<td>{$value_set_each['val_name']|escape:'html':'UTF-8'}</td>
										<td>
											<a class="edit_button_v" title="{l s='Edit' mod='marketplace'}" edit="{$value_set_each['editable']|escape:'html':'UTF-8'}" href="{$link->getModuleLink('marketplace', 'addfeaturevalue',['id_feature_value' => $value_set_each['editable'],'id_feature'=>$id_feature])|addslashes}">
												<i class="material-icons">&#xE254;</i>
											</a>
											<a class="delete_button_v" title="{l s='Delete' mod='marketplace'}" edit="{$value_set_each['editable']|escape:'html':'UTF-8'}" href="{$link->getModuleLink('marketplace', 'viewfeaturevalue',['id_feature_value'=>$value_set_each['editable'], 'id_feature'=>$id_feature, 'delete_feature_val'=>1])|addslashes}">
												<i class="material-icons">&#xE872;</i>
											</a>
										</td>
									</tr>
									{$num = $num + 1}
								{/foreach}
							{else}
								<tr>
									<td colspan="4">
										<div id="empty_list">{l s='This feature have no values yet.' mod='marketplace'}</div>
									</td>
								</tr>
							{/if}
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}
{/block}