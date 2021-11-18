<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class MarketplaceSpacificPriceModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        header('Content-Type: application/json');

        $action = Tools::getValue('action');
        $product_id = Tools::getValue('product_id');

       


        $spIdShop = $_POST["form"]["step2"]["specific_price"]["sp_id_shop"];
        $spIdCurrency = $_POST["form"]["step2"]["specific_price"]["sp_id_currency"];
        $spIdCountry = $_POST["form"]["step2"]["specific_price"]["sp_id_country"];
        $spIdGroup = $_POST["form"]["step2"]["specific_price"]["sp_id_group"];
        $spIdProductAttribute = $_POST["form"]["step2"]["specific_price"]["sp_id_product_attribute"];
        $spFormDate = $_POST["form"]["step2"]["specific_price"]["sp_from"];
        $spIdToDate = $_POST["form"]["step2"]["specific_price"]["sp_to"];
        $spFromQuentity = $_POST["form"]["step2"]["specific_price"]["sp_from_quantity"];
        $spLeaveBPrice = $_POST["form"]["step2"]["specific_price"]["leave_bprice"];
        $spReduction = $_POST["form"]["step2"]["specific_price"]["sp_reduction"];
        $spReductionType = $_POST["form"]["step2"]["specific_price"]["sp_reduction_type"];
        $spReductionTax = $_POST["form"]["step2"]["specific_price"]["sp_reduction_tax"];
        $spIdProduct = $_POST["form"]["id_product"];



        /* return Db::getInstance()->execute('
		INSERT INTO `'._DB_PREFIX_.'specific_price` (`id_product`, `priority`)
		VALUES ('.(int)$spIdProduct.',\''.pSQL(rtrim($value, ';')).'\')
		ON DUPLICATE KEY UPDATE `priority` = \''.pSQL(rtrim($value, ';')).'\'
		'); */

        $quary = 'INSERT INTO '._DB_PREFIX_.'specific_price (id_product, id_shop, id_currency, id_country, id_group, id_product_attribute, price, from_quantity, reduction, reduction_tax, reduction_type, `from`, `to`,id_specific_price_rule, id_cart,id_shop_group,id_customer) VALUES('.$spIdProduct.', '.$spIdShop.','.$spIdCurrency.', '.$spIdCountry.', '.$spIdGroup.', '.$spIdProductAttribute.', '.$spLeaveBPrice.', '.$spFromQuentity.','.$spReduction.', '.$spReductionTax.', "'.$spReductionType.'","'.$spFormDate.'", "'.$spIdToDate.'",0,0,0,0)';
           
         return Db::getInstance()->execute($quary);
        
        /* $res = Db::getInstance()->execute('
        INSERT INTO '._DB_PREFIX_.'specific_price
            (id_attachment, id_product) VALUES
            ('.(int) $this->id.', '.(int) $idProduct.')'); */


        
        if( $action == 'list')
            $this->showList($product_id);
        if( $action == 'add')
            $this->addSpacificPrice($product_id);

        die();
        
    }
    
    // http://localhost/ps174/en/module/marketplace/spacificprice?ajax=1&action=list&product_id=21
    public function showList($product_id)
    {
        
        $specific_price_list_sql = "SELECT
            sp.id_specific_price,
            sp.id_product,
            spr.name AS rule_name,
            sop.name AS shop,
            cur.name AS currency,
            ctl.name AS country,
            grl.name AS `group`,
            sp.id_specific_price_rule,
            sp.price as fixed_price,
	        cur.iso_code AS currency_code,
            sp.id_cart,
            sp.id_shop,
            sp.id_shop_group,
            sp.id_currency,
            sp.id_country,
            sp.id_group,
            sp.id_customer,
            sp.id_product_attribute,
            sp.from_quantity,
            sp.reduction,
            sp.reduction_tax,
            sp.reduction_type,
            sp.from,
            sp.to
        FROM
            "._DB_PREFIX_."specific_price sp
        LEFT JOIN "._DB_PREFIX_."specific_price_rule spr ON
            (sp.id_specific_price_rule = spr.id_specific_price_rule)
        LEFT JOIN "._DB_PREFIX_."shop sop ON
            (sp.id_shop = sop.id_shop)
        LEFT JOIN "._DB_PREFIX_."currency cur ON
            (sp.id_currency = cur.id_currency)
        LEFT JOIN "._DB_PREFIX_."country_lang ctl ON
            (sp.id_country = ctl.id_country AND ctl.id_lang = 1)
        LEFT JOIN "._DB_PREFIX_."group_lang grl ON
            (sp.id_group = grl.id_group AND grl.id_lang = 1)
        LEFT JOIN "._DB_PREFIX_."customer cus ON
            (sp.id_customer = cus.id_customer)
        WHERE sp.id_product = ".$product_id."
        GROUP BY sp.id_specific_price";

        $specific_price_list = Db::getInstance()->executeS($specific_price_list_sql);

        foreach ($specific_price_list as $key=>$specific_price){
            $specific_price_list[$key]['impact'] = '-'.$specific_price['currency_code'].$specific_price['reduction'].'('.($specific_price['reduction_tax'] == 1 ? 'Tax incl.': 'Tax excl.' ).')';
            $specific_price_list[$key]['period'] = 'From '.$specific_price['from'].'<br />to '.$specific_price['to'];
            $specific_price_list[$key]['can_delete'] = 'true';            
        };

        die(json_encode($specific_price_list));
    }

    // http://localhost/ps174/en/module/marketplace/spacificprice?ajax=1&action=add&product_id=21
    public function addSpacificPrice($product_id)
    {
        
    }

}
