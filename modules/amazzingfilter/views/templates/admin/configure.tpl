{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if !empty($js_vars)}
<script type="text/javascript">{foreach $js_vars as $name => $value}
	var {$name} = {$value|json_encode nofilter};{* can not be escaped *}
{/foreach}</script>
{/if}

{if $files_update_warnings}
	<div class="alert alert-warning">
		{l s='Some of your customized files have been updated in the new version' mod='amazzingfilter'}
		<ul>
		{foreach $files_update_warnings as $file => $identifier}
			<li>
				{$file|escape:'html':'UTF-8'}
				<span class="warning-advice">
					{l s='Make sure you update this file in your theme folder, and insert the following code to the last line' mod='amazzingfilter'}:
					<span class="code">{$identifier|escape:'html':'UTF-8'}</span>
				</span>
			</li>
		{/foreach}
		</ul>
	</div>
{/if}
{function renderElement type='saveMultipleSettingsBtn' cls=''}
	{if $type == 'saveMultipleSettingsBtn'}
		<div class="panel-footer multiple-settings">
			<button type="button" class="saveMultipleSettings btn btn-default{if $cls} {$cls|escape:'html':'UTF-8'}{/if}">
				<i class="process-icon-save"></i> {l s='Save' mod='amazzingfilter'}
			</button>
		</div>
	{else if $type == 'resetBtn'}
		<a href="#" class="resetSelectors"><i class="icon-undo"></i> {l s='Reset' mod='amazzingfilter'}</a>
	{/if}
{/function}
<div class="bootstrap af clearfix">
	<div class="menu-panel col-lg-2 col-md-3">
		<div class="list-group">
			<a href="#indexation" class="list-group-item{if $indexation_required} active{/if}"><i class="icon-list"></i> {l s='Indexation' mod='amazzingfilter'} <i class="icon-exclamation indexation-warning{if !$indexation_required} hidden{/if}"></i></a>
			<a href="#filter-templates" class="list-group-item{if !$indexation_required} active{/if}"><i class="icon-filter"></i> {l s='Filter templates' mod='amazzingfilter'}</a>
			<a href="#hook-settings" class="list-group-item"><i class="icon-anchor"></i> {l s='Hook settings' mod='amazzingfilter'}</a>
			<a href="#general-settings" class="list-group-item"><i class="icon-cogs"></i> {l s='General settings' mod='amazzingfilter'}</a>
			<a href="#selector-settings" class="list-group-item"><i class="icon-code"></i> {l s='Layout classes/ids' mod='amazzingfilter'}</a>
			<a href="#caching-settings" class="list-group-item"><i class="icon-tachometer"></i> {l s='Caching settings' mod='amazzingfilter'}</a>
			<a href="#customer-filters" class="list-group-item"><i class="icon-user"></i> {l s='Customer filters' mod='amazzingfilter'}</a>
			{if $overrides_data}
				<a href="#overrides" class="list-group-item"><i class="icon-file-text-o"></i> {l s='Overrides' mod='amazzingfilter'}</a>
			{/if}
			<a href="#info" class="list-group-item"><i class="icon-info-circle"></i> {l s='Information' mod='amazzingfilter'}</a>
		</div>
		{if !empty($sp.tpl.side_panel)}
			{include file=$sp.tpl.side_panel}
		{/if}
		<div class="list-group">
			{foreach $merged_data as $type => $data}
				{$merged_key = 'merged_'|cat:$type|cat:'s'}
				<a href="#merged-{$type|escape:'html':'UTF-8'}s" class="list-group-item{if empty($settings.general.$merged_key.value)} hidden{/if}">
				<i class="icon-sitemap icon-rotate-90"></i> {$data.title|escape:'html':'UTF-8'}</a>
			{/foreach}
		</div>
		<label class="instant-settings-label hidden">{l s='Instantly save settings' mod='amazzingfilter'} <input type="checkbox" class="instant-settings"></label>
	</div>
	<div class="panel tab-content col-lg-10 col-md-9">
		{if !empty($sp.tpl.center_panel)}
			{include file=$sp.tpl.center_panel}
		{/if}
		<div id="indexation" class="tab-pane{if $indexation_required} active{/if}">
			<h3>{l s='Data indexation' mod='amazzingfilter'}</h3>
			<div class="indexation-data row">
				{foreach $indexation_data as $id_shop => $data}
				<div class="col-lg-4 grid-item">
					<div class="shop-indexation-data{if !$data.missing} complete{/if}" data-shop="{$id_shop|intval}">
						<div class="shop-name">{$data.shop_name|escape:'html':'UTF-8'} <i class="icon-check visible-on-complete"></i></div>
						{l s='Total indexed' mod='amazzingfilter'}: <span class="count indexed">{$data.indexed|intval}</span><br>
						{l s='Missing in index' mod='amazzingfilter'}: <span class="count missing">{$data.missing|intval}</span><br>
						<div class="indexation-actions">
							<a href='#' class="eraseIndex"><i class="icon-eraser"></i> {l s='Erase index' mod='amazzingfilter'}</a>
							<a href='#' class="toggle-cron pull-right"><i class="icon-clock-o"></i> {l s='Cron indexation' mod='amazzingfilter'}</a>
							<div class="cron-block">
								<div class="cron-row first">
									<h4 class="cron-title">{l s='Index missing products URL' mod='amazzingfilter'}</h4>
									<div class="u">{$this->getCronURL($id_shop, ['action' => 'index-missing'])|escape:'html':'UTF-8'}</div>
								</div>
								<div class="cron-row">
									<h4 class="cron-title">{l s='Index all products URL' mod='amazzingfilter'}</h4>
									<div class="u">{$this->getCronURL($id_shop, ['action' => 'index-all'])|escape:'html':'UTF-8'}</div>
								</div>
								<div class="cron-row">
									<h4 class="cron-title">{l s='Index selected products URL' mod='amazzingfilter'}</h4>
									<div class="u">{$this->getCronURL($id_shop, ['action' => 'index-selected', 'ids' => '1-2-3'])|escape:'html':'UTF-8'}</div>
									<div class="grey-note">
										{l s='You can replace [1]1-2-3[/1] with other IDs, separated by dashes.' mod='amazzingfilter' tags=['<span class="u">']}
										{l s='For example [1]1-15-32-18-37-120[/1]' mod='amazzingfilter' tags=['<span class="u">']}
									</div>
								</div>
								<div class="grey-note">
									{l s='Cron commands' mod='amazzingfilter'}:
									<span class="code">curl -L "indexation_url"</span>, or
									<span class="code">wget -O /dev/null -q "indexation_url"</span>
								</div>
								<a href="#" class="close-cron">&times;</a>
							</div>
						</div>
						<div class="progress">
							{$total = $data.missing + $data.indexed}
							{if $total}{$w = (100 - $data.missing/$total * 100)|round:0}{else}{$w = 100}{/if}
							<div class="progress-bar progress-bar-success indexation" role="progressbar" aria-valuenow="{$w|intval}"
							aria-valuemin="0" aria-valuemax="100" style="width:{$w|intval}%">
							</div>
						</div>
					</div>
				</div>
				{/foreach}
			</div>
			<div class="indexation-buttons">
				<div class="ajax-status">{l s='Indexation is in progress... Please, do not close this tab' mod='amazzingfilter'}</div>
				<button type="button" class="btn btn-default uppercase indexProducts missing">
					<span class="start"><i class="icon-play"></i> {l s='Index missing products' mod='amazzingfilter'}</span>
					<span class="stop"><i class="icon-refresh icon-spin"></i> {l s='Stop indexation' mod='amazzingfilter'}</span>
				</button>
				<button type="button" class="btn btn-default uppercase indexProducts all" data-all-identifier="{microtime(true)|escape:'html':'UTF-8'}">
					<span class="start"><i class="icon-play"></i> {l s='Reindex all products' mod='amazzingfilter'}</span>
					<span class="stop"><i class="icon-refresh icon-spin"></i> {l s='Stop indexation' mod='amazzingfilter'}</span>
				</button>
			</div>
			<div class="indexation-settings">
				<a href="#" class="toggleIndexationSettings">
					<span class="txt">{l s='Indexation settings' mod='amazzingfilter'}</span> <i class="icon-cog"></i>
				</a>
				<form method="post" action="" class="settings_form form-horizontal" data-type="indexation">
					<div class="clearfix">
						{foreach $settings.indexation as $name => $field}
							{include file="./form-group.tpl" label_class = 'ib' input_wrapper_class = 'ib'}
						{/foreach}
					</div>
					{renderElement type='saveMultipleSettingsBtn' cls='indexation'}
				</form>
			</div>
		</div>
		<div id="filter-templates" class="tab-pane{if !$indexation_required} active{/if}">
			{foreach $grouped_templates as $controller_type => $group_data}
				{if $controller_type == 'seopage'}{continue}{/if}
				{include file="./template-group.tpl"}
			{/foreach}
		</div>
		<div id="hook-settings" class="tab-pane">
			<h3>{l s='Hook settings' mod='amazzingfilter'}</h3>
			<div class="ajax-warning alert alert-warning hidden"></div>
			<label class="inline-block">{l s='Hook filter to' mod='amazzingfilter'}</label>
			{$current_hook_name = ''}
			<div class="inline-block">
				<select class="hookSelector">
					{foreach $available_hooks as $hook_name => $selected}
						<option value="{$hook_name|escape:'html':'UTF-8'}"{if $selected} selected {$current_hook_name = $hook_name}{/if}>{$hook_name|escape:'html':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			<div class="alert alert-info special-hook-note{if $current_hook_name != 'displayAmazzingFilter'} hidden{/if}">
				{l s='In order to display this hook, insert the following code in any tpl' mod='amazzingfilter'}:
				<b>{literal}{hook h='displayAmazzingFilter'}{/literal}</b>
			</div>
			<div class="dynamic-positions">
				{if $current_hook_name}{$this->renderHookPositionsForm($current_hook_name)} {* can not be escaped *}{/if}
			</div>
		</div>
		<div id="general-settings" class="tab-pane">
			<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="general">
				<h3>{l s='General appearance' mod='amazzingfilter'}</h3>
				<div class="clearfix">
					{foreach $settings.general as $name => $field}
						{include file="./form-group.tpl" group_class = 'settings-item' label_class = 'settings-label' input_wrapper_class = 'settings-input'}
					{/foreach}
				</div>
			</form>
			{renderElement type='saveMultipleSettingsBtn'}
		</div>
		<div id="selector-settings" class="tab-pane">
			<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="iconclass">
				<h3>{l s='Icon classes used in filter' mod='amazzingfilter'} {renderElement type='resetBtn'}</h3>
				<div class="clearfix">
					{foreach $settings.iconclass as $name => $field}
						{include file="./form-group.tpl" group_class = 'settings-item' label_class = 'settings-label' input_wrapper_class = 'settings-input'}
					{/foreach}
				</div>
			</form>
			<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="themeclass">
				<h3 class="in-the-middle">{l s='Theme classes' mod='amazzingfilter'} {renderElement type='resetBtn'}</h3>
				<div class="clearfix">
					{foreach $settings.themeclass as $name => $field}
						{include file="./form-group.tpl" group_class = 'settings-item' label_class = 'settings-label' input_wrapper_class = 'settings-input'}
					{/foreach}
				</div>
			</form>
			<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="themeid">
				<h3 class="in-the-middle">{l s='Theme ids' mod='amazzingfilter'} {renderElement type='resetBtn'}</h3>
				<div class="clearfix">
					{foreach $settings.themeid as $name => $field}
						{include file="./form-group.tpl" group_class = 'settings-item' label_class = 'settings-label' input_wrapper_class = 'settings-input'}
					{/foreach}
				</div>
			</form>
			{renderElement type='saveMultipleSettingsBtn'}
		</div>
		<div id="caching-settings" class="tab-pane">
			<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="caching">
				<h3>{l s='Activate caching for the following resources' mod='amazzingfilter'}:</h3>
				<div class="alert alert-info">
					{l s='Caching is used to optimize page loading time.' mod='amazzingfilter'}
					<a href="{$documentation_link|escape:'html':'UTF-8'}#page=5" target="_blank" class="u">
						{l s='More info' mod='amazzingfilter'} <i class="icon-external-link-sign"></i>
					</a>
					<button type="button" class="btn btn-secondary clearCache pull-right"><i class="icon-trash"></i> {l s='Clear cache' mod='amazzingfilter'}</button>
				</div>
				<div class="clearfix">
					{foreach $settings.caching as $name => $field}
						{include file="./form-group.tpl" group_class = 'form-group' label_class = 'control-label col-md-3' input_wrapper_class = 'col-md-3'}
					{/foreach}
				</div>
			</form>
			{renderElement type='saveMultipleSettingsBtn'}
		</div>
		{foreach $merged_data as $type => $data}
			<div id="merged-{$type|escape:'html':'UTF-8'}s" class="tab-pane">
				<div class="merged-params" data-type="{$type|escape:'html':'UTF-8'}">
					<h3>{$data.title|escape:'html':'UTF-8'}</h3>
					<div class="merged-header text-right">
						<label class="inline-block">{l s='Group' mod='amazzingfilter'}</label>
						<div class="inline-block">
							<select class="mergedGroup">{foreach $data.groups as $id => $name}
								<option value="{$id|intval}"{if $id == $data.selected_group} selected{/if}>{$name|escape:'html':'UTF-8'}</option>
							{/foreach}</select>
						</div>
						<button type="button" class="btn btn-primary addMergedRow">
							<i class="icon-plus-circle"></i> {l s='Add merged value' mod='amazzingfilter'}
						</button>
					</div>
					<div class="merged-list clear-both">{* filled dynamically *}</div>
				</div>
			</div>
		{/foreach}
		<div id="customer-filters" class="tab-pane">
			<h3>{l s='Customer filters' mod='amazzingfilter'}</h3>
			<form class="customer-filters">
				<div class="alert alert-info">
					{l s='Specify criteria, that can be used in customer filters' mod='amazzingfilter'}.<br>
					<i>{l s='In order to deactivate this feature, just make sure all criteria are unchecked' mod='amazzingfilter'}</i>
				</div>
				{foreach $available_customer_filters as $input_name => $f}
					<div class="col-lg-4">
						<label class="control-label">
							{$checked = in_array($input_name, $saved_customer_filters)}
							<input type="checkbox" name="customer_filters[]" value="{$input_name|escape:'html':'UTF-8'}"{if $checked} checked{/if}>
							<span class="prefix">{$f.prefix|escape:'html':'UTF-8'}:</span>
							{$f.name|escape:'html':'UTF-8'}
						</label>
					</div>
				{/foreach}
				<div class="clearfix"></div>
				<div class="panel-footer">
					<button type="button" class="saveAvailableCustomerFilters btn btn-default">
						<i class="process-icon-save"></i> {l s='Save' mod='amazzingfilter'}
					</button>
				</div>
			</form>
		</div>
		{if $overrides_data}
		<div id="overrides" class="tab-pane">
			<h3>{l s='Overrides' mod='amazzingfilter'}</h3>
			<div class="alert alert-info">
				{l s='In most cases overrides are added automatically on module installation' mod='amazzingfilter'}.
				{l s='They are used to improve filtering/indexation functionality' mod='amazzingfilter'}.<br>
				<span class="b">{l s='NOTE: These are advanced settings' mod='amazzingfilter'}.</span>
				{l s='Do not change anything here, if you are not sure what are you doing.' mod='amazzingfilter'}
			</div>
			<div class="overrides-list">
				{foreach $overrides_data as $class_name => $override}
					<div class="override-item{if $override.installed === true} installed{else if $override.installed === false} not-installed{/if} clearfix">
						<span class="override-name b">{$override.path|escape:'html':'UTF-8'}</span>
						{if $override.installed === true || $override.installed === false}
							<span class="override-status alert-success">{l s='Installed' mod='amazzingfilter'}</span>
							<span class="override-status alert-danger">{l s='Not installed' mod='amazzingfilter'}</span>
						{else}
							<span class="override-status alert-warning">{l s='The following methods are already overriden: %s' mod='amazzingfilter' sprintf=[$override.installed]}</span>
							<span class="grey-note pull-right install-manually">{l s='Should be added manually' mod='amazzingfilter'}</span>
						{/if}
						<button class="btn btn-default install-override pull-right" data-override="{$override.path|escape:'html':'UTF-8'}">
							{l s='Install' mod='amazzingfilter'}
						</button>
						<button class="btn btn-default uninstall-override pull-right" data-override="{$override.path|escape:'html':'UTF-8'}">
							{l s='Uninstall' mod='amazzingfilter'}
						</button>
						<div class="grey-note">
							{if $class_name == 'AdminProductsController'}
								{l s='Obligatory' mod='amazzingfilter'}.
							{else}
								{l s='Not obligatory' mod='amazzingfilter'}.
							{/if}
							{if $override.note}{$override.note|escape:'html':'UTF-8'}.{/if}
						</div>
					</div>
				{/foreach}
			</div>
		</div>
		{/if}
		<div id="info" class="tab-pane">
			<h3 class="panel-title"><span class="text">{l s='Information' mod='amazzingfilter'}</span></h3>
			<div class="info-row">Current version: <b>{$this->version|escape:'html':'UTF-8'}</b></div>
			<div class="info-row"><a href="{$changelog_link|escape:'html':'UTF-8'}" target="_blank"><i class="icon-code-fork"></i> Changelog</a></div>
			<div class="info-row"><a href="{$documentation_link|escape:'html':'UTF-8'}" target="_blank"><i class="icon-file-text"></i> Documentation</a></div>
			<div class="info-row"><a href="{$contact_us_link|escape:'html':'UTF-8'}" target="_blank"><i class="icon-envelope"></i> Contact us</a></div>
			<div class="info-row"><a href="{$other_modules_link|escape:'html':'UTF-8'}" target="_blank"><i class="icon-download"></i> Our modules</a></div>
		</div>
	</div>
</div>
<div class="alert alert-warning reindex-reminder orig hidden">
	{l s='Don\'t forget to re-index all products' mod='amazzingfilter'}
	<button type="button" class="close close-reminder">&times;</button>
</div>
<div class="modal fade" id="dynamic-popup" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"></h3>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="dynamic-content clearfix"></div>
		</div>
	</div>
</div>
{* since 3.1.4 *}
