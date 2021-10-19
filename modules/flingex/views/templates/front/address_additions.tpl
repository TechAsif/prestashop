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
<div id="address_types" class="required form-group" style="display: none;">
    <input type="radio" style="margin-right: 5px;" id="address_type_RE" name="address_type" value="RE">
    <label style="margin-right: 10px;" for="address_type_RE">{l s='Regular address' mod='dhldp'}</label>
    <input type="radio" style="margin-right: 5px;" id="address_type_PS" name="address_type" value="PS">
    <label style="margin-right: 10px;" for="address_type_PS">{l s='DHL Packstation' mod='dhldp'}</label>
    <input type="radio" style="margin-right: 5px;" id="address_type_PF" name="address_type" value="PF">
    <label style="margin-right: 10px;" for="address_type_PF">{l s='DHL Postfiliale' mod='dhldp'}</label>
</div>
<div id="block_PS" class="required form-group" style="display: none;">
    <label>{l s='Packstation number' mod='dhldp'} <sup>*</sup></label>
    <button class="btn btn-default button-search" style="padding: 3px 6px;" type="button" id="searchPS" name="searchPS"><span>{l s='Search' mod='dhldp'} <i class="icon-search"></i></span></button>
    <input class="form-control is_required" type="text"  name="packstation_number"/>
    <label>{l s='Post number' mod='dhldp'} <sup>*</sup></label>
    <input class="form-control is_required" type="text" name="post_number_ps"/>
</div>
<div id="block_PF" class="required form-group" style="display: none;">
    <label>{l s='Postfiliale number' mod='dhldp'} <sup>*</sup></label>
    <button class="btn btn-default button-search" style="padding: 3px 6px;" type="button" id="searchPF" name="searchPF"><span>{l s='Search' mod='dhldp'} <i class="icon-search"></i></span></button>
    <input class="form-control is_required" type="text" name="postfiliale_number"/>
    <label>{l s='Post number' mod='dhldp'} <sup>*</sup></label>
    <input class="form-control is_required" type="text" name="post_number_pf"/>
</div>
{include file="$self/views/templates/front/map.tpl"}