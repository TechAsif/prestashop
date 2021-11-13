<?php
/**
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
*/

class WkMpInstall
{
    public function createMpTables()
    {
        $mpSuccess = true;
        $mpDatabaseInstance = Db::getInstance();
        if ($tableQueries = $this->getMpTableQueries()) {
            foreach ($tableQueries as $mpQuery) {
                $mpSuccess &= $mpDatabaseInstance->execute(trim($mpQuery));
            }
        }

        return $mpSuccess;
    }

    private function getMpTableQueries()
    {
        return array(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller` (
                `id_seller` int(10) unsigned NOT NULL auto_increment,
                `shop_name_unique` varchar(255) character set utf8 NOT NULL,
                `link_rewrite` varchar(255) character set utf8 NOT NULL,
                `seller_firstname` varchar(255) character set utf8 NOT NULL,
                `seller_lastname` varchar(255) character set utf8 NOT NULL,
                `business_email` varchar(128) NOT NULL,
                `phone` varchar(32) DEFAULT NULL,
                `fax` varchar(32) DEFAULT NULL,
                `address` text,
                `postcode` varchar(12) DEFAULT NULL,
                `city` varchar(64) DEFAULT NULL,
                `id_country` int(10) unsigned NOT NULL DEFAULT '0',
                `id_state` int(10) unsigned NOT NULL DEFAULT '0',
                `tax_identification_number` varchar(255) DEFAULT  NULL,
                `default_lang` int(10) unsigned NOT NULL DEFAULT '0',
                `facebook_id` varchar(255) character set utf8 NOT NULL,
                `twitter_id` varchar(255) character set utf8 NOT NULL,
                `youtube_id` varchar(255) character set utf8 NOT NULL,
                `instagram_id` varchar(255) character set utf8 NOT NULL,
                `profile_image` varchar(15) NOT NULL,
                `profile_banner` varchar(15) NOT NULL,
                `shop_image` varchar(15) NOT NULL,
                `shop_banner` varchar(15) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `shop_approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `seller_customer_id` int(10) unsigned NOT NULL,
                `seller_details_access` varchar(255) character set utf8 NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_seller`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_lang` (
                `id_seller` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `shop_name` varchar(255) character set utf8 NOT NULL,
                `about_shop` text,
                PRIMARY KEY (`id_seller`, `id_lang`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_help_desk` (
                `id_mp_help_desk` int(10) unsigned NOT NULL auto_increment,
                `id_product` int(11),
                `id_customer` int(11),
                `id_seller` int(11),
                `subject` varchar(128) DEFAULT NULL,
                `description` text,
                `customer_email` varchar(128) NOT NULL,
                `active` tinyint(1) NOT NULL DEFAULT '0',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_help_desk`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_review` (
                `id_review` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(11),
                `id_customer` int(11),
                `customer_email` varchar(100),
                `rating` int(11),
                `review` text,
                `active` tinyint(1),
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_review`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_review_likes` (
                `id_review_like` int(10) unsigned NOT NULL auto_increment,
                `id_review` int(10) unsigned NOT NULL,
                `id_customer` int(10) unsigned NOT NULL,
                `like` tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_review_like`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_product` (
                `id_mp_product` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(10) unsigned NOT NULL,
                `id_ps_product` int(10) unsigned DEFAULT 0,
                `id_mp_duplicate_product_parent` int(10) unsigned DEFAULT 0,
                `id_ps_shop` int(10) unsigned DEFAULT 1,
                `id_category` int(10) unsigned DEFAULT 1,
                `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `unity` varchar(255) character set utf8 NOT NULL,
                `unit_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `id_tax_rules_group` int(10) unsigned NOT NULL DEFAULT '0',
                `on_sale` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `additional_shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
                `quantity` int(10) NOT NULL DEFAULT '0',
                `minimal_quantity` int(10) NOT NULL DEFAULT '1',
                `low_stock_threshold` int(10) NULL DEFAULT NULL,
                `low_stock_alert` TINYINT(1) NOT NULL DEFAULT '0',
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `status_before_deactivate` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `show_condition` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `condition` ENUM('new', 'used', 'refurbished') NOT NULL DEFAULT 'new',
                `available_for_order` tinyint(1) NOT NULL DEFAULT '1',
                `show_price` tinyint(1) NOT NULL DEFAULT '1',
                `online_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `visibility` ENUM('both', 'catalog', 'search', 'none') NOT NULL DEFAULT 'both',
                `admin_assigned` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `width` decimal(20, 6) NOT NULL DEFAULT '0',
                `height` decimal(20, 6) NOT NULL DEFAULT '0',
                `depth` decimal(20, 6) NOT NULL DEFAULT '0',
                `weight` decimal(20, 6) NOT NULL DEFAULT '0',
                `reference` varchar(32) character set utf8 NOT NULL,
                `ean13` varchar(13) character set utf8 NOT NULL,
                `upc` varchar(12) character set utf8 NOT NULL,
                `isbn` varchar(13) character set utf8 NOT NULL,
                `out_of_stock` tinyint(1) unsigned NOT NULL DEFAULT '2',
                `available_date` date NOT NULL,
                `ps_id_carrier_reference` text,
                `admin_approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `additional_delivery_times` tinyint(1) unsigned NOT NULL DEFAULT '1',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_product`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_product_lang` (
                `id_mp_product` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `product_name` varchar(255) character set utf8 NOT NULL,
                `short_description` text,
                `description` text,
                `available_now` varchar(255) character set utf8 NOT NULL,
                `available_later` varchar(255) character set utf8 NOT NULL,
                `meta_title` varchar(255) character set utf8 NOT NULL,
                `meta_description` varchar(255) character set utf8 NOT NULL,
                `link_rewrite` varchar(255) character set utf8 NOT NULL,
                `delivery_in_stock` varchar(255) DEFAULT NULL,
                `delivery_out_stock` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id_mp_product`, `id_lang`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_product_category` (
                `id_mp_category_product` int(10) unsigned NOT NULL auto_increment,
                `id_category` int(10) unsigned DEFAULT 1,
                `id_seller_product` int(10) unsigned NOT NULL,
                `is_default` tinyint(1) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_mp_category_product`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_product_image` (
                `id_mp_product_image` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `seller_product_id` int(10) NOT NULL,
                `seller_product_image_name` varchar(15) NOT NULL,
                `id_ps_image` int(10) NOT NULL,
                `position` int(10) NOT NULL DEFAULT '0',
                `cover` tinyint(1) DEFAULT NULL,
                `active` tinyint(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id_mp_product_image`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_feature` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `ps_id_feature` int(10) unsigned NOT NULL,
                `mp_id_product` int(10) unsigned NOT NULL,
                `ps_id_feature_value` int(10) unsigned NOT NULL,
                `mp_id_feature_value` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_feature_value` (
                `mp_id_feature_value` int(10) unsigned NOT NULL auto_increment,
                `ps_id_feature` int(10) unsigned NOT NULL,
                `is_custom` tinyint(1) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`mp_id_feature_value`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_feature_value_lang` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `ps_id_feature_value` int(10) unsigned NOT NULL,
                `mp_id_feature_value` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `value` varchar(255) character set utf8 NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_attribute` (
                `id_mp_product_attribute` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_mp_product` int(10) unsigned NOT NULL,
                `id_ps_product_attribute` int(10) unsigned NOT NULL,
                `id_ps_product` int(10) unsigned NOT NULL,
                `mp_reference` varchar(32) DEFAULT NULL,
                `mp_ean13` varchar(13) DEFAULT NULL,
                `mp_upc` varchar(12) DEFAULT NULL,
                `mp_isbn` varchar(12) DEFAULT NULL,
                `mp_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_unit_price_impact` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_quantity` int(10) NOT NULL DEFAULT '0',
                `mp_weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_default_on` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `mp_minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1',
                `mp_available_date` date NOT NULL,
                `low_stock_threshold` int(10) NULL DEFAULT NULL,
                `low_stock_alert` TINYINT(1) NOT NULL DEFAULT '0',
                `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
                PRIMARY KEY (`id_mp_product_attribute`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_attribute_shop` (
                `id_mp_product` int(10) unsigned NOT NULL,
                `id_mp_product_attribute` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `mp_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_unit_price_impact` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `mp_default_on` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `mp_minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1',
                `mp_available_date` date NOT NULL,
                `low_stock_threshold` int(10) NULL DEFAULT NULL,
                `low_stock_alert` TINYINT(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id_mp_product_attribute`,`id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_attribute_image` (
                `id_mp_product_attribute` int(10) unsigned NOT NULL,
                `id_image` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_mp_product_attribute`,`id_image`),
                KEY `id_image` (`id_image`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_product_attribute_combination` (
                `id_ps_attribute` int(10) unsigned NOT NULL,
                `id_mp_product_attribute` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ps_attribute`,`id_mp_product_attribute`),
                KEY `id_mp_product_attribute` (`id_mp_product_attribute`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_attribute_impact` (
                `id_mp_attribute_impact` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_mp_product` int(11) unsigned NOT NULL,
                `id_attribute` int(11) unsigned NOT NULL,
                `mp_weight` decimal(20,6) NOT NULL,
                `mp_price` decimal(17,2) NOT NULL,
                PRIMARY KEY (`id_mp_attribute_impact`),
                UNIQUE KEY `id_mp_product` (`id_mp_product`,`id_attribute`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_stock_available` (
                `id_mp_stock_available` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_mp_product` int(11) unsigned NOT NULL,
                `id_mp_product_attribute` int(11) unsigned NOT NULL,
                `id_shop` int(11) unsigned NOT NULL,
                `id_shop_group` int(11) unsigned NOT NULL,
                `quantity` int(10) NOT NULL DEFAULT '0',
                `depends_on_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `out_of_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id_mp_stock_available`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_commision` (
                `id_wk_mp_commision` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller` int(10) NOT NULL,
                `commision_type` varchar(64) DEFAULT NULL,
                `commision_rate` decimal(20,2) NOT NULL DEFAULT '0.00',
                `commision_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commision_tax_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_customer_id` int(10) NOT NULL,
                PRIMARY KEY (`id_wk_mp_commision`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_payment_mode` (
                `id_mp_payment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `payment_mode` varchar(255) NOT NULL,
                PRIMARY KEY (`id_mp_payment`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_customer_payment_detail` (
                `id_customer_payment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `seller_customer_id` int(10) unsigned NOT NULL,
                `payment_mode_id` int(10) unsigned NOT NULL,
                `payment_detail` varchar(255) NOT NULL,
                PRIMARY KEY (`id_customer_payment`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order` (
                `id_mp_order` int(10) unsigned NOT NULL auto_increment,
                `seller_customer_id` int(10) unsigned NOT NULL,
                `seller_id` int(10) unsigned NOT NULL,
                `seller_shop` varchar(255) character set utf8 NOT NULL,
                `seller_firstname` varchar(255) character set utf8 NOT NULL,
                `seller_lastname` varchar(255) character set utf8 NOT NULL,
                `seller_email` varchar(128) NOT NULL,
                `total_earn_ti` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_earn_te` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_order`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_detail` (
                `id_mp_order_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller_order` int(10) NOT NULL,
                `product_id` int(10) NOT NULL,
                `product_attribute_id` int(10) NOT NULL,
                `id_customization` int(10) NOT NULL DEFAULT '0',
                `seller_customer_id` int(10) NOT NULL,
                `seller_name` varchar(255) NOT NULL,
                `product_name` varchar(255) NOT NULL,
                `quantity` int(10) NOT NULL,
                `price_ti` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `price_te` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `id_order` int(10) NOT NULL,
                `commission_type` varchar(64) DEFAULT NULL,
                `commission_rate` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commission_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commission_tax_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `tax_distribution_type` varchar(255) NOT NULL,
                `id_currency` int(10) NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_mp_order_detail`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_status` (
                `id_order_status` int(10) unsigned NOT NULL auto_increment,
                `id_order` int(10) unsigned NOT NULL,
                `id_seller` int(10) unsigned NOT NULL,
                `current_state` int(10) unsigned NOT NULL,
                `tracking_number` varchar(64) DEFAULT NULL,
                `tracking_url` varchar(255) DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_order_status`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_history` (
                `id_order_history` int(10) unsigned NOT NULL auto_increment,
                `id_order` int(10) unsigned NOT NULL,
                `id_seller` int(10) unsigned NOT NULL,
                `id_order_state` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_order_history`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_order_voucher` (
                `id_order_voucher` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `order_id` int(10) NOT NULL,
                `seller_id` int(10) NOT NULL,
                `voucher_name` varchar(255) NOT NULL,
                `voucher_value` decimal(20,6) NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_order_voucher`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_admin_shipping` (
                `id_wk_mp_admin_shipping` int(11) unsigned NOT NULL auto_increment,
                `order_id` int(11) NOT NULL,
                `order_reference` VARCHAR(9),
                `shipping_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_mp_admin_shipping`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_shipping_distribution` (
                `id_seller_shipping_distribution` int(11) unsigned NOT NULL auto_increment,
                `order_id` int(11) NOT NULL,
                `order_reference` VARCHAR(9),
                `seller_customer_id` int(10) unsigned NOT NULL,
                `seller_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                PRIMARY KEY (`id_seller_shipping_distribution`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_transaction_history`(
                `id_seller_transaction_history` int(10) unsigned NOT NULL auto_increment,
                `id_customer_seller` int(10) NOT NULL,
                `id_currency` int(10) unsigned NOT NULL,
                `id_mp_order_detail` int(10) NOT NULL DEFAULT '0',
                `seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_shipping` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_refunded_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_receive` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_shipping` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_refunded_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `payment_method` varchar(255) NOT NULL DEFAULT 'Manual',
                `transaction_type` varchar(255) NOT NULL DEFAULT 'order',
                `id_transaction` varchar(254) NULL DEFAULT '0',
                `remark` varchar(255) DEFAULT NULL,
                `status` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_seller_transaction_history`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_carrier_distributor_type` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_ps_reference` int(11) unsigned NOT NULL,
                `type` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_shipping_commission` (
                `id_wk_mp_shipping_commission` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller` int(10) NOT NULL,
                `commission_rate` decimal(20,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id_wk_mp_shipping_commission`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
        );
    }

    public function deleteMpTables()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS
            `'._DB_PREFIX_.'wk_mp_seller`,
            `'._DB_PREFIX_.'wk_mp_seller_lang`,
            `'._DB_PREFIX_.'wk_mp_seller_help_desk`,
            `'._DB_PREFIX_.'wk_mp_seller_review`,
            `'._DB_PREFIX_.'wk_mp_seller_review_likes`,
            `'._DB_PREFIX_.'wk_mp_seller_product`,
            `'._DB_PREFIX_.'wk_mp_seller_product_lang`,
            `'._DB_PREFIX_.'wk_mp_seller_product_category`,
            `'._DB_PREFIX_.'wk_mp_seller_product_image`,
            `'._DB_PREFIX_.'wk_mp_product_feature`,
            `'._DB_PREFIX_.'wk_mp_product_feature_value`,
            `'._DB_PREFIX_.'wk_mp_product_feature_value_lang`,
            `'._DB_PREFIX_.'wk_mp_product_attribute`,
            `'._DB_PREFIX_.'wk_mp_product_attribute_image`,
            `'._DB_PREFIX_.'wk_mp_product_attribute_combination`,
            `'._DB_PREFIX_.'wk_mp_product_attribute_shop`,
            `'._DB_PREFIX_.'wk_mp_attribute_impact`,
            `'._DB_PREFIX_.'wk_mp_stock_available`,
            `'._DB_PREFIX_.'wk_mp_commision`,
            `'._DB_PREFIX_.'wk_mp_payment_mode`,
            `'._DB_PREFIX_.'wk_mp_customer_payment_detail`,
            `'._DB_PREFIX_.'wk_mp_seller_order`,
            `'._DB_PREFIX_.'wk_mp_seller_order_detail`,
            `'._DB_PREFIX_.'wk_mp_seller_order_status`,
            `'._DB_PREFIX_.'wk_mp_seller_order_history`,
            `'._DB_PREFIX_.'wk_mp_order_voucher`,
            `'._DB_PREFIX_.'wk_mp_admin_shipping`,
            `'._DB_PREFIX_.'wk_mp_seller_shipping_distribution`,
            `'._DB_PREFIX_.'wk_mp_seller_transaction_history`,
            `'._DB_PREFIX_.'wk_mp_carrier_distributor_type`,
            `'._DB_PREFIX_.'wk_mp_shipping_commission`'
        );
    }
}
