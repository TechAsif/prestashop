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
<div class="row dhl_map" id="dhl_map" style="display:none;">
    <div class="col-md-12">
        <h4 class="page-heading dhl_map_title" id="dhl_map_title_ps">{l s='Choose a DHL Packstation' mod='dhldp'}</h4>
        <h4 class="page-heading dhl_map_title" id="dhl_map_title_pf">{l s='Choose a DHL Postfiliale' mod='dhldp'}</h4>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3">
        <div id="errors" class="form-group alert alert-danger"></div>
        <div class="form-group">
            <label for="dhl_map_zip">{l s='Zip' mod='dhldp'}<sup>*</sup></label>
            <input id="dhl_map_zip" type="text" class="form-control is_required" data-validate="isGenericName" name="dhl_map_zip">
        </div>
        <div class="form-group">
            <label for="dhl_map_city">{l s='City' mod='dhldp'}<sup>**</sup></label>
            <input id="dhl_map_city" type="text" class="form-control is_required" data-validate="isGenericName" name="dhl_map_city">
        </div>
        <div class="form-group">
            <label for="dhl_map_street">{l s='Street' mod='dhldp'}</label>
            <input id="dhl_map_street" type="text" class="form-control is_required" data-validate="isGenericName" name="dhl_map_street">
        </div>
        <div class="form-group">
            <label for="dhl_map_street_no">{l s='House number' mod='dhldp'}</label>
            <input id="dhl_map_street_no" type="text" class="form-control is_required" data-validate="isGenericName" name="dhl_map_street_no">
            <input type="hidden" class="form-control" name="dhl_map_type">
            <input type="hidden" class="form-control" name="dhl_map_number">
        </div>
        <button type="button" id="findOnMap" class="button btn btn-default button-small"><span>{l s='Search' mod='dhldp'} <i class="icon-search"></i></span></button>
    </div>
    <div class="col-xs-4 col-sm-4 col-md-4">
        <div class="dhl_list"></div>
    </div>
    <div class="map_wrapper col-xs-5 col-sm-5 col-md-5">
        <div id="map"></div>
    </div>
</div>