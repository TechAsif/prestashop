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
<div class="row" id="dhldp_dhl_addit_services" style="{if !isset($addit_services.show_dhl_additional_services) || (isset($addit_services.show_dhl_additional_services) && $addit_services.show_dhl_additional_services != 1)}display: none;{/if}">
    <div class="panel">
        <div class="panel-heading">{l s='Additional services' mod='paperfly'}</div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="row form-wrapper form-horizontal">
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Day of delivery' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control datepicker" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][DayOfDelivery]"
                                   value="{$addit_services.DayOfDelivery|escape:'htmlall':'UTF-8'}" maxlength="10"/>
                                <span class="help-block">{l s='yyyy-mm-dd' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Delivery timeframe' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <select name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][DeliveryTimeframe]"  class="form-control">
                                    <option value="">----</option>
                                    {foreach from=$addit_services.deliverytimeframe_options key=option_key item=option_value}
                                        <option value="{$option_key|escape:'html':'UTF-8'}"{if $addit_services.DeliveryTimeframe == $option_key} selected="selected"{/if}>{$option_value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Return immediately' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ReturnImmediately]"
                                        {if $addit_services.ReturnImmediately == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='Service of immediatly shipment return in case of non successful delivery for product' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Individual sender requirements for product' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IndividualSenderRequirement]" maxlength="250">{$addit_services.IndividualSenderRequirement|escape:'htmlall':'UTF-8'}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Service for package return' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][PackagingReturn]"
                                   {if $addit_services.PackagingReturn == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='After the delivery of DHL Parcel same day, we provide the return of your reusable parcel at your return address. Individual extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Notice Of Non Deliverability' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][NoticeOfNonDeliverability]"
                                    {if $addit_services.NoticeOfNonDeliverability == 1} checked="checked"{/if} value="1"/>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Shipment handling for product' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <select name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ShipmentHandling]"  class="fixed-width-xl form-control">
                                    <option value="">----</option>
                                    {foreach from=$addit_services.shipmenthandling_options key=option_key item=option_value}
                                        <option value="{$option_key|escape:'html':'UTF-8'}"{if $addit_services.ShipmentHandling == $option_key} selected="selected"{/if}>{$option_value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Endorsement' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <select name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Endorsement]"  class="form-control">
                                    <option value="">----</option>
                                    {foreach from=$addit_services.endorsement_options key=option_key item=option_value}
                                        <option value="{$option_key|escape:'html':'UTF-8'}"{if $addit_services.Endorsement == $option_key} selected="selected"{/if}>{$option_value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Visual age check' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <select name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][VisualCheckOfAge]"  class="form-control">
                                    <option value="">----</option>
                                    {foreach from=$addit_services.visualcheckofage_options key=option_key item=option_value}
                                        <option value="{$option_key|escape:'html':'UTF-8'}"{if $addit_services.VisualCheckOfAge == $option_key} selected="selected"{/if}>{$option_value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <span class="help-block">{l s='With the service visual check of age you can order in an uncomplicated and convenient way that your items are not handed over to children or minors. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Notification' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                {*{if (is_array($addit_services.permission_confirmation) && $addit_services.permission_confirmation['permission_tpd'] == 0)}
                                    <div class="alert-warning">
                                        {l s='Permission for transferring e-mail address and phone number has NOT been granted by customer' mod='paperfly'} ({$addit_services.permission_confirmation['date_add']|escape:'html':'UTF-8'})
                                    </div>
                                {/if}*}
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification]"
                                        {if $addit_services.Notification == 1} checked="checked"{/if} value="1"/ {*{if (is_array($addit_services.permission_confirmation) && $addit_services.permission_confirmation['permission_tpd'] == 0)}disabled="disabled"{/if}*}>
                                <span class="help-block">{l s='Mechanism to send notifications by email after successful manifesting of shipment. Email address is email address of delivery address.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Notification: recepient Email Address' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification_recepientEmailAddress]"
                                       value="{$addit_services.Notification_recepientEmailAddress|escape:'htmlall':'UTF-8'}" maxlength="10"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Cash On Delivery' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery]"
                                        {if $addit_services.CashOnDelivery == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='Safety especially in regard to customers who placed their first order and discretion of payment handling - transaction will not show up in bank account statement of the recipient - enabled by reliable payment during parcel handing over. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Cash On Delivery: add fee' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_addFee]"
                                        {if $addit_services.CashOnDelivery_addFee == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Cash On Delivery: amount' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <div class="input-group fixed-width-x2">
                                    <input class="form-control" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_codAmount]"
                                           value="{$addit_services.CashOnDelivery_codAmount|escape:'htmlall':'UTF-8'}" maxlength="10"/>
                                    <div class="input-group-addon">{l s='euro' mod='paperfly'} </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Additional Insurance' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance]"
                                        {if $addit_services.AdditionalInsurance == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='The service provides that your parcels are protected, even if they exceed the liability limit of  EUR 500. The additional insurance service offers you sufficient protection up to EUR 2,500 (additional insurance A) or EUR 25,000 (additional insurance B). Therefore you can avoid financial loss or damage. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Additional Insurance: amount' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <div class="input-group fixed-width-x2">
                                    <input class="form-control" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance_insuranceAmount]"
                                           value="{$addit_services.AdditionalInsurance_insuranceAmount|escape:'htmlall':'UTF-8'}" maxlength="10"/>
                                    <div class="input-group-addon">{l s='euro' mod='paperfly'} </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Bulky Goods' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][BulkyGoods]"
                                        {if $addit_services.BulkyGoods == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Ident check' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck]"
                                        {if $addit_services.IdentCheck == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='With the service Ident-Check you order that your parcels are only delivered to the recipient in person after the identity and - where applicable - the age have been checked against the identity card. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Ident check: surname' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_surname]"
                                          maxlength="255">{$addit_services.IdentCheck_Ident_surname|escape:'htmlall':'UTF-8'}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Ident check: first name' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_givenName]"
                                          maxlength="255">{$addit_services.IdentCheck_Ident_givenName|escape:'htmlall':'UTF-8'}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Ident check: date of birth' mod='paperfly'}</label>
                            <input class="form-control datepicker" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_dateOfBirth]"
                                   value="{$addit_services.IdentCheck_Ident_dateOfBirth|escape:'htmlall':'UTF-8'}" maxlength="10"/>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Ident check: minimum age' mod='paperfly'}</label>
                            <input class="form-control" type="text" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_minimumAge]"
                                   value="{$addit_services.IdentCheck_Ident_minimumAge|escape:'htmlall':'UTF-8'}" maxlength="3"/>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='GoGreen' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][GoGreen]"
                                        {if $addit_services.GoGreen == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='You will make a sustainable contribution towards climate protection by offsetting the CO2 e-emissions generated during the transportation of your items. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Perishables' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Perishables]"
                                        {if $addit_services.Perishables == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                    </div>
					<div class="form-group">
						<label class="control-label col-lg-6">{l s='Parcel outlet routing' mod='paperfly'}</label>
						<div class="col-lg-6">
							<input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting]"
									{if $addit_services.ParcelOutletRouting == 1} checked="checked"{/if} value="1"/>
							<span class="help-block">{l s='Your undeliverable item (recipient unknown
or acceptance refused) gets a second chance to reach the recipient. Rather than being
returned immediately to you, the undeliverable item will be held at the nearest retail
outlet that has a parcel collection point for collection by the recipient. Your recipient
will be informed of this by e-mail. If the item is collected, the time and costs involved in
returning it can be avoided' mod='paperfly'}</span>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-6">{l s='Parcel outlet routing: details' mod='paperfly'}</label>
						<div class="col-lg-6">
							<textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting_details]"
									  maxlength="100">{$addit_services.ParcelOutletRouting_details|escape:'htmlall':'UTF-8'}</textarea>
							<span class="help-block"></span>
							<span class="help-block">{l s='Details can be an email-address, if not set receiver email will be used' mod='paperfly'}</span>
						</div>
					</div>
                </div>
                <div class="col-lg-6">
                    <div class="row form-wrapper form-horizontal">
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Personally' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Personally]"
                                        {if $addit_services.Personally == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='No Neighbour Delivery' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][NoNeighbourDelivery]"
                                        {if $addit_services.NoNeighbourDelivery == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='With the service no neighbour delivery you can exclude an alternative delivery to the neighbour in case the recipient is not available during delivery. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Named Person Only' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][NamedPersonOnly]"
                                    {if $addit_services.NamedPersonOnly == 1} checked="checked"{/if} value="1"/>
                                <span class="help-block">{l s='With the service named person only you order that your parcels are only delivered to the recipient in person or to an authorized person. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Return receipt' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ReturnReceipt]"
                                    {if $addit_services.ReturnReceipt == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Premium' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <input class="form-control" type="checkbox" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Premium]"
                                    {if $addit_services.Premium == 1} checked="checked"{/if} value="1"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Preferred location' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][PreferredLocation]"
                                          maxlength="100">{$addit_services.PreferredLocation|escape:'htmlall':'UTF-8'}</textarea>
                                <span class="help-block"></span>
                                <span class="help-block">{l s='This addressing version enables your customers to receive parcels round the clock, even if nobody sits at home. No extra charge.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Preferred neighbour' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][PreferredNeighbour]"
                                          maxlength="100">{$addit_services.PreferredNeighbour|escape:'htmlall':'UTF-8'}</textarea>
                                <span class="help-block"></span>
                                <span class="help-block">{l s=' If the customer is not at home on the day of delivery, the parcels are left with the preferred neighbour. No extra charge.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Preferred day' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <textarea class="form-control" style="width: 100%;word-wrap: break-word; resize: none; height: 85px;" name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][PreferredDay]"
                                          maxlength="100">{$addit_services.PreferredDay|escape:'htmlall':'UTF-8'}</textarea>
                                <span class="help-block"></span>
                                <span class="help-block">{l s='Your parcel is delivered on the desired day that you specified in advance. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-6">{l s='Preferred time' mod='paperfly'}</label>
                            <div class="col-lg-6">
                                <select name="addit_services[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][PreferredTime]"  class="form-control">
                                    <option value="">----</option>
                                    {foreach from=$addit_services.preferredtime_options key=option_key item=option_value}
                                        <option value="{$option_key|escape:'html':'UTF-8'}"{if $addit_services.PreferredTime == $option_key} selected="selected"{/if}>{$option_value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <span class="help-block">{l s='With the service preferred time your customers can select one of the two-hour time frames for the delivery of your items – nationwide in Germany. Extra charge in addition to the price of a parcel.' mod='paperfly'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <input type="hidden" id="show_dhl_additional_services" name="addit_services[{$addit_services.id_order_carrier|escape:'htmlall':'UTF-8'}][show_dhl_additional_services]" value="{$addit_services.show_dhl_additional_services|escape:'htmlall':'UTF-8'}">
    </div>
</div>
<script language="javascript">
    $(function() {
        $(".datepicker").datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });

        $("textarea").maxlength(
            {
                text: '%length / %maxlength'
            }
        );

        if ($("select[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportType]']").val() == 'OTHER') {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportTypeDescription]']").closest('div.form-group').show()
        } else {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportTypeDescription]']").closest('div.form-group').hide()
        }

        if ($("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery]']:checked").length) {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_addFee]']").closest('div.form-group').show();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_codAmount]']").closest('div.form-group').show();
        } else {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_addFee]']").closest('div.form-group').hide();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_codAmount]']").closest('div.form-group').hide();
        }

        if ($("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification]']:checked").length) {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification_recepientEmailAddress]']").closest('div.form-group').show();
        } else {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification_recepientEmailAddress]']").closest('div.form-group').hide();
        }

        if ($("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance]']:checked").length) {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance_insuranceAmount]']").closest('div.form-group').show();
        } else {
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance_insuranceAmount]']").closest('div.form-group').hide();
        }
		
		if ($("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting]']:checked").length) {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting_details]']").closest('div.form-group').show();
        } else {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting_details]']").closest('div.form-group').hide();
        }

        if ($("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck]']:checked").length) {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_surname]']").closest('div.form-group').show();
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_givenName]']").closest('div.form-group').show();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_dateOfBirth]']").closest('div.form-group').show();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_minimumAge]']").closest('div.form-group').show();
        } else {
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_surname]']").closest('div.form-group').hide();
            $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_givenName]']").closest('div.form-group').hide();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_dateOfBirth]']").closest('div.form-group').hide();
            $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_minimumAge]']").closest('div.form-group').hide();
        }


        $(document).on('click', "input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck]']", function() {
            if ($(this).is(':checked')) {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_surname]']").closest('div.form-group').show();
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_givenName]']").closest('div.form-group').show();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_dateOfBirth]']").closest('div.form-group').show();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_minimumAge]']").closest('div.form-group').show();
            } else {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_surname]']").closest('div.form-group').hide();
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_givenName]']").closest('div.form-group').hide();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_dateOfBirth]']").closest('div.form-group').hide();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][IdentCheck_Ident_minimumAge]']").closest('div.form-group').hide();
            }
        });

        $(document).on('click', "input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance]']", function() {
            if ($(this).is(':checked')) {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance_insuranceAmount]']").closest('div.form-group').show();
            } else {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][AdditionalInsurance_insuranceAmount]']").closest('div.form-group').hide();
            }
        });
		
		$(document).on('click', "input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting]']", function() {
            if ($(this).is(':checked')) {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting_details]']").closest('div.form-group').show();
            } else {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][ParcelOutletRouting_details]']").closest('div.form-group').hide();
            }
        });

        $(document).on('click', "input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery]']", function() {
            if ($(this).is(':checked')) {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_addFee]']").closest('div.form-group').show();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_codAmount]']").closest('div.form-group').show();
            } else {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_addFee]']").closest('div.form-group').hide();
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][CashOnDelivery_codAmount]']").closest('div.form-group').hide();
            }
        });

        $(document).on('click', "input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification]']", function() {
            if ($(this).is(':checked')) {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification_recepientEmailAddress]']").closest('div.form-group').show();
            } else {
                $("input[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][Notification_recepientEmailAddress]']").closest('div.form-group').hide();
            }
        });

        $(document).on('change', "select[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportType]']", function() {
            if ($("select[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportType]']").val() == 'OTHER') {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportTypeDescription]']").closest('div.form-group').show()
            } else {
                $("textarea[name*='[{$addit_services.id_order_carrier|escape:'html':'UTF-8'}][exportTypeDescription]']").closest('div.form-group').hide()
            }
        })
    });
</script>