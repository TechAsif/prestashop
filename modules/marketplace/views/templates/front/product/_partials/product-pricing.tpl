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

<hr>
<div {if isset($backendController)}class="col-lg-6 col-lg-offset-2" {/if}>
  <div class="row">
    <h4 class="col-md-12">{l s='Pricing' mod='marketplace'}</h4>
  </div>
  {hook h='displayMpProductPriceTop'}
  <div class="form-group row">
    <div class="col-md-6">
      <label for="price" class="control-label required">
        {l s='Price (tax excl.)' mod='marketplace'}
        <div class="wk_tooltip">
          <span
            class="wk_tooltiptext">{l s='This is the retail price at which you intend to sell this product to your customers.' mod='marketplace'}</span>
        </div>
      </label>
      <div class="input-group">
        <input type="text" id="price" name="price"
          value="{if isset($smarty.post.price)}{$smarty.post.price}{else if isset($product_info)}{$product_info.price}{else}0.000000{/if}"
          class="form-control" data-action="input_excl" pattern="\d+(\.\d+)?" autocomplete="off"
          placeholder="{l s='Enter Product Base Price' mod='marketplace'}" />
        <span class="input-group-addon">{$defaultCurrencySign}</span>
      </div>
      {if isset($admin_commission)}
        <span id="wk_display_admin_commission" class="form-control-comment">
          {l s='Admin commission will be %s of base price you entered.' sprintf=[$admin_commission] mod='marketplace'}
        </span>
      {/if}
    </div>
    <!-- Product Tax Rule  -->
    {if isset($mp_seller_applied_tax_rule) && $mp_seller_applied_tax_rule && isset($tax_rules_groups)}
      <div class="col-md-6">
        <label for="id_tax_rules_group" class="control-label">
          {l s='Tax Rule' mod='marketplace'}
        </label>
        <div class="row">
          <div class="col-md-12">
            <select name="id_tax_rules_group" id="id_tax_rules_group" class="form-control form-control-select"
              data-action="input_excl">
              <option value="0">{l s='No tax' mod='marketplace'}</option>
              {foreach $tax_rules_groups as $tax_rule}
                <option value="{$tax_rule.id_tax_rules_group|escape:'html':'UTF-8'}"
                  {if isset($id_tax_rules_group)}
                    {if $id_tax_rules_group == $tax_rule.id_tax_rules_group}
                      selected="selected" {/if}{else}{if $defaultTaxRuleGroup == $tax_rule.id_tax_rules_group}
                    selected="selected" {/if}
                  {/if}>
                  {$tax_rule.name|escape:'html':'UTF-8'}
                </option>
              {/foreach}
            </select>
          </div>
        </div>
      </div>
    {/if}
  </div>
  {if isset($mp_seller_applied_tax_rule) && $mp_seller_applied_tax_rule && isset($tax_rules_groups)}
    <div class="alert alert-info">
      {l s='Product Price (tax incl.) will be calculated on the basis of customer address.' mod='marketplace'}</div>
  {/if}
  <div class="form-group row">
    {if Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE') || isset($backendController)}
      <div class="col-md-6 ">
        <label for="wholesale_price" class="control-label">
          {l s='Wholesale Price' mod='marketplace'}
          <div class="wk_tooltip">
            <span
              class="wk_tooltiptext">{l s='The cost price is the price you paid for the product. Do not include the tax. It should be lower than the retail price: the difference between the two will be your margin.' mod='marketplace'}</span>
          </div>
        </label>
        <div class="input-group">
          <input type="text" id="wholesale_price" name="wholesale_price"
            value="{if isset($smarty.post.wholesale_price)}{$smarty.post.wholesale_price}{else if isset($product_info)}{$product_info.wholesale_price}{else}0.000000{/if}"
            class="form-control" pattern="\d+(\.\d+)?"
            placeholder="{l s='Enter Product Wholesale Price' mod='marketplace'}" />
          <span class="input-group-addon">{$defaultCurrencySign}</span>
        </div>
      </div>
    {/if}
    {if Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT') || isset($backendController)}
      <div class="col-md-6">
        <label for="unit_price" class="control-label">
          {l s='Price per unit (tax excl.) ' mod='marketplace'}
          <div class="wk_tooltip">
            <span
              class="wk_tooltiptext">{l s='Some products can be purchased by unit (per bottle, per pound, etc.), and this is the price for one unit. For instance, if you’re selling fabrics, it would be the price per meter.' mod='marketplace'}</span>
          </div>
        </label>
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <input type="text" id="unit_price" name="unit_price"
                value="{if isset($smarty.post.unit_price)}{$smarty.post.unit_price}{else if isset($product_info)}{$product_info.unit_price}{else}0.000000{/if}"
                class="form-control" pattern="\d+(\.\d+)?" />
              <span class="input-group-addon">{$defaultCurrencySign}</span>
            </div>
          </div>
          <div class="col-md-6">
            <input type="text" id="unity" name="unity"
              value="{if isset($smarty.post.unity)}{$smarty.post.unity}{else if isset($product_info)}{$product_info.unity}{/if}"
              class="form-control" placeholder="{l s='Per kilo, per litre' mod='marketplace'}" />
          </div>
        </div>
      </div>
    {/if}
  </div>

  <div class="form-group row mb-3">
    <div class="col-md-12">
      <h2>Specific prices
        <span class="help-box" data-toggle="popover"
          data-content="You can set specific prices for customers belonging to different groups, different countries, etc."></span>
      </h2>
    </div>
    <div class="col-md-12">
      <div id="specific-price" class="mb-2">
        <a class="btn btn-outline-primary mb-3" data-toggle="collapse" href="#specific_price_form"
          aria-expanded="false">
          <i class="material-icons">add_circle</i>Add a specific price
        </a>
        <table id="js-specific-price-list" class="table hide seo-table"
          data="/ps174/admin084oazcuj/index.php/specific-price/list/1?_token=TPMW9jc1YO_o8OughS85zTVmciw_-Gog550S_c5mpUU"
          data-action-delete="/ps174/admin084oazcuj/index.php/specific-price/delete/1?_token=TPMW9jc1YO_o8OughS85zTVmciw_-Gog550S_c5mpUU">
          <thead class="thead-default">
            <tr>
              <th>Rule</th>
              <th>Combination</th>
              <th>Currency</th>
              <th>Country</th>
              <th>Group</th>
              <th>Customer</th>
              <th>Fixed price</th>
              <th>Impact</th>
              <th>Period</th>
              <th>From</th>
              <th></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="collapse" id="specific_price_form"
        data-action="/ps174/admin084oazcuj/index.php/specific-price/add?_token=TPMW9jc1YO_o8OughS85zTVmciw_-Gog550S_c5mpUU">
        <div class="card card-block">
          <h4><b>Specific price conditions</b></h4>
          <input type="hidden" id="form_step2_specific_price_sp_id_shop" name="form[step2][specific_price][sp_id_shop]"
            class="form-control" value="1" />
          <div class="row">
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>For</label>
                <select id="form_step2_specific_price_sp_id_currency" name="form[step2][specific_price][sp_id_currency]"
                  data-toggle="select2" data-minimumResultsForSearch="7" class="custom-select">
                  <option value="">All currencies</option>
                  {foreach from=$currencies item=currencie}
                    <option value="{$currencie.id_currency}">{$currencie.name|escape:'html':'UTF-8'}</option>
                  {/foreach}
                </select>
              </fieldset>
            </div>
            <div class="col-md-3">
              <fieldset class="form-group">
                <select id="form_step2_specific_price_sp_id_country" name="form[step2][specific_price][sp_id_country]"
                  data-toggle="select2" data-minimumResultsForSearch="7" class="custom-select">
                  <option value="">All countries</option>
                  {foreach from=$countries item=country}
                    <option value="{$country.id_country}">{$country.name|escape:'html':'UTF-8'}</option>
                  {/foreach}
                </select>
              </fieldset>
            </div>
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>&nbsp;</label>
                <select id="form_step2_specific_price_sp_id_group" name="form[step2][specific_price][sp_id_group]"
                  data-toggle="select2" data-minimumResultsForSearch="7" class="custom-select">
                  <option value="">All groups</option>
                  {foreach from=$groupes item=group}
                    <option value="{$group.id_group}">{$group.name|escape:'html':'UTF-8'}</option>
                  {/foreach}
                </select>
              </fieldset>
            </div>
            <div class="col-md-6">
              <fieldset class="form-group">
                <label>Customer</label>
                <input type="text" id="form_step2_specific_price_sp_id_customer"
                  class="form-control typeahead form_step2_specific_price_sp_id_customer" placeholder="All customers"
                  autocomplete="off" />
                <ul id="form_step2_specific_price_sp_id_customer-data"
                  class="typeahead-list product-list nostyle col-sm-12"></ul>
              </fieldset>
            </div>
          </div>
          <div class="row">
            <div id="specific-price-combination-selector" class="col-md-6 hide">
              <fieldset class="form-group">
                <label>Combinations</label>
                <select id="form_step2_specific_price_sp_id_product_attribute"
                  name="form[step2][specific_price][sp_id_product_attribute]"
                  data-action="/ps174/admin084oazcuj/index.php/combination/product-combinations/1?_token=TPMW9jc1YO_o8OughS85zTVmciw_-Gog550S_c5mpUU"
                  class="custom-select">
                  <option value="">Apply to all combinations</option>
                </select>
              </fieldset>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>Available from</label>
                <div class="input-group datepicker">
                  <input type="text" class="form-control" id="form_step2_specific_price_sp_from"
                    name="form[step2][specific_price][sp_from]" placeholder="YYYY-MM-DD" class="datepicker" />
                  <div class="input-group-append">
                    <div class="input-group-text"><i class="material-icons">date_range</i></div>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>to</label>
                <div class="input-group datepicker">
                  <input type="text" class="form-control" id="form_step2_specific_price_sp_to"
                    name="form[step2][specific_price][sp_to]" placeholder="YYYY-MM-DD" class="datepicker" />
                  <div class="input-group-append">
                    <div class="input-group-text"><i class="material-icons">date_range</i></div>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="col-md-2">
              <fieldset class="form-group">
                <label>Starting at</label>
                <div class="input-group">
                  <input type="text" id="form_step2_specific_price_sp_from_quantity"
                    name="form[step2][specific_price][sp_from_quantity]" class="form-control" value="1" />
                  <div class="input-group-append">
                    <span class="input-group-text">Unit(s)</span>
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
          <br>
          <h4><b>Impact on price</b></h4>
          <div class="row">
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>Product price (tax excl.)</label>
                <div class="input-group money-type">
                  <div class="input-group-prepend">
                    <span class="input-group-text">BDT </span>
                  </div>
                  <input type="text" id="form_step2_specific_price_sp_price"
                    name="form[step2][specific_price][sp_price]" disabled="disabled" class="price form-control" />
                </div>
              </fieldset>
            </div>
            <div class="col-md-3">
              <fieldset class="form-group">
                <label>&nbsp;</label>
                <div class="checkbox"> <label><input type="checkbox" id="form_step2_specific_price_leave_bprice"
                      name="form[step2][specific_price][leave_bprice]" value="1" checked="checked" />
                    Leave initial price</label>
                </div>
              </fieldset>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-2 col-lg-3">
              <fieldset class="form-group">
                <label>Apply a discount of</label>
                <div class="input-group money-type">
                  <div class="input-group-prepend">
                    <span class="input-group-text">BDT </span>
                  </div>
                  <input type="text" id="form_step2_specific_price_sp_reduction"
                    name="form[step2][specific_price][sp_reduction]" class="form-control" value="0.000000" />
                </div>
              </fieldset>
            </div>
            <div class="col-xl-2 col-lg-3">
              <fieldset class="form-group">
                <label>&nbsp;</label>
                <select id="form_step2_specific_price_sp_reduction_type"
                  name="form[step2][specific_price][sp_reduction_type]" class="custom-select">
                  <option value="amount">€</option>
                  <option value="percentage">%</option>
                </select>
              </fieldset>
            </div>
            <div class="col-xl-2 col-lg-3">
              <fieldset class="form-group">
                <label>&nbsp;</label>
                <select id="form_step2_specific_price_sp_reduction_tax"
                  name="form[step2][specific_price][sp_reduction_tax]" class="custom-select">
                  <option value="0">Tax excluded</option>
                  <option value="1" selected="selected">Tax included</option>
                </select>
              </fieldset>
            </div>
          </div>
          <div class="col-md-12 text-sm-right">
            <button type="button" id="form_step2_specific_price_cancel" name="form[step2][specific_price][cancel]"
              class="btn-outline-secondary js-cancel btn">Cancel</button>
            <button type="button" id="form_step2_specific_price_save" name="form[step2][specific_price][save]"
              class="btn-outline-primary js-save btn">Apply</button>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>

  {* On Sale flag on product *}
  {if Configuration::get('WK_MP_PRODUCT_ON_SALE') || isset($backendController)}
    <div class="form-group">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="on_sale" id="on_sale" value="1"
            {if isset($product_info) && $product_info.on_sale == '1'}Checked="checked" {/if}>
          <span>
            {l s='Display the "On sale!" flag on the product page, and on product listings.' mod='marketplace'}
          </span>
        </label>
      </div>
    </div>
  {/if}
  {hook h='displayMpProductPriceBottom'}
</div>
{if isset($backendController)}<div class="clearfix"></div>{/if}