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

class WkMpSellerPaymentSplit extends CartRule
{
    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public function sellerWiseSplitedAmount($params, $saveVoucher = false)
    {
        //$order = $params['order'];
        $order = new Order((int) $params['id_order']);
        $cart = $params['cart'];

        $idOrder = $order->id;

        $voucherAllInfo = array();
        $orderProductDetails = array();
        $giftProductListInfo = array();
        $cheapestProduct = array();
        $appliedVoucherListInfo = array();
        $appliedVoucherFixedPriceInfo = array();
        $wkSpecificProductIds = array();
        $orderProductDetails['is_gift_product'] = 0;

        $cartRules = $cart->getCartRules();
        $cartProducts = $cart->getProducts();

        if ($cartRules) {
            $voucherAllInfo = $this->calculateVoucher($cartRules, $cartProducts); //get information of all vouchers
            $appliedVoucherListInfo = $voucherAllInfo['order']; //voucher for order
            $appliedVoucherFixedPriceInfo = $voucherAllInfo['fixed_price']; // specific amount voucher
            $giftProductListInfo = $voucherAllInfo['gift_product']; //all git product info
            $cheapestProduct = $voucherAllInfo['cheapest_product']; //cheapest product voucher info
        }

        $cartProducts = $order->getProducts();
        foreach ($cartProducts as $cartProductKey => $product) {
            $cartProducts[$cartProductKey]['id_product'] = $product['product_id'];
            $cartProducts[$cartProductKey]['id_product_attribute'] = $product['product_attribute_id'];
            $cartProducts[$cartProductKey]['cart_quantity'] = $product['product_quantity'];
            $cartProducts[$cartProductKey]['price_wt'] = $product['unit_price_tax_incl'];
            $cartProducts[$cartProductKey]['price'] = $product['unit_price_tax_excl'];
        }

        // get cart order products, customer, seller details
        $objMpSellerOrderDetails = new WkMpSellerOrderDetail();
        $sellerCartProducts = $objMpSellerOrderDetails->getSellerProductByIdOrder($idOrder);
        if ($sellerCartProducts) {
            $objMpCommission = new WkMpCommission();

            $sellerProduct = array();
            $orderTotalWeight = 0;
            $orderTotalProducts = 0;
            $orderTotalPrice = 0;
            $conversionRate = WkMpSellerOrder::getCurrencyConversionRate(
                $this->context->currency->id,
                Configuration::get('PS_CURRENCY_DEFAULT')
            );
            $showVoucherDetails = array();
            foreach ($sellerCartProducts as $product) {
                if (!$product['id_customization']) {
                    $product['id_customization'] = 0;
                }
                // check if gift product availble in order
                $orderProductDetails = $this->checkGiftProduct(
                    $product['product_id'],
                    $product['product_attribute_id'],
                    $product['product_quantity'],
                    $giftProductListInfo
                );

                // ordered product quantity excluding gift product
                $product['product_quantity'] = $orderProductDetails['quantity'];
                // calculate reduction order reduction percentage
                if (!$orderProductDetails['is_gift_product']) {
                    $reductionDetails = $this->calculateReductionPercentage(
                        $product['product_id'].'-'.$product['id_customization'],
                        $product['product_attribute_id'],
                        $appliedVoucherListInfo
                    );

                    $productPricePercentage = 100 - $reductionDetails['price'];
                    $productTaxPercentage = 100 - $reductionDetails['tax'];

                    /*Prevent a case where two voucher applied on a cart,
                    where first voucher is making order value zero and second voucher
                    has some discount on specific product, then seller and admin
                    amount will be calcuated in negative...*/
                    if ($productPricePercentage < 0) {
                        $productPricePercentage = 0;
                        $productTaxPercentage = 0;
                    }
                    // End of code -----------------------------------------------------------------

                    $productPrice = $this->getCartProductPriceByIdProductAndIdAttribute(
                        $cartProducts,
                        $product['product_id'],
                        $product['product_attribute_id']
                    );

                    // calculate product tax
                    $taxAmount = (((($productPrice['price_ti'] - $productPrice['price_te']) * $product['product_quantity']) * $productTaxPercentage) / 100);

                    // calculate product price
                    $mpProductPriceTE = ((($productPrice['price_te'] * $product['product_quantity']) * $productPricePercentage) / 100);
                    $mpProductPriceTI = ((($productPrice['price_ti'] * $product['product_quantity']) * $productPricePercentage) / 100);
                    if ($saveVoucher) {
                        $voucherValue = (float) Tools::ps_round((float) ($productPrice['price_ti'] * $product['product_quantity']), 2);
                        $showVoucherDetails = $this->calculateReductionPercentageForShowingVoucher(
                            $product['product_id'].'-'.$product['id_customization'],
                            $product['product_attribute_id'],
                            $appliedVoucherListInfo,
                            $voucherValue,
                            $idOrder,
                            $product['id_seller'],
                            $showVoucherDetails
                        );
                    }

                    if (array_key_exists($product['product_id'], $appliedVoucherFixedPriceInfo)) {
                        /* If voucher for specific product of fixed amount than
                        that amount is deducted from total product price and tax */
                        foreach ($appliedVoucherFixedPriceInfo[$product['product_id']] as $value) {
                            if (array_key_exists($product['id_seller'], $showVoucherDetails)
                            && array_key_exists($value['ps_id_cart_rule'], $showVoucherDetails[$product['id_seller']])
                            ) {
                                if (!in_array($product['product_id'], $wkSpecificProductIds)) {
                                    $taxAmount -= $value['reduction_tax'];
                                    $mpProductPriceTE -= $value['reduction_amount'];
                                    $mpProductPriceTI -= ($value['reduction_amount'] + $value['reduction_tax']);
                                    $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_value'] += $value['reduction_amount'] + $value['reduction_tax'];
                                }
                            } else {
                                $taxAmount -= $value['reduction_tax'];
                                $mpProductPriceTE -= $value['reduction_amount'];
                                $mpProductPriceTI -= ($value['reduction_amount'] + $value['reduction_tax']);
                                $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_value'] = $value['reduction_amount'] + $value['reduction_tax'];
                                $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['id_order'] = $idOrder;
                                $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_name'] = $value['voucher_name'];
                                $wkSpecificProductIds[] = $product['product_id'];
                            }
                        }
                    }

                    if (array_key_exists('id_product', $cheapestProduct)) { // if voucher for cheapest product
                        if ($cheapestProduct['id_product'] == $product['product_id']
                        && $cheapestProduct['id_product_attribute'] == $product['product_attribute_id']
                        && $cheapestProduct['id_customization'] == $product['id_customization']) {
                            $productPriceOfCheapestProduct = (($cheapestProduct['discount_percentage'] * $productPrice['price_te']) / 100);
                            $productTaxOfCheapestProduct = (($cheapestProduct['discount_percentage'] * ($productPrice['price_ti'] - $productPrice['price_te'])) / 100);

                            $mpProductPriceTE -= $productPriceOfCheapestProduct;
                            $taxAmount -= $productTaxOfCheapestProduct;
                            $mpProductPriceTI -= ($productPriceOfCheapestProduct + $productTaxOfCheapestProduct);
                            foreach ($cheapestProduct['cheapest_voucher'] as $value) {
                                if (array_key_exists($product['id_seller'], $showVoucherDetails) && array_key_exists($value['ps_id_cart_rule'], $showVoucherDetails[$product['id_seller']])) {
                                    $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_value'] += (float)($value['value'] * $productPrice['price_ti']) / 100;
                                } else {
                                    $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_value'] = (float)($value['value'] * $productPrice['price_ti']) / 100;
                                    $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['id_order'] = $idOrder;
                                    $showVoucherDetails[$product['id_seller']][$value['ps_id_cart_rule']]['voucher_name'] = $value['voucher_name'];
                                }
                            }
                        }
                    }

                    $product['total_price_tax_incl'] = $mpProductPriceTI;
                    $product['total_price_tax_excl'] = $mpProductPriceTE;
                }

                if (!($voucherAllInfo && $orderProductDetails['is_gift_product'])) {
                    $orderTotalProducts += $product['product_quantity'];
                    $orderTotalWeight += ($product['product_weight'] * $product['product_quantity']);
                    $orderTotalPrice += $product['total_price_tax_incl'];
                    if ($product['mp_id_product']) {
                        $commissionType = WkMpCommission::WK_COMMISSION_PERCENTAGE;
                        $commissionRate = 0;
                        $commissionFixedAmount = 0;
                        $commissionFixedAmountOnTax = 0;

                        $sellerFinalCommission = $objMpCommission->getFinalCommissionForSeller($product['id_seller']);
                        if ($sellerFinalCommission) {
                            $commissionType = $sellerFinalCommission['commission_type'];
                            $commissionRate = $sellerFinalCommission['commission_rate'];
                            $commissionFixedAmount = $sellerFinalCommission['commission_fixed_amt'];
                            $commissionFixedAmountOnTax = $sellerFinalCommission['commission_fixed_tax_amt'];
                        }

                        //Hook defined to override admin commission rate ie. mp advance commission module
                        $mpAdvanceCommissionRate = Hook::exec(
                            'actionOverrideMpAdminCommission',
                            array(
                                'sellerProductDetail' => $product,
                                'action' => 'marketplace'
                            )
                        );
                        if ($mpAdvanceCommissionRate) {
                            $commissionRate = $mpAdvanceCommissionRate;
                            //if commission returning from mp advance commision module then
                            //By default commission type will always percentage because module is managing only percent
                            $commissionType = WkMpCommission::WK_COMMISSION_PERCENTAGE;
                        }

                        if (($commissionType == WkMpCommission::WK_COMMISSION_FIXED)
                        || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
                            //If order currency is different from default currency
                            if ($this->context->currency->id !== Configuration::get('PS_CURRENCY_DEFAULT')) {
                                $commissionFixedAmount = Tools::convertPriceFull(
                                    $commissionFixedAmount,
                                    new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                                    new Currency($this->context->currency->id)
                                );

                                $commissionFixedAmountOnTax = Tools::convertPriceFull(
                                    $commissionFixedAmountOnTax,
                                    new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                                    new Currency($this->context->currency->id)
                                );
                            }

                            //calcuate total fixed amt and fixed tax amt according to product quantity
                            $commissionFixedAmount *= $product['product_quantity'];
                            $commissionFixedAmountOnTax *= $product['product_quantity'];
                        }

                        $adminCommission = 0;
                        $sellerAmount = 0;
                        $adminTax = 0;
                        $sellerTax = 0;

                        $commissionActualRate = 0;
                        $commissionActualAmount = 0;
                        $commissionActualTaxAmount = 0;

                        if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                            $commissionActualRate = $commissionRate;
                            //Admin commission according to percentage
                            $adminCommission = (($product['total_price_tax_excl']) * $commissionRate) / 100;
                        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
                            if ($commissionFixedAmount > $product['total_price_tax_excl']) {
                                //If fixed commission is greater than total price then total price will be admin commi.
                                $adminCommission = $product['total_price_tax_excl'];
                                $commissionActualAmount = $product['total_price_tax_excl'];
                            } else {
                                //Fixed tax excl commission will be admin commission
                                $adminCommission = $commissionFixedAmount;
                                $commissionActualAmount = $commissionFixedAmount;
                            }
                        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                            $commissionActualRate = $commissionRate;
                            //Fixed and percentage base commission
                            //Percentage will always calculate on total price tax excl.
                            $adminCommission = (($product['total_price_tax_excl']) * $commissionRate) / 100;

                            //And then fixed commission will added in rest commission
                            $pendingMpProductPriceTE = $product['total_price_tax_excl'] - $adminCommission;
                            if ($commissionFixedAmount > $pendingMpProductPriceTE) {
                                //If fixed commission is greater than total price then total price will be admin commi.
                                $adminCommission += $pendingMpProductPriceTE;
                                $commissionActualAmount = $pendingMpProductPriceTE;
                            } else {
                                $adminCommission += $commissionFixedAmount;
                                $commissionActualAmount = $commissionFixedAmount;
                            }
                        }

                        //Rest tax excl amount will goes to seller
                        if ($product['total_price_tax_excl'] > $adminCommission) {
                            $sellerAmount = $product['total_price_tax_excl'] - $adminCommission;
                        }

                        //Distribution of product tax
                        $totalTax = $product['total_price_tax_incl'] - $product['total_price_tax_excl'];
                        if (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'admin') {
                            $adminTax = $totalTax;
                        } elseif (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'seller') {
                            $sellerTax = $totalTax;
                        } elseif (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'distribute_both') {
                            if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                                $adminTax = ($totalTax * $commissionRate) / 100;
                            } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
                                if ($commissionFixedAmountOnTax) {
                                    if ($commissionFixedAmountOnTax > $totalTax) {
                                        //If fixed tax commission is greater than total tax
                                        $adminTax = $totalTax;
                                        $commissionActualTaxAmount = $totalTax;
                                    } else {
                                        $adminTax = $commissionFixedAmountOnTax;
                                        $commissionActualTaxAmount = $commissionFixedAmountOnTax;
                                    }
                                }
                            } elseif ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                                //Fixed and percentage base commission
                                $adminTax = ($totalTax * $commissionRate) / 100;

                                if ($commissionFixedAmountOnTax) {
                                    $pendingTaxAmount = $totalTax - $adminTax;
                                    if ($commissionFixedAmountOnTax > $pendingTaxAmount) {
                                        //If fixed tax commission is greater than total tax
                                        $adminTax += $pendingTaxAmount;
                                        $commissionActualTaxAmount = $pendingTaxAmount;
                                    } else {
                                        $adminTax += $commissionFixedAmountOnTax;
                                        $commissionActualTaxAmount = $commissionFixedAmountOnTax;
                                    }
                                }
                            }

                            //Rest tax amount will goes to seller
                            if ($totalTax > $adminTax) {
                                $sellerTax = $totalTax - $adminTax;
                            }
                        }

                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']] = $product;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['admin_commission'] = $adminCommission;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['admin_tax'] = $adminTax;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['seller_amount'] = $sellerAmount;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['seller_tax'] = $sellerTax;

                        //Commission calculation data
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['commission_type'] = $commissionType;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['commission_rate'] = $commissionActualRate;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['commission_amt'] = $commissionActualAmount;
                        $sellerProduct[$product['id_customer']]['product_list'][$product['id_ps_product']][$product['product_attribute_id']][$product['id_customization']]['commission_tax_amt'] = $commissionActualTaxAmount;

                        $sellerProduct[$product['id_customer']]['seller_name'] = $product['seller_firstname'].' '.$product['seller_lastname'];
                        $sellerProduct[$product['id_customer']]['seller_email'] = $product['business_email'];
                        $sellerProduct[$product['id_customer']]['seller_default_lang_id'] = $product['default_lang'];

                        /* In sellerProduct array products are grouped by seller.
                            First index is seller's customer id, inside this array 'product_list'
                            index have all product of currenct index seller,
                            total_admin_commission have total admin commission of currenct index seller,
                            total_admin_tax is total admin tax if current index seller
                        */
                        if (array_key_exists('total_admin_commission', $sellerProduct[$product['id_customer']])) {
                            $sellerProduct[$product['id_customer']]['total_admin_commission'] += ($adminCommission * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_admin_tax'] += ($adminTax * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_seller_amount'] += ($sellerAmount * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_seller_tax'] += ($sellerTax * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_earn_ti'] += ($product['total_price_tax_incl'] * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_earn_te'] += ($product['total_price_tax_excl'] * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_price_tax_incl'] += $product['total_price_tax_incl'];
                            $sellerProduct[$product['id_customer']]['total_product_weight'] += ($product['product_weight'] * $product['product_quantity']);
                            $sellerProduct[$product['id_customer']]['no_of_products'] += $product['product_quantity'];
                        } else {
                            $sellerProduct[$product['id_customer']]['total_admin_commission'] = ($adminCommission * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_admin_tax'] = ($adminTax * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_seller_amount'] = ($sellerAmount * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_seller_tax'] = ($sellerTax * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_earn_ti'] = ($product['total_price_tax_incl'] * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_earn_te'] = ($product['total_price_tax_excl'] * $conversionRate);
                            $sellerProduct[$product['id_customer']]['total_price_tax_incl'] = $product['total_price_tax_incl'];
                            $sellerProduct[$product['id_customer']]['total_product_weight'] = ($product['product_weight'] * $product['product_quantity']);
                            $sellerProduct[$product['id_customer']]['no_of_products'] = $product['product_quantity'];
                        }
                    } else {
                        if (array_key_exists('admin', $sellerProduct)) {
                            $sellerProduct['admin']['total_price_tax_incl'] += $product['total_price_tax_incl'];
                            $sellerProduct['admin']['total_product_weight'] += ($product['product_weight'] * $product['product_quantity']);
                        } else {
                            $sellerProduct['admin']['total_price_tax_incl'] = $product['total_price_tax_incl'];
                            $sellerProduct['admin']['total_product_weight'] = ($product['product_weight'] * $product['product_quantity']);
                        }
                    }
                }

                // if gift product then reduce the quantity of that product
                if (!empty($voucherAllInfo) && !empty($giftProductListInfo)) {
                    if (isset($giftProductListInfo[$product['product_id']])) {
                        if (isset($giftProductListInfo[$product['product_id']][$product['product_attribute_id']])) {
                            $objMpProduct = new WkMpSellerProduct();
                            $mpProductDetail = WkMpSellerProduct::getSellerProductByPsIdProduct($product['product_id']);
                            if ($mpProductDetail) {
                                $objMpProduct = new WkMpSellerProduct($mpProductDetail['id_mp_product']);
                                $objMpProduct->quantity -= $giftProductListInfo[$product['product_id']][$product['product_attribute_id']];
                                $objMpProduct->save();
                            }
                        }
                    }
                }
            }

            if ($saveVoucher && $showVoucherDetails) {
                foreach ($showVoucherDetails as $key => $value) {
                    if ($key) {
                        $seller_id = $key;
                        foreach ($value as $val) {
                            $objMpOrderVoucher = new WkMpOrderVoucher();
                            $objMpOrderVoucher->order_id = $idOrder;
                            $objMpOrderVoucher->seller_id = $seller_id;
                            $objMpOrderVoucher->voucher_name = $val['voucher_name'];
                            $objMpOrderVoucher->voucher_value = $val['voucher_value'];
                            $objMpOrderVoucher->save();
                        }
                    }
                }
            }
            return $sellerProduct;
        } else {
            return false;
        }
    }

    public function paymentGatewaySplitedAmount($cartRules = false, $cartProducts = false, $productWise = false)
    {
        if (!$cartRules) {
            $cartRules = $this->context->cart->getCartRules();
        }

        if (!$cartProducts) {
            $cartProducts = $this->context->cart->getProducts();
        }

        $voucherAllInfo = $this->calculateVoucher($cartRules, $cartProducts); //get information of all vouchers

        $appliedVoucherListInfo = $voucherAllInfo['order']; //voucher for order
        $appliedVoucherFixedPriceInfo = $voucherAllInfo['fixed_price']; // specific amount voucher
        $giftProductListInfo = $voucherAllInfo['gift_product']; //all git product info
        $cheapestProduct = $voucherAllInfo['cheapest_product']; //cheapest product voucher info
        $isFreeShippingOnAppliedVoucher = $voucherAllInfo['free_shipping']; //is free shipping in any voucher

        $customerCommission = array();
        $sellerSplitAmount = array();
        $cartProductList = array(); // this array contain product details after voucher processing
        foreach ($cartProducts as $details) {
            $cartProductId = $details['id_product'];
            $cartProductIdAttribute = $details['id_product_attribute'];
            $cartProductIdCustomization = $details['id_customization'];
            if (!$cartProductIdCustomization) {
                $cartProductIdCustomization = 0;
            }

            // check is git product availble in cart
            $cartProductDetails = $this->checkGiftProduct(
                $cartProductId,
                $cartProductIdAttribute,
                $details['quantity'],
                $giftProductListInfo
            );

            // cart product quantity after gift product quantity reduction
            $cartProductQuantity = $cartProductDetails['quantity'];

            if (!$cartProductDetails['is_gift_product']) {
                // calculate reduction order reduction percentage
                $reductionDetails = $this->calculateReductionPercentage(
                    $cartProductId.'-'.$cartProductIdCustomization,
                    $cartProductIdAttribute,
                    $appliedVoucherListInfo
                );

                $productPricePercentage = 100 - $reductionDetails['price'];
                $productTaxPercentage = 100 - $reductionDetails['tax'];

                // check is mp product
                $mpSellerProductData = WkMpSellerProduct::getSellerProductByPsIdProduct($details['id_product']);
                if ($mpSellerProductData['id_mp_product'] > 0) {
                    $MpShopData = WkMpSeller::getSeller(
                        $mpSellerProductData['id_seller'],
                        $this->context->language->id
                    );

                    $commissionType = WkMpCommission::WK_COMMISSION_PERCENTAGE;
                    $commissionRate = 0;
                    $commissionFixedAmount = 0;
                    $commissionFixedAmountOnTax = 0;

                    $objMpCommission = new WkMpCommission();
                    $sellerFinalCommission = $objMpCommission->getFinalCommissionForSeller(
                        $mpSellerProductData['id_seller']
                    );
                    if ($sellerFinalCommission) {
                        $commissionType = $sellerFinalCommission['commission_type'];
                        $commissionRate = $sellerFinalCommission['commission_rate'];
                        $commissionFixedAmount = $sellerFinalCommission['commission_fixed_amt'];
                        $commissionFixedAmountOnTax = $sellerFinalCommission['commission_fixed_tax_amt'];
                    }

                    //Hook defined to override admin commission rate ie. mp advance commission module
                    $mpAdvanceCommissionRate = Hook::exec(
                        'actionOverrideMpAdminCommission',
                        array(
                            'sellerProductDetail' => $mpSellerProductData,
                            'action' => 'paymentgateway'
                        )
                    );
                    if ($mpAdvanceCommissionRate) {
                        $commissionRate = $mpAdvanceCommissionRate;
                        //if commission returning from mp advance commision module then
                        //By default commission type will always percentage because module is managing only percent
                        $commissionType = WkMpCommission::WK_COMMISSION_PERCENTAGE;
                    }

                    // Get product price
                    $productPrice = $this->getCartProductPriceByIdProductAndIdAttribute(
                        $cartProducts,
                        $cartProductId,
                        $cartProductIdAttribute
                    );

                    // calculate product tax
                    $taxAmount = (((($productPrice['price_ti'] - $productPrice['price_te']) * $cartProductQuantity) * $productTaxPercentage) / 100);

                    // calculate product price
                    $mpProductPriceTE = ((($productPrice['price_te'] * $cartProductQuantity) * $productPricePercentage) / 100);

                    if (array_key_exists($cartProductId, $appliedVoucherFixedPriceInfo)) {
                        // if voucher for specific product of fixed amount than that amount is deducted from total product price and tax
                        foreach ($appliedVoucherFixedPriceInfo[$cartProductId] as $value) {
                            $taxAmount -= $value['reduction_tax'];
                            $mpProductPriceTE -= $value['reduction_amount'];
                        }
                    }

                    if (array_key_exists('id_product', $cheapestProduct)) { // if voucher for cheapest product
                        if ($cheapestProduct['id_product'] == $cartProductId
                        && $cheapestProduct['id_product_attribute'] == $cartProductIdAttribute
                        && $cheapestProduct['id_customization'] == $cartProductIdCustomization
                        ) {
                            $productPriceOfCheapestProduct = (($cheapestProduct['discount_percentage'] * $productPrice['price_te']) / 100);
                            $productTaxOfCheapestProduct = (($cheapestProduct['discount_percentage'] * ($productPrice['price_ti'] - $productPrice['price_te'])) / 100);

                            $mpProductPriceTE -= $productPriceOfCheapestProduct;
                            $taxAmount -= $productTaxOfCheapestProduct;
                        }
                    }

                    if (($commissionType == WkMpCommission::WK_COMMISSION_FIXED)
                    || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
                        //If order currency is different from default currency
                        if ($this->context->currency->id !== Configuration::get('PS_CURRENCY_DEFAULT')) {
                            $commissionFixedAmount = Tools::convertPriceFull(
                                $commissionFixedAmount,
                                new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                                new Currency($this->context->currency->id)
                            );

                            $commissionFixedAmountOnTax = Tools::convertPriceFull(
                                $commissionFixedAmountOnTax,
                                new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                                new Currency($this->context->currency->id)
                            );
                        }

                        //calcuate total fixed amt and fixed tax amt according to product quantity
                        $commissionFixedAmount *= $details['quantity'];
                        $commissionFixedAmountOnTax *= $details['quantity'];
                    }

                    $adminCommisionAmt = 0;
                    $sellerCommisionAmt = 0;

                    if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                        //Admin commission according to percentage
                        $adminCommisionAmt = (float) Tools::ps_round(
                            (($mpProductPriceTE) * $commissionRate) / 100,
                            6
                        );
                    } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
                        if ($commissionFixedAmount > $mpProductPriceTE) {
                            //If fixed commission is greater than total price then total price will be admin commi.
                            $adminCommisionAmt = (float) Tools::ps_round($mpProductPriceTE, 6);
                        } else {
                            //Fixed tax excl commission will be admin commission
                            $adminCommisionAmt = (float) Tools::ps_round($commissionFixedAmount, 6);
                        }
                    } elseif ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                        //Fixed and percentage base commission
                        //Percentage will always calculate on total price tax excl.
                        $adminCommisionAmt = (float) Tools::ps_round(
                            (($mpProductPriceTE) * $commissionRate) / 100,
                            6
                        );
                        //And then fixed commission will added in rest commission
                        $pendingMpProductPriceTE = $mpProductPriceTE - $adminCommisionAmt;
                        if ($commissionFixedAmount > $pendingMpProductPriceTE) {
                            //If fixed commission is greater than total price then total price will be admin commi.
                            $adminCommisionAmt += (float) Tools::ps_round($pendingMpProductPriceTE, 6);
                        } else {
                            $adminCommisionAmt += (float) Tools::ps_round($commissionFixedAmount, 6);
                        }
                    }

                    //Rest tax excl amount will goes to seller
                    if ($mpProductPriceTE > $adminCommisionAmt) {
                        $sellerCommisionAmt = (float) Tools::ps_round(($mpProductPriceTE - $adminCommisionAmt), 6);
                    }

                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['weight'] = ($details['weight'] * $cartProductQuantity);
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['price'] = ($mpProductPriceTE + $taxAmount);
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['qty'] = $cartProductQuantity;
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['seller_customer_id'] = $MpShopData['seller_customer_id'];

                    // commission calculation
                    if (array_key_exists('admin', $customerCommission)) {
                        if (array_key_exists($MpShopData['seller_customer_id'], $customerCommission['admin'])) {
                            $customerCommission['admin'][$MpShopData['seller_customer_id']] += $adminCommisionAmt;
                        } else {
                            $customerCommission['admin'][$MpShopData['seller_customer_id']] = $adminCommisionAmt;
                        }
                    } else {
                        $customerCommission['admin'][$MpShopData['seller_customer_id']] = $adminCommisionAmt;
                    }

                    if ($productWise) {
                        $customerCommission[$details['id_product']] = $sellerCommisionAmt;
                    } else {
                        if (array_key_exists($MpShopData['seller_customer_id'], $customerCommission)) {
                            $customerCommission[$MpShopData['seller_customer_id']] += $sellerCommisionAmt;
                        } else {
                            $customerCommission[$MpShopData['seller_customer_id']] = $sellerCommisionAmt;
                        }
                    }

                    // tax distribution
                    $commisionToAdmin = 0;
                    $commisionToSeller = 0;
                    if (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'admin') {
                        $customerCommission['admin'][$MpShopData['seller_customer_id']] += $taxAmount;
                    } elseif (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'seller') {
                        $customerCommission[$MpShopData['seller_customer_id']] += $taxAmount;
                    } elseif (Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'distribute_both') {
                        if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                            $commisionToAdmin = ($taxAmount * $commissionRate) / 100;
                        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
                            if ($commissionFixedAmountOnTax) {
                                if ($commissionFixedAmountOnTax > $taxAmount) {
                                    //If fixed tax commission is greater than total tax
                                    $commisionToAdmin = $taxAmount;
                                } else {
                                    $commisionToAdmin = $commissionFixedAmountOnTax;
                                }
                            }
                        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                            //Fixed and percentage base commission
                            $commisionToAdmin = ($taxAmount * $commissionRate) / 100;

                            if ($commissionFixedAmountOnTax) {
                                $pendingTaxAmount = $taxAmount - $commisionToAdmin;
                                if ($commissionFixedAmountOnTax > $pendingTaxAmount) {
                                    //If fixed tax commission is greater than total tax
                                    $commisionToAdmin += $pendingTaxAmount;
                                } else {
                                    $commisionToAdmin += $commissionFixedAmountOnTax;
                                }
                            }
                        }

                        //Rest tax amount will goes to seller
                        if ($taxAmount > $commisionToAdmin) {
                            $commisionToSeller = $taxAmount - $commisionToAdmin;
                        }

                        $customerCommission['admin'][$MpShopData['seller_customer_id']] += $commisionToAdmin;
                        if ($productWise) {
                            $customerCommission[$details['id_product']] += $commisionToSeller;
                        } else {
                            $customerCommission[$MpShopData['seller_customer_id']] += $commisionToSeller;
                        }
                    }

                    $sellerSplitAmount[$MpShopData['seller_customer_id']]['total_price_tax_incl'] = $details['price_wt'];
                    $sellerSplitAmount[$MpShopData['seller_customer_id']]['total_product_weight'] = ($details['weight'] * $details['quantity_available']);
                } else {
                    // admin product
                    $productPrice = $this->getCartProductPriceByIdProductAndIdAttribute(
                        $cartProducts,
                        $cartProductId,
                        $cartProductIdAttribute
                    );
                    $taxAmount = (((($productPrice['price_ti'] - $productPrice['price_te']) * $cartProductQuantity) * $productTaxPercentage) / 100);
                    $productPriceTI = ((($productPrice['price_te'] * $cartProductQuantity) * $productPricePercentage) / 100);

                    if (array_key_exists($cartProductId, $appliedVoucherFixedPriceInfo)) {
                        $taxAmount -= $appliedVoucherFixedPriceInfo[$cartProductId]['reduction_tax'];
                        $productPriceTI -= $appliedVoucherFixedPriceInfo[$cartProductId]['reduction_amount'];
                    }

                    if (array_key_exists('id_product', $cheapestProduct)) {
                        if ($cheapestProduct['id_product'] == $cartProductId && $cheapestProduct['id_product_attribute'] == $cartProductIdAttribute) {
                            $productPriceOfCheapestProduct = (($cheapestProduct['discount_percentage'] * $productPrice['price_te']) / 100);
                            $productTaxOfCheapestProduct = (($cheapestProduct['discount_percentage'] * ($productPrice['price_ti'] - $productPrice['price_te'])) / 100);

                            $productPriceTI -= $productPriceOfCheapestProduct;
                            $taxAmount -= $productTaxOfCheapestProduct;
                        }
                    }
                    $productPriceTI += $taxAmount;
                    if (array_key_exists('admin', $customerCommission)) {
                        if (array_key_exists('own', $customerCommission['admin'])) {
                            $customerCommission['admin']['own'] += $productPriceTI;
                        } else {
                            $customerCommission['admin']['own'] = $productPriceTI;
                        }
                    } else {
                        $customerCommission['admin']['own'] = $productPriceTI;
                    }

                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['weight'] = ($details['weight'] * $cartProductQuantity);
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['price'] = $productPriceTI;
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['qty'] = $cartProductQuantity;
                    $cartProductList[$details['id_product'].'_'.$details['id_product_attribute']]['seller_customer_id'] = 'admin';

                    $sellerSplitAmount['admin']['total_price_tax_incl'] = $details['price_wt'];
                    $sellerSplitAmount['admin']['total_product_weight'] = ($details['weight'] * $details['quantity_available']);
                }
            }
        }
        // check is free shipping in any voucher
        if (!array_key_exists('own', $customerCommission['admin'])) {
            $customerCommission['admin']['own'] = 0;
        }

        $shippingDistribution = false;
        $distributorShippingCost = Hook::exec('actionShippingDistributionCost', array('seller_splitDetail' => $sellerSplitAmount, 'cart' => $this->context->cart), null, true);
        if ($distributorShippingCost) {
            foreach ($distributorShippingCost as $module) {
                if ($module) {
                    foreach ($module as $distributerKey => $distributorCost) {
                        if ($distributerKey != 'admin') {
                            //For sellers
                            $customerCommission[$distributerKey] += Tools::ps_round($distributorCost, 2);
                        } else {
                            //For admin
                            $customerCommission[$distributerKey]['own'] += Tools::ps_round($distributorCost, 2);
                        }
                    }
                    $shippingDistribution = true;
                }
            }
        } else {
            //If MP Shipping module is enabled then distribute shipping amount according to configuration
            if ($customerCommission && $sellerSplitAmount) {
                $distributorShippingCost = WkMpAdminShipping::getShippingDistributionData(
                    $sellerSplitAmount,
                    $this->context->cart
                );
                if ($distributorShippingCost) {
                    foreach ($distributorShippingCost as $distributerKey => $distributorCost) {
                        if ($distributerKey != 'admin') {
                            //For sellers
                            $customerCommission[$distributerKey] += Tools::ps_round($distributorCost, 2);
                        } else {
                            //For admin
                            $customerCommission[$distributerKey]['own'] += Tools::ps_round($distributorCost, 2);
                        }
                    }
                    $shippingDistribution = true;
                }
            }
        }

        //If Whole shipping will go to Admin
        if (!$isFreeShippingOnAppliedVoucher && !$shippingDistribution) {
            $customerCommission['admin']['own'] += $this->context->cart->getTotalShippingCost();
        }

        return $customerCommission;
    }

    public function calculateVoucher($cartRules, $cartProducts)
    {
        // calculate all voucher's and their type
        $voucherAllInfo = array();
        $appliedVoucherListInfo = array();
        $appliedVoucherFixedPriceInfo = array();
        $cheapestProduct = array();
        $isFreeShippingOnAppliedVoucher = false;

        $gift_info = $this->getGiftProducts($cartRules);
        $giftProductListInfo = $gift_info['gift_product_list'];
        $isFreeShippingOnAppliedVoucher = $gift_info['free_shipping'];
        $i = 0;
        $j = 0;
        $l = 0;
        $m = 0;
        foreach ($cartRules as $cartRule) {
            $objCartRule = new CartRule($cartRule['id_cart_rule']);
            if ((float) $objCartRule->reduction_amount) { //voucher is created as fixed amount
                $cartRuleReductionAmount = $objCartRule->reduction_amount;
                if ($this->context->cart->id_currency != $objCartRule->reduction_currency) {
                    // if voucher amount currency and cart currency are different
                    $voucherCurrency = new Currency($objCartRule->reduction_currency);

                    // First we convert the voucher value to the default currency
                    if ($cartRuleReductionAmount == 0 || $voucherCurrency->conversion_rate == 0) {
                        $cartRuleReductionAmount = 0;
                    } else {
                        $cartRuleReductionAmount /= $voucherCurrency->conversion_rate;
                    }

                    // Then we convert the voucher value in the default currency into the cart currency
                    $cartCurrency = new Currency($this->context->cart->id_currency);
                    $cartRuleReductionAmount *= $cartCurrency->conversion_rate;
                    $cartRuleReductionAmount = Tools::ps_round($cartRuleReductionAmount, 6);
                }

                $productId = $objCartRule->reduction_product;
                if ($productId > 0) { // voucher for specific product
                    $productPrice = $this->getCartProductPriceByIdProduct($cartProducts, $productId);
                    $productPriceTI = $productPrice['price_ti'];
                    $productPriceTE = $productPrice['price_te'];
                    $productVatAmount = $productPriceTI - $productPriceTE;

                    if ($productVatAmount == 0 || $productPriceTE == 0) {
                        $productVatRate = 0;
                    } else {
                        $productVatRate = $productVatAmount / $productPriceTE;
                    }
                    $productVat = $productVatRate * $cartRuleReductionAmount;

                    if ($objCartRule->reduction_tax) {
                        $reductionAmount = $cartRuleReductionAmount - $productVat;
                    } else {
                        $reductionAmount = $cartRuleReductionAmount;
                    }
                    $appliedVoucherFixedPriceInfo[$productId][$l]['reduction_tax'] = $productVat;
                    $appliedVoucherFixedPriceInfo[$productId][$l]['reduction_amount'] = $reductionAmount;
                    $appliedVoucherFixedPriceInfo[$productId][$l]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                    $appliedVoucherFixedPriceInfo[$productId][$l]['voucher_name'] = $cartRule['name'];
                    $appliedVoucherFixedPriceInfo[$productId][$l]['voucher_description'] = $cartRule['description'];
                    $l++;
                } elseif ($productId == 0) { // voucher for order without shipping
                    $cartAmount = $this->getOrderTotalWithOutGiftProductPrice($cartProducts, $giftProductListInfo);
                    $cartAmountTI = $cartAmount['ti'];
                    $cartAmountTE = $cartAmount['te'];
                    $cartVatAmount = $cartAmountTI - $cartAmountTE;

                    if ($cartVatAmount == 0 || $cartAmountTE == 0) {
                        $cartAverageVatRate = 0;
                    } else {
                        $cartAverageVatRate = 0;
                        if ($cartAmountTE > 0) {
                            $cartAverageVatRate = Tools::ps_round($cartVatAmount / $cartAmountTE, 3);
                        }
                    }

                    $cartRuleVatAmount = $cartAverageVatRate * $cartRuleReductionAmount;

                    if ($objCartRule->reduction_tax) {
                        //$reductionAmount = $cartRuleReductionAmount - $cartRuleVatAmount;
                        $reductionAmount = $cartRuleReductionAmount / (1 + $cartAverageVatRate);
                    } else {
                        $reductionAmount = $cartRuleReductionAmount;
                    }

                    if ($cartVatAmount && $cartRuleVatAmount) {
                        $appliedVoucherListInfo['order'][$i]['reduction_tax'] = (($cartRuleVatAmount * 100) / $cartVatAmount);
                    } else {
                        $appliedVoucherListInfo['order'][$i]['reduction_tax'] = 0;
                    }

                    // if ($reductionAmount > $cartAmountTE) {
                    //     $this->errors[] = 'Reduction amount is greater then cart amount.';
                    // }

                    $wkFinalReductionAmount = 0;
                    if ($cartAmountTE > 0) {
                        $wkFinalReductionAmount = ($reductionAmount * 100) / $cartAmountTE;
                    }
                    $appliedVoucherListInfo['order'][$i]['reduction_percent'] = $wkFinalReductionAmount;
                    $appliedVoucherListInfo['order'][$i]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                    $appliedVoucherListInfo['order'][$i]['voucher_name'] = $cartRule['name'];
                    $appliedVoucherListInfo['order'][$i]['voucher_description'] = $cartRule['description'];
                    $i++;
                }
            } elseif ((float) $objCartRule->reduction_percent) { //voucher is created as percentage
                $reductionPercent = $objCartRule->reduction_percent;
                $voucherType = (int) $objCartRule->reduction_product;
                if ($voucherType > 0) { // voucher for specific product and voucher_type is product id of that product
                    if (array_key_exists($voucherType, $appliedVoucherListInfo)) {
                        $appliedVoucherListInfo[$voucherType]['all_attr'][$j]['value'] = $reductionPercent;
                    } else {
                        $appliedVoucherListInfo[$voucherType]['all_attr'][$j]['value'] = $reductionPercent;
                    }
                    $appliedVoucherListInfo[$voucherType]['all_attr'][$j]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                    $appliedVoucherListInfo[$voucherType]['all_attr'][$j]['voucher_name'] = $cartRule['name'];
                    $appliedVoucherListInfo[$voucherType]['all_attr'][$j]['voucher_description'] = $cartRule['description'];
                    $j++;
                } elseif ($voucherType == 0) { // voucher for order without shipping
                    $appliedVoucherListInfo['order'][$i]['reduction_tax'] = $reductionPercent;
                    $appliedVoucherListInfo['order'][$i]['reduction_percent'] = $reductionPercent;
                    $appliedVoucherListInfo['order'][$i]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                    $appliedVoucherListInfo['order'][$i]['voucher_name'] = $cartRule['name'];
                    $appliedVoucherListInfo['order'][$i]['voucher_description'] = $cartRule['description'];
                    $i++;
                } elseif ($voucherType == -1) { // vaucher for cart cheapest product
                    $minProductPrice = $cartProducts[0]['price_wt'];
                    $minProductId = $cartProducts[0]['id_product'];
                    $minProductIdAttribute = $cartProducts[0]['id_product_attribute'];
                    $minProductIdCustomization = $cartProducts[0]['id_customization'];
                    foreach ($cartProducts as $cartProduct) {
                        $productPriceWT = $cartProduct['price_wt'];
                        if ($productPriceWT < $minProductPrice) {
                            $minProductPrice = $productPriceWT;
                            $minProductId = $cartProduct['id_product'];
                            $minProductIdAttribute = $cartProduct['id_product_attribute'];
                            $minProductIdCustomization = $cartProduct['id_customization'];
                        }
                    }

                    if (!$minProductIdCustomization) {
                        $minProductIdCustomization = 0;
                    }

                    if ($minProductId != 0) {
                        $cheapestProduct['id_product'] = $minProductId;
                        $cheapestProduct['id_product_attribute'] = $minProductIdAttribute;
                        $cheapestProduct['id_customization'] = $minProductIdCustomization;
                        if (array_key_exists('discount_percentage', $cheapestProduct)) {
                            $cheapestProduct['discount_percentage'] += $reductionPercent;
                        } else {
                            $cheapestProduct['discount_percentage'] = $reductionPercent;
                        }
                        $cheapestProduct['cheapest_voucher'][$m]['value'] = $reductionPercent;
                        $cheapestProduct['cheapest_voucher'][$m]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                        $cheapestProduct['cheapest_voucher'][$m]['voucher_name'] = $cartRule['name'];
                        $cheapestProduct['cheapest_voucher'][$m]['voucher_description'] = $cartRule['description'];
                        $m++;
                        //}
                    }
                } elseif ($voucherType == -2) {  // vaucher for selected product
                    $selectedProducts = $objCartRule->checkProductRestrictions($this->context, true);
                    if (is_array($selectedProducts)) {
                        $k = 0;
                        foreach ($cartProducts as $product) {
                            if ((in_array($product['id_product'].'-'.$product['id_product_attribute'], $selectedProducts)
                            || in_array($product['id_product'].'-0', $selectedProducts))) {
                                if (!$product['id_customization']) {
                                    $product['id_customization'] = 0;
                                }
                                $productwithCustomization = $product['id_product'].'-'.$product['id_customization'];
                                if (array_key_exists($productwithCustomization, $appliedVoucherListInfo)) {
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['value'] = $reductionPercent;
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['voucher_name'] = $cartRule['name'];
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['voucher_description'] = $cartRule['description'];
                                } else {
                                    $appliedVoucherListInfo[$productwithCustomization]['all_attr'] = array();
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['value'] = $reductionPercent;
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['ps_id_cart_rule'] = $cartRule['id_cart_rule'];
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['voucher_name'] = $cartRule['name'];
                                    $appliedVoucherListInfo[$productwithCustomization][$product['id_product_attribute']][$k]['voucher_description'] = $cartRule['description'];
                                }
                                $k++;
                            }
                        }
                    }
                }
            }
        }
        $voucherAllInfo['order'] = $appliedVoucherListInfo;
        $voucherAllInfo['fixed_price'] = $appliedVoucherFixedPriceInfo;
        $voucherAllInfo['gift_product'] = $giftProductListInfo;
        $voucherAllInfo['cheapest_product'] = $cheapestProduct;
        $voucherAllInfo['free_shipping'] = $isFreeShippingOnAppliedVoucher;
        return $voucherAllInfo;
    }

    public function getGiftProducts($cartRules)
    {
        $isFreeShippingOnAppliedVoucher = false;
        $giftProductListInfo = array();

        foreach ($cartRules as $cartRule) {
            $objCartRule = new CartRule($cartRule['id_cart_rule']);
            if ($objCartRule->gift_product != 0) {
                $productId = $objCartRule->gift_product;

                $product_id_attribute = $objCartRule->gift_product_attribute;
                if (array_key_exists($productId, $giftProductListInfo)) {
                    if (array_key_exists($product_id_attribute, $giftProductListInfo[$productId])) {
                        $giftProductListInfo[$productId][$product_id_attribute] += 1;
                    } else {
                        $giftProductListInfo[$productId][$product_id_attribute] = 1;
                    }
                } else {
                    $giftProductListInfo[$productId] = array();
                    if (array_key_exists($product_id_attribute, $giftProductListInfo[$productId])) {
                        $giftProductListInfo[$productId][$product_id_attribute] += 1;
                    } else {
                        $giftProductListInfo[$productId][$product_id_attribute] = 1;
                    }
                }
            }

            if ($objCartRule->free_shipping) {
                $isFreeShippingOnAppliedVoucher = true;
            }
            unset($objCartRule);
        }
        return array('gift_product_list' => $giftProductListInfo, 'free_shipping' => $isFreeShippingOnAppliedVoucher);
    }

    public function checkGiftProduct($cartProductId, $cartProductIdAttribute, $cartProductQuantity, $giftProductListInfo)
    {
        $cartProductDetails = array();
        $cartProductDetails['is_gift_product'] = false;
        if (array_key_exists($cartProductId, $giftProductListInfo)) {
            if (array_key_exists($cartProductIdAttribute, $giftProductListInfo[$cartProductId])) {
                if ($cartProductQuantity > $giftProductListInfo[$cartProductId][$cartProductIdAttribute]) {
                    $cartProductQuantity = $cartProductQuantity - $giftProductListInfo[$cartProductId][$cartProductIdAttribute];
                } else {
                    $cartProductDetails['is_gift_product'] = true;
                }
            }
        }
        $cartProductDetails['quantity'] = $cartProductQuantity;
        return $cartProductDetails;
    }

    public function calculateReductionPercentage($cartProductId, $cartProductIdAttribute, $appliedVoucherListInfo)
    {
        $productTaxReductionPercentage = 0;
        $productPriceReductionPercentage = 0;
        if (array_key_exists('order', $appliedVoucherListInfo)) {
            foreach ($appliedVoucherListInfo['order'] as $value) {
                $productTaxReductionPercentage += $value['reduction_tax'];
                $productPriceReductionPercentage += $value['reduction_percent'];
            }
        }

        if (array_key_exists($cartProductId, $appliedVoucherListInfo)) {
            if (array_key_exists('all_attr', $appliedVoucherListInfo[$cartProductId])) {
                foreach ($appliedVoucherListInfo[$cartProductId]['all_attr'] as $value) {
                    $productTaxReductionPercentage += $value['value'];
                    $productPriceReductionPercentage += $value['value'];
                }
            }

            if (array_key_exists($cartProductIdAttribute, $appliedVoucherListInfo[$cartProductId])) {
                foreach ($appliedVoucherListInfo[$cartProductId][$cartProductIdAttribute] as $value) {
                    $productTaxReductionPercentage += $value['value'];
                    $productPriceReductionPercentage += $value['value'];
                }
            }
        }
        return array('price' => $productPriceReductionPercentage, 'tax' => $productTaxReductionPercentage);
    }

    public function calculateReductionPercentageForShowingVoucher($cartProductId, $cartProductIdAttribute, $appliedVoucherListInfo, $mpProductPriceTI, $idOrder, $idSeller, $showVoucherDetails)
    {
        if (array_key_exists('order', $appliedVoucherListInfo)) {
            foreach ($appliedVoucherListInfo['order'] as $value) {
                if (array_key_exists($idSeller, $showVoucherDetails) && array_key_exists($value['ps_id_cart_rule'], $showVoucherDetails[$idSeller])) {
                    $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] += (float) Tools::ps_round((float) (($mpProductPriceTI * $value['reduction_percent']) / 100), 2);
                } else {
                    $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] = (float) Tools::ps_round((float) (($mpProductPriceTI * $value['reduction_percent']) / 100), 2);
                    $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['id_order'] = $idOrder;
                    $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_name'] = $value['voucher_name'];
                }
            }
        }

        if (array_key_exists($cartProductId, $appliedVoucherListInfo)) {
            if (array_key_exists('all_attr', $appliedVoucherListInfo[$cartProductId])) {
                foreach ($appliedVoucherListInfo[$cartProductId]['all_attr'] as $value) {
                    if (array_key_exists($idSeller, $showVoucherDetails) && array_key_exists($value['ps_id_cart_rule'], $showVoucherDetails[$idSeller])) {
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] += (float) Tools::ps_round((float) (($mpProductPriceTI * $value['value']) / 100), 2);
                    } else {
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] = (float) Tools::ps_round((float) (($mpProductPriceTI * $value['value']) / 100), 2);
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['id_order'] = $idOrder;
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_name'] = $value['voucher_name'];
                    }
                }
            }

            if (array_key_exists($cartProductIdAttribute, $appliedVoucherListInfo[$cartProductId])) {
                foreach ($appliedVoucherListInfo[$cartProductId][$cartProductIdAttribute] as $value) {
                    if (array_key_exists($idSeller, $showVoucherDetails) && array_key_exists($value['ps_id_cart_rule'], $showVoucherDetails[$idSeller])) {
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] += (float) Tools::ps_round((float) (($mpProductPriceTI * $value['value']) / 100), 2);
                    } else {
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_value'] = (float) Tools::ps_round((float) (($mpProductPriceTI * $value['value']) / 100), 2);
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['id_order'] = $idOrder;
                        $showVoucherDetails[$idSeller][$value['ps_id_cart_rule']]['voucher_name'] = $value['voucher_name'];
                    }
                }
            }
        }
        return $showVoucherDetails;
    }

    public function getCartProductPriceByIdProduct($cartProducts, $productId)
    {
        $result = array();
        $result['price_ti'] = 0;
        $result['price_te'] = 0;
        foreach ($cartProducts as $product) {
            if ($product['id_product'] == $productId) {
                $result['price_ti'] = $product['price_wt'];
                $result['price_te'] = $product['price'];
            }
        }

        return $result;
    }

    public function getCartProductPriceByIdProductAndIdAttribute($cartProducts, $cartProductId, $cartProductIdAttribute)
    {
        $result = array();
        $result['price_ti'] = 0;
        $result['price_te'] = 0;
        foreach ($cartProducts as $product) {
            if (($product['id_product'] == $cartProductId) && ($product['id_product_attribute'] == $cartProductIdAttribute)) {
                $result['price_ti'] = $product['price_wt'];
                $result['price_te'] = $product['price'];
            }
        }

        return $result;
    }

    public function getOrderTotalWithOutGiftProductPrice($cartProducts, $giftProductListInfo)
    {
        $orderTotalAmount = array();
        $orderTotalAmount['te'] = 0;
        $orderTotalAmount['ti'] = 0;
        foreach ($cartProducts as $cartProduct) {
            if (array_key_exists($cartProduct['id_product'], $giftProductListInfo)) {
                if (array_key_exists($cartProduct['id_product_attribute'], $giftProductListInfo[$cartProduct['id_product']])) {
                    if ($giftProductListInfo[$cartProduct['id_product']][$cartProduct['id_product_attribute']] < $cartProduct['cart_quantity']) {
                        $cart_quantity = $cartProduct['cart_quantity'] - $giftProductListInfo[$cartProduct['id_product']][$cartProduct['id_product_attribute']];
                        $orderTotalAmount['ti'] += ($cartProduct['price_wt'] * $cart_quantity);
                        $orderTotalAmount['te'] += ($cartProduct['price'] * $cart_quantity);
                    }
                } else {
                    $orderTotalAmount['ti'] += ($cartProduct['price_wt'] * $cartProduct['cart_quantity']);
                    $orderTotalAmount['te'] += ($cartProduct['price'] * $cartProduct['cart_quantity']);
                }
            } else {
                $orderTotalAmount['ti'] += ($cartProduct['price_wt'] * $cartProduct['cart_quantity']);
                $orderTotalAmount['te'] += ($cartProduct['price'] * $cartProduct['cart_quantity']);
            }
        }
        return $orderTotalAmount;
    }

    public function settleSellerAmount(
        $idSeller,
        $sellerAmount,
        $idCurrency,
        $seller_receive = true,
        $paymentMethod = 'Manual',
        $transactionType = 'order',
        $remark = false,
        $idTransaction = false
    ) {
        $sellerInfo = new WkMpSeller($idSeller);
        $idCustomerSeller = $sellerInfo->seller_customer_id;
        $sellerPaymentTransaction = new WkMpSellerTransactionHistory();

        $sellerPaymentTransaction->id_customer_seller = $idCustomerSeller;
        $sellerPaymentTransaction->id_currency = $idCurrency;

        if ($seller_receive) { // Admin settling amount to seller
            $sellerPaymentTransaction->seller_receive = (float) $sellerAmount;
        } else { // Reverting settling amount from seller
            $sellerPaymentTransaction->seller_amount = (float) $sellerAmount;
        }
        if ($transactionType == '1') {
            $transactionType = 'settlement';
        }
        if ($remark == '1') {
            $remark = 'Paid to seller';
        }
        $sellerPaymentTransaction->payment_method = $paymentMethod;
        $sellerPaymentTransaction->transaction_type = $transactionType;
        $sellerPaymentTransaction->id_transaction = $idTransaction;
        $sellerPaymentTransaction->remark = $remark;
        $sellerPaymentTransaction->status = 1;
        if ($sellerPaymentTransaction->save()) {
            return $sellerPaymentTransaction->id;
        }
        return false;
    }
}
