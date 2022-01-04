{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{$mark_no_matches = $hidden_inputs.hide_zero_matches || $hidden_inputs.dim_zero_matches}

{*** main layout is coming after functions ***}
{function renderBoxes type = 'checkbox' label_type = 'checkbox' color_display = 0 values = [] root = true}
	{if $values|count}
	<ul class="{if !$root}child-categories{else if $color_display == 1}af-inline-colors{/if}">
	{$before_cut = count($values)}{if !empty($filter.cut_off)}{$before_cut = $filter.cut_off}{/if}
	{foreach $values as $v}
		{$count = 0}{if !empty($count_data[$filter.first_char][$v.id])}{$count = $count_data[$filter.first_char][$v.id]}{/if}
		{$children = []}{if $check_for_children && !empty($filter.values[$v.id])}{$children = $filter.values[$v.id]}{/if}
		{$is_customer_filter = !empty($applied_customer_filters[$k][$v.id])}
		<li class="item-{$v.identifier|escape:'html':'UTF-8'}{if !empty($v.selected)} active{/if}{if !empty($children)} af-parent-category{/if}{if $mark_no_matches && !$count} no-matches{/if}{if $is_customer_filter} has-customer-filter{/if}{if !$before_cut} cut{/if}">
			<label for="{$v.identifier|escape:'html':'UTF-8'}" class="af-{$label_type|escape:'html':'UTF-8'}-label{if $is_customer_filter} customer-filter-label" data-id="{$v.identifier|escape:'html':'UTF-8'}{/if}"{if $color_display == 1} title="{$v.name|escape:'html':'UTF-8'}"{/if}>
				{if !empty($v.color_style)}<span class="af-color-box{if !empty($v.bright)} bright{/if}" style="{$v.color_style|escape:'html':'UTF-8'}">{/if}
				{if !$is_customer_filter}
					<input type="{$type|escape:'html':'UTF-8'}" id="{$v.identifier|escape:'html':'UTF-8'}" class="af {$type|escape:'html':'UTF-8'}" name="{$filter.submit_name|escape:'html':'UTF-8'}" value="{$v.id|escape:'html':'UTF-8'}" data-url="{$v.link|escape:'html':'UTF-8'}"{if !empty($v.selected)} checked{/if}>
				{else}
					<a href="#" class="{$af_classes['icon-lock']|escape:'html':'UTF-8'}"></a><input type="hidden" id="{$v.identifier|escape:'html':'UTF-8'}" class="af {$type|escape:'html':'UTF-8'} customer-filter" name="{$filter.submit_name|escape:'html':'UTF-8'}" value="{$v.id|escape:'html':'UTF-8'}" data-name="{$filter.submit_name|escape:'html':'UTF-8'}" data-url="{$v.link|escape:'html':'UTF-8'}">
				{/if}
				{if !empty($v.color_style)}</span>{/if}
				<span class="name">{$v.name|escape:'html':'UTF-8'}</span>
				{if !empty($children)}<a href="#" class="af-toggle-child"><span class="hidden-on-open">&plus;</span><span class="visible-on-open">&minus;</span></a>{/if}
				{if $hidden_inputs.count_data}<span class="count">{$count|intval}</span>{/if}
			</label>
			{if $before_cut && (!$hidden_inputs.hide_zero_matches || $count)}{$before_cut = $before_cut - 1}{/if}
			{if !empty($children)}
				{renderBoxes type = $type label_type = $label_type color_display = $color_display values = $children  root = false}
			{/if}
		</li>
	{/foreach}
	</ul>
	{/if}
{/function}

{function renderOptions values = [] nesting_prefix = ''}
	{foreach $values as $v}
		{$count = 0}{if !empty($count_data[$filter.first_char][$v.id])}{$count = $count_data[$filter.first_char][$v.id]}{/if}
		{$is_customer_filter = !empty($applied_customer_filters[$k][$v.id])}
		{if $count || !empty($v.selected) || $is_customer_filter || !$hidden_inputs.hide_zero_matches}
			<option id="{$v.identifier|escape:'html':'UTF-8'}" value="{$v.id|escape:'html':'UTF-8'}" class="{if $is_customer_filter}customer-filter{/if}{if $mark_no_matches && !$count} no-matches{/if}" data-url="{$v.link|escape:'html':'UTF-8'}" data-text="{$v.name|escape:'html':'UTF-8'}"{if !empty($v.selected)} selected{/if}{if !$count && $hidden_inputs.dim_zero_matches} disabled{/if}>
				{if $nesting_prefix}{$nesting_prefix|escape:'html':'UTF-8'}{/if}
				{$v.name|escape:'html':'UTF-8'}
				{if $hidden_inputs.count_data}({$count|intval}){/if}
			</option>
			{if $check_for_children && !empty($filter.values[$v.id])}
				{renderOptions values = $filter.values[$v.id] nesting_prefix = $nesting_prefix|cat:'-'}
			{/if}
		{/if}
	{/foreach}
{/function}

{function renderAvailableSelectOptions values = [] nesting_prefix = ''}
	{foreach $values as $v}
		<span class="{if !empty($applied_customer_filters[$k][$v.id])}customer-filter{/if}" data-value="{$v.id|escape:'html':'UTF-8'}" data-url="{$v.link|escape:'html':'UTF-8'}" data-text="{if $nesting_prefix}{$nesting_prefix|escape:'html':'UTF-8'} {/if}{$v.name|escape:'html':'UTF-8'}" data-id="{$v.identifier|escape:'html':'UTF-8'}"></span>
		{if $check_for_children && !empty($filter.values[$v.id])}
			{renderAvailableSelectOptions values = $filter.values[$v.id] nesting_prefix = $nesting_prefix|cat:'-'}
		{/if}
	{/foreach}
{/function}

<div id="amazzing_filter" class="af block {$af_layout_type|escape:'html':'UTF-8'}-layout {$hook_name|escape:'html':'UTF-8'}{if !$hidden_inputs.count_data} hide-counters{/if}{if $hidden_inputs.hide_zero_matches} hide-zero-matches{/if}{if $hidden_inputs.dim_zero_matches} dim-zero-matches{/if}{if $hidden_inputs.compact_offset == 1} compact-offset-left{/if}{if !empty($is_iphone)} is-iphone{/if}"{if !$filters} style="display:none"{/if}>
	<h2 class="title_block">
		{if $current_controller == 'index'}{l s='Instant filter' mod='amazzingfilter'}{else}{l s='Filter by' mod='amazzingfilter'}{/if}
	</h2>
	<div class="block_content">
		<div class="selectedFilters clearfix hidden{if $af_layout_type == 'horizontal'} inline{/if}">
			{if $hidden_inputs.sf_position == 1}<span class="selected-filters-label">{l s='Filters:' mod='amazzingfilter'}</span>{/if}
			<div class="clearAll">
				<a href="#" class="all">
					<span class="txt">{l s='Clear' mod='amazzingfilter'}</span>
					<i class="{$af_classes['icon-eraser']|escape:'html':'UTF-8'}"></i>
				</a>
			</div>
		</div>
		<form action="#" id="af_form">
			<span class="hidden_inputs">
				{foreach $hidden_inputs as $name => $value}
					<input type="hidden" id="af_{$name|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}">
				{/foreach}
				{foreach $extra_hidden_inputs as $k => $inputs}
					{foreach $inputs as $key => $grouped_options}
						{foreach $grouped_options as $id_group => $options}
							<input type="hidden" name="{$k|escape:'html':'UTF-8'}[{$key|escape:'html':'UTF-8'}][{$id_group|escape:'html':'UTF-8'}]" value="{$options|implode:','|escape:'html':'UTF-8'}">
						{/foreach}
					{/foreach}
				{/foreach}
			</span>
			{foreach $filters as $k => $filter}
			{if $filter.type == 'hidden'}
				{if isset($filter.forced_values)}
					{foreach $filter.forced_values as $value_id => $value_link}
						<input type="hidden" name="{$filter.submit_name|escape:'html':'UTF-8'}" value="{$value_id|escape:'html':'UTF-8'}" class="sp-hidden-filter" data-group="{$filter.link|escape:'html':'UTF-8'}" data-url="{$value_link|escape:'html':'UTF-8'}">
					{/foreach}
				{/if}
			{else if !empty($filter.values)}
			{$check_for_children = !empty($filter.id_parent) && !empty($filter.values[$filter.id_parent])}
			<div class="af_filter clearfix {array_keys($filter.classes)|implode:' '|escape:'html':'UTF-8'}" data-key="{$filter.first_char|escape:'html':'UTF-8'}" data-url="{$filter.link|escape:'html':'UTF-8'}" data-type="{$filter.type|intval}">
				<div class="af_subtitle_heading{if !empty($filter.special)} hidden{/if}">
					<h5 class="af_subtitle">{$filter.name|escape:'html':'UTF-8'}</h5>
				</div>
				<div class="af_filter_content">
				{if !empty($filter.quick_search)}
					<div class="af-quick-search">
						<input type="text" class="qsInput" data-notrigger="1"{if $check_for_children} data-tree="1"{/if}>
						<div class="alert-warning qs-no-matches hidden">{l s='no matches' mod='amazzingfilter'}</div>
					</div>
				{/if}
				{if $filter.type == 1 || $filter.type == 2}
					{$values = $filter.values}
					{if $check_for_children}{$values = $filter.values[$filter.id_parent]}{/if}
					{$type = 'checkbox'}{if $filter.type == 2}{$type = 'radio'}{/if}
					{$label_type = $type}{if !empty($filter.textbox)}{$label_type='textbox'}{/if}
					{$color_display = 0}{if !empty($filter.color_display)}{$color_display = $filter.color_display}{$label_type='color'}{/if}
					{renderBoxes type = $type label_type = $label_type color_display = $color_display values = $values}
				{else if $filter.type == 3}
					{$values = $filter.values}
					{if $check_for_children}{$values = $filter.values[$filter.id_parent]}{/if}
					{if !empty($applied_customer_filters[$k])}
						{$customer_filter_id = current(array_keys($applied_customer_filters[$k]))}
						{$customer_filter_name = current($applied_customer_filters[$k])}
						<label class="customer-filter-label for-select" data-id="{$filter.first_char|escape:'html':'UTF-8'}-{$customer_filter_id|escape:'html':'UTF-8'}">
							<a href="#" class="{$af_classes['icon-lock']|escape:'html':'UTF-8'}"></a>
							<span class="name">{$customer_filter_name|escape:'html':'UTF-8'}</span>
						</label>
						<div class="selector-with-customer-filter hidden">
					{/if}
					<select id="selector-{$k|escape:'html':'UTF-8'}" class="af-select form-control form-control-select" name="{$filter.submit_name|escape:'html':'UTF-8'}">
						<option value="" class="first">{if !empty($filter.first_option)}{$filter.first_option|escape:'html':'UTF-8'}{else}--{/if}</option>
						{renderOptions values = $values}
					</select>
					<div class="dynamic-select-options hidden">
						{renderAvailableSelectOptions values = $values}
					</div>
					{if !empty($applied_customer_filters[$k])}</div>{/if}
				{else if $filter.type == 4}
					<div class="{$k|escape:'html':'UTF-8'}_slider af-slider" data-url="{$filter.link|escape:'html':'UTF-8'}" data-type="{$k|escape:'html':'UTF-8'}">
						<div class="slider-bar" data-step="{$filter.slider_step|floatval}">{* filled dynamically *}</div>
						<div class="slider-values">
							<span class="from_display slider_value">
								<span class="prefix">{$filter.prefix|escape:'html':'UTF-8'}</span><span class="value"></span><span class="suffix">{$filter.suffix|escape:'html':'UTF-8'}</span>
								<input type="text" id="{$k|escape:'html':'UTF-8'}_from" class="input-text" name="{$filter.submit_name|escape:'html':'UTF-8'}[from]" value="{$filter.values.from|floatval}" >
								<input type="hidden" id="{$k|escape:'html':'UTF-8'}_min" name="{$filter.submit_name|escape:'html':'UTF-8'}[min]" value="{$filter.values.min|floatval}" >
							</span>
							<span class="to_display slider_value">
								<span class="prefix">{$filter.prefix|escape:'html':'UTF-8'}</span><span class="value"></span><span class="suffix">{$filter.suffix|escape:'html':'UTF-8'}</span>
								<input type="text" id="{$k|escape:'html':'UTF-8'}_to" class="input-text" name="{$filter.submit_name|escape:'html':'UTF-8'}[to]" value="{$filter.values.to|floatval}">
								<input type="hidden" id="{$k|escape:'html':'UTF-8'}_max" name="{$filter.submit_name|escape:'html':'UTF-8'}[max]" value="{$filter.values.max|floatval}">
							</span>
						</div>
					</div>
				{/if}
				</div>
				{if !empty($filter.cut_off)}
					<a href="#" class="toggle-cut-off" data-cut="{$filter.cut_off|intval}">
						<span class="more">{l s='more...' mod='amazzingfilter'}</span>
						<span class="less">{l s='less' mod='amazzingfilter'}</span>
					</a>
				{/if}
			</div>
			{/if}
			{/foreach}
		</form>
		<div class="btn-holder hidden">
			<a href="#" class="btn btn-primary full-width viewFilteredProducts{if $hidden_inputs.reload_action != 2} hidden{/if}">
				{l s='View products' mod='amazzingfilter'} <span class="af-total-count">{$total_products|intval}</span>
			</a>
			{if !empty($my_filters_link)}
				<a href="{$my_filters_link|escape:'html':'UTF-8'}" class="btn btn-default manage-permanent-filters full-width" target="_blank">
					{l s='Manage permanent filters' mod='amazzingfilter'}
				</a>
			{/if}
		</div>
	</div>
	<a href="#" class="btn-primary compact-toggle type-{$hidden_inputs.compact_btn|intval}">
		<span class="{$af_classes['icon-filter']|escape:'html':'UTF-8'} compact-toggle-icon"></span>
		<span class="compact-toggle-text">{l s='Filter' mod='amazzingfilter'}</span>
	</a>
</div>
<div class="af-compact-overlay"></div>
{* since 3.1.6 *}
