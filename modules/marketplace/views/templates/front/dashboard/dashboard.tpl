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
{if $logged}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='My Dashboard' mod='marketplace'}</span>
			</div>
			<div class="clearfix wk-mp-right-column">
				{* Page content here *}
				<div class="row">
					<div class="col-sm-12">
						<p>
							<strong>{l s='Hello' mod='marketplace'}, {$seller_name}!</strong>
							<br>
							{l s='From your My Account Dashboard, You have the ability to view a snapshot of your recent account activity and update your account information.' mod='marketplace'}
						</p>
					</div>
				</div>
				{hook h='displayMpDashboardTop'}
				<div class="dashboard-date-content">
					<div class="row">
						<div class="col-md-7">
							<div class="btn-group margin-btm-10">
								{*Display current day graph*}
								<button data-trigger-function="Day" data-date-range="1" class="btn setPreselectDateRange {if $preselectDateRange == '1'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Day' mod='marketplace'}
								</button>
								{*Display current month graph*}
								<button data-trigger-function="Month" data-date-range="2" class="btn setPreselectDateRange {if $preselectDateRange == '2'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Month' mod='marketplace'}
								</button>
								{*Display current year graph*}
								<button data-trigger-function="Year" data-date-range="3" class="btn setPreselectDateRange {if $preselectDateRange == '3'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Year' mod='marketplace'}
								</button>
								{*Display previous day graph*}
								<button data-trigger-function="PreviousDay" data-date-range="4" class="btn setPreselectDateRange {if $preselectDateRange == '4'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Day-1' mod='marketplace'}
								</button>
								{*Display previous month graph*}
								<button data-trigger-function="PreviousMonth" data-date-range="5" class="btn setPreselectDateRange {if $preselectDateRange == '5'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Month-1' mod='marketplace'}
								</button>
								{*Display previous year graph*}
								<button data-trigger-function="PreviousYear" data-date-range="6" class="btn setPreselectDateRange {if $preselectDateRange == '6'}btn-primary{else}btn-default{/if}" type="button">
									{l s='Year-1' mod='marketplace'}
								</button>
							</div>
						</div>
						<div class="col-md-5">
							<input type="hidden" id="dashboardDateFrom" name="dashboardDateFrom" value="{$dateFrom|date_format:"%Y-%m-%d"}">
							<input type="hidden" id="dashboardDateTo" name="dashboardDateTo" value="{$dateTo|date_format:"%Y-%m-%d"}">
							<input type="hidden" name="preselectDateRange" id="preselectDateRange" value="{$preselectDateRange}">
							<div class="input-group">
								<span class="input-group-addon"><i class="material-icons">&#xE8A3;</i></span>
								<input type="text" class="form-control" id="date-range-picker">
							</div>
						</div>
					</div>
				</div>
				<div class="panel">
					<h4><i class="material-icons">&#xE1B8;</i> {l s='Stats Graph' mod='marketplace'}</h4>
					{if Configuration::get('WK_MP_DASHBOARD_GRAPH') == '1'}
						<span>{l s='Graph is showing on the basis of Payment Accepted Orders.' mod='marketplace'}</span>
					{else if Configuration::get('WK_MP_DASHBOARD_GRAPH') == '2'}
						<span>{l s='Graph is showing on the basis of Confirmed Orders.' mod='marketplace'}</span>
					{/if}
					<br>
					<span>{l s='Sales will calculate on basis of product(tax excl.) price only. Shipping amount will not calculate in sales.' mod='marketplace'}</span>
					<br><br>
					<div class="panel-content">
						{block name='mp-view-graph'}
							{include file='module:marketplace/views/templates/front/dashboard/_partials/view-graph.tpl'}
						{/block}
					</div>
				</div>
				<hr>
				<div class="panel">
					<h4><i class="material-icons">&#xE870;</i> {l s='Recent Orders' mod='marketplace'}</h4>
					<div class="panel-content">
						<div class="row tabs">
							<div class="col-sm-12">
								{block name='mp-recent-orders'}
									{include file='module:marketplace/views/templates/front/dashboard/_partials/recent-orders.tpl'}
								{/block}
							</div>
						</div>
					</div>
				</div>
				{hook h='displayMpDashboardBottom'}
			</div>
		</div>
	</div>
{/if}
<link href="{$smarty.const._MODULE_DIR_}marketplace/views/css/libs/graph/nv.d3.css" rel="stylesheet">
<script src="{$smarty.const._MODULE_DIR_}marketplace/views/js/libs/graph/d3.v3.min.js"></script>
<script src="{$smarty.const._MODULE_DIR_}marketplace/views/js/libs/graph/nv.d3.min.js"></script>
{/block}