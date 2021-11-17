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
use PrestaShopBundle\Controller\Admin\SpecificPriceController;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class MarketplaceSpacificPriceModuleFrontController extends ModuleFrontController
{
    public $customContainer;
    public function __construct()
    {
        parent::__construct();
    }
    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        // {
        //     "id_specific_price": "5",
        //     "id_product": 21,
        //     "rule_name": "--",
        //     "attributes_name": "All combinations",
        //     "shop": "Prestashop 1.7.4",
        //     "currency": "Bangladeshi Taka",
        //     "country": "Bangladesh",
        //     "group": "All groups",
        //     "customer": "All customers",
        //     "fixed_price": "--",
        //     "impact": "- BDT10.00 (Tax incl.)",
        //     "period": "From 2021-11-11 00:00:00<br />to 2022-03-12 00:00:00",
        //     "from_quantity": "1",
        //     "can_delete": true
        //   }

        // `id_specific_price`, //  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        // `id_product`, //  INT(10) UNSIGNED NOT NULL,

        // `id_specific_price_rule`, //  INT(10) UNSIGNED NOT NULL,
        // `id_cart`, //  INT(10) UNSIGNED NOT NULL,
        // `id_shop`, //  INT(10) UNSIGNED NOT NULL DEFAULT 1,
        // `id_shop_group`, //  INT(10) UNSIGNED NOT NULL,
        // `id_currency`, //  INT(10) UNSIGNED NOT NULL,
        // `id_country`, //  INT(10) UNSIGNED NOT NULL,
        // `id_group`, //  INT(10) UNSIGNED NOT NULL,
        // `id_customer`, //  INT(10) UNSIGNED NOT NULL,
        // `id_product_attribute`, //  INT(10) UNSIGNED NOT NULL,
        // `price`, //  DECIMAL(20,6) NOT NULL,
        // `from_quantity`, //  MEDIUMINT(7) UNSIGNED NOT NULL,
        // `reduction`, //  DECIMAL(20,6) NOT NULL,
        // `reduction_tax`, //  TINYINT(1) NOT NULL DEFAULT 1,
        // `reduction_type`, //  ENUM(amount,percentage) NOT NULL,
        // `from`, //  DATETIME NOT NULL,
        // `to`, //  DATETIME NOT NULL,
        // header('Content-Type: application/json');


        
        $order_sql = "SELECT 
        `id_specific_price`,
        `id_product`,
        pot.name as rule_name,
        `id_specific_price_rule`,
        `id_cart`,
        `id_shop`,
        `id_shop_group`,
        `id_currency`,
        `id_country`,
        `id_group`,
        `id_customer`,
        `id_product_attribute`,
        `price`,
        `from_quantity`,
        `reduction`,
        `reduction_tax`,
        `reduction_type`,
        `from`,
        `to`,
        FROM "._DB_PREFIX_."specific_price po 
        left JOIN "._DB_PREFIX_."specific_price_rule pot 
        ON (po.id_specific_price_rule=pot.id_specific_price_rule)group by po.reference'";


        // $order_query = Db::getInstance()->executeS($order_sql);

        // foreach ($order_query as $key=>$paperfly_order){
        //     specific_price
        // }
        die(json_encode([
            'preview' => 'lroem',
            'modal'   => 'ipsdddum'
        ]));
    }


}
