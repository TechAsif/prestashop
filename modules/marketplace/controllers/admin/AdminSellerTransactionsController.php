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

class AdminSellerTransactionsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->identifier = 'id_seller_transaction_history';
        parent::__construct();

        $this->bootstrap = true;
        $this->list_no_link = true;

        $this->table = 'wk_mp_seller_transaction_history';
        $this->className = 'WkMpSellerTransactionHistory';
        $this->_orderBy = 'id_seller_transaction_history';
        if (!Tools::getValue('mp_seller_transaction') && !Tools::getValue('mp_transaction_detail')) {
            $this->_select = '
                count(*) as count_values,
                so.`seller_shop` as unique_shop,
                so.`seller_id` as id_seller,
                CONCAT(so.`seller_firstname`," ",so.`seller_lastname`) as seller_name,
                so.`seller_email` as email';
            $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_order` so ON (a.`id_customer_seller` = so.`seller_customer_id`)';
            $this->_group = 'GROUP BY so.`seller_customer_id`';
            $this->toolbar_title = $this->l('Manage Seller Transaction');

            $this->fields_list = array(
                'id_seller' => array(
                    'title' => $this->l('ID Seller'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'havingFilter' => true,
                ),
                'unique_shop' => array(
                    'title' => $this->l('Unique Shop Name'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'seller_name' => array(
                    'title' => $this->l('Seller Name'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'email' => array(
                    'title' => $this->l('Seller Email'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'count_values' => array(
                    'title' => $this->l('Total Transaction'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'orderby' => false,
                    'search' => false,
                    'badge_success' => true,
                    'callback' => 'checkTransactionTotal'
                ),
            );

            $this->fields_list['id_customer_seller'] = array(
                'title' => $this->l('Details'),
                'align' => 'center',
                'search' => false,
                'hint' => $this->l('View Seller Settlement/Payment Transaction Details'),
                'callback' => 'viewSettlementBtn',
            );
        }
        $this->_conf['1'] = $this->l('Seller Amount Settled Successfully');
        $this->_conf['2'] = $this->l('Seller Settled Amount Cancelled Successfully');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function checkTransactionTotal($val, $arr)
    {
        if ($val) {
            return WkMpSellerTransactionHistory::getTotalTransaction($arr['id_customer_seller']);
        }
    }

    public function viewSettlementBtn($id)
    {
        if ($id) {
            $html = '<span class="btn-group-action">
                        <span class="btn-group">
                            <a class="btn btn-default" href="'.
                            self::$currentIndex.'&token='.$this->token.'&view'.
                            $this->table.'&mp_seller_transaction=1&id_customer_seller='.
                            $id.'"><i class="icon-search-plus"></i>&nbsp;'.$this->l('View Transaction').'
                            </a>
                        </span>
                    </span>';

            return $html;
        }
    }

    public function displayViewDetailLink($token = null, $id, $name = null)
    {
        $objTransactionHistory = new WkMpSellerTransactionHistory($id);
        if ($objTransactionHistory->id_customer_seller) {
            $url = self::$currentIndex.'&token='.$this->token;
            $class = $data = '';

            if ($objTransactionHistory->transaction_type == WkMpSellerTransactionHistory::MP_SELLER_ORDER
                || $objTransactionHistory->transaction_type == WkMpSellerTransactionHistory::MP_ORDER_CANCEL
                || $objTransactionHistory->transaction_type == WkMpSellerTransactionHistory::MP_ORDER_REFUND
            ) {
                $class = 'wk_view_detail';
                $data = 'data-id-order= '.(int) $objTransactionHistory->id_transaction;
                $url .= '&view_transaction_detail=1&id_transaction='.$objTransactionHistory->id_transaction;
            } elseif ($objTransactionHistory->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT
                || $objTransactionHistory->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL
            ) {
                $class = 'wk_view_transaction_detail';
                $data = 'data-id-transaction= '.(int) $objTransactionHistory->id;
                $url .= '&view_transaction_detail=1&id_transaction='.$objTransactionHistory->id_transaction;
            }
            $this->context->smarty->assign(array(
                'class' => $class,
                'data' => $data,
                'url' => $url,
                'id_customer_seller' => Tools::getValue('id_customer_seller')
            ));
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.'marketplace/views/templates/admin/mp_transaction_view.tpl'
            );
        }
    }

    public function renderList()
    {
        $orderTotal = WkMpSellerTransactionHistory::getSellerOrderTotalByIdCustomer();
        if ($orderTotal) {
            WkMpSellerTransactionHistory::assignSellerTransactionTotal($orderTotal);
        }

        return parent::renderList();
    }

    public function initSellerTransaction()
    {
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        $this->type_array = array(
            WkMpSellerTransactionHistory::MP_SELLER_ORDER => 'Orders',
            WkMpSellerTransactionHistory::MP_ORDER_CANCEL => 'Order Cancelled',
            WkMpSellerTransactionHistory::MP_ORDER_REFUND => 'Order Refunded',
            WkMpSellerTransactionHistory::MP_SETTLEMENT => 'Settlement',
            WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL => 'Settlement Cancelled'
        );
        $this->fields_list = array(
            'transaction_type' => array(
                'title' => $this->l('Transaction Type'),
                'align' => 'center',
                'type' => 'select',
                'list' => $this->type_array,
                'filter_key' => 'a!transaction_type',
                'order_key' => 'transaction_type',
                'callback' => 'checkPaymentType'
            ),
            'id_transaction' => array(
                'title' => $this->l('Transaction ID'),
                'align' => 'center',
                'callback' => 'checkTransactionID',
                'class' => 'wkCustomRow'
            ),
            'seller_amount' => array(
                'title' => $this->l('Seller Amount'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'checkTransactionType',
                'class' => 'wkCustomRow',
            ),
            'seller_tax' => array(
                'title' => $this->l('Seller Tax'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'admin_commission' => array(
                'title' => $this->l('Admin Commission'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'admin_tax' => array(
                'title' => $this->l('Admin Tax'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'seller_shipping' => array(
                'title' => $this->l('Seller Shipping'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setSellerShippingEarning',
                'class' => 'wkCustomRow',
            ),
            'payment_method' => array(
                'title' => $this->l('Payment Mode'),
                'align' => 'center',
                'callback' => 'checkFieldValue',
                'class' => 'wkCustomRow',
            ),
            'remark' => array(
                'title' => $this->l('Remark'),
                'align' => 'center',
                'maxlength' => 100,
                'callback' => 'checkFieldValue',
                'class' => 'wkCustomRow',
            ),
            'transaction_add_date' => array(
                'title' => $this->l('Transaction Date'),
                'type' => 'datetime',
                'class' => 'wkCustomRow',
                'havingFilter' => true
            ),
        );

        $this->addRowAction('ViewDetail');
        self::$currentIndex = self::$currentIndex.'&token='.$this->token.'&view'.
        $this->table.'&id_customer_seller='.(int) $idCustomerSeller.'&mp_seller_transaction=1';
        $this->context->smarty->assign(array(
            'current' => self::$currentIndex,
        ));
    }

    public function initAllSellerTransaction()
    {
        $this->type_array = array(
            WkMpSellerTransactionHistory::MP_SELLER_ORDER => 'Orders',
            WkMpSellerTransactionHistory::MP_ORDER_CANCEL => 'Order Cancelled',
            WkMpSellerTransactionHistory::MP_ORDER_REFUND => 'Order Refunded',
            WkMpSellerTransactionHistory::MP_SETTLEMENT => 'Settlement',
            WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL => 'Settlement Cancelled'
        );

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $this->fields_list = array(
            'transaction_type' => array(
                'title' => $this->l('Transaction Type'),
                'align' => 'center',
                'type' => 'select',
                'list' => $this->type_array,
                'filter_key' => 'a!transaction_type',
                'order_key' => 'transaction_type',
                'callback' => 'checkPaymentType'
            ),
            'id_transaction' => array(
                'title' => $this->l('Transaction ID'),
                'align' => 'center',
                'callback' => 'checkTransactionID',
                'class' => 'wkCustomRow'
            ),
            'seller_amount' => array(
                'title' => $this->l('Seller Amount'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'checkTransactionType',
                'class' => 'wkCustomRow',
            ),
            'seller_tax' => array(
                'title' => $this->l('Seller Tax'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'admin_commission' => array(
                'title' => $this->l('Admin Commission'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'admin_tax' => array(
                'title' => $this->l('Admin Tax'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setOrderCurrency',
                'class' => 'wkCustomRow',
            ),
            'seller_shipping' => array(
                'title' => $this->l('Seller Shipping'),
                'align' => 'center',
                'type' => 'price',
                'callback' => 'setSellerShippingEarning',
                'class' => 'wkCustomRow',
            ),
            'payment_method' => array(
                'title' => $this->l('Payment Mode'),
                'align' => 'center',
                'callback' => 'checkFieldValue',
                'class' => 'wkCustomRow',
            ),
            'remark' => array(
                'title' => $this->l('Remark'),
                'align' => 'center',
                'maxlength' => 100,
                'callback' => 'checkFieldValue',
                'class' => 'wkCustomRow',
            ),
            'date_add' => array(
                'title' => $this->l('Transaction Date'),
                'type' => 'datetime',
                'class' => 'wkCustomRow',
            ),
        );
        $this->addRowAction('ViewDetail');
        self::$currentIndex = self::$currentIndex.'&token='.$this->token.'&view'.$this->table.'&id_currency='.(int) Tools::getValue('id_currency').'&mp_transaction_detail=1&all=1';
        $this->context->smarty->assign(array(
            'current' => self::$currentIndex,
        ));
    }

    public function setSellerShippingEarning($val, $arr)
    {
        if ($arr['transaction_type'] == WkMpSellerTransactionHistory::MP_SELLER_ORDER) {
            if (Tools::getValue('id_customer_seller')) {
                //Specific seller transaction
                $sellerShippingEarning = WkMpAdminShipping::getSellerShippingByIdOrder(
                    (int) $arr['id_transaction'],
                    (int) $arr['id_customer_seller']
                );
            } else {
                //For all transactions
                $sellerShippingEarning = Db::getInstance()->getValue(
                    'SELECT `seller_earn` FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping`
                    WHERE `order_id` = ' . (int) $arr['id_transaction']
                );
            }

            if (!$sellerShippingEarning) {
                $sellerShippingEarning = 0;
            }
            return Tools::displayPrice($sellerShippingEarning, (int) $arr['id_currency']);
        }

        return '-';
    }

    public function renderView()
    {
        $this->context->smarty->assign('noListHeader', 1);

        // unset the filter if first renderlist contain any filteration
        if (!Tools::isSubmit('wk_mp_seller_transaction_historyOrderway')) {
            unset($this->context->cookie->sellertransactionswk_mp_seller_transaction_historyOrderby);
            unset($this->context->cookie->sellertransactionswk_mp_seller_transaction_historyOrderway);
        }

        $idCustomerSeller = Tools::getValue('id_customer_seller');
        if ($idCustomerSeller) {
            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomerSeller);
            $orderTotal = WkMpSellerTransactionHistory::getSellerOrderTotalByIdCustomer($idCustomerSeller);
            if ($orderTotal) {
                WkMpSellerTransactionHistory::assignSellerTransactionTotal($orderTotal, $idCustomerSeller);
                $this->context->smarty->assign(
                    array(
                        'seller' => $seller,
                        'settlement' => 1,
                    )
                );
            }
            if (Tools::getValue('mp_seller_transaction') && !Tools::getValue('view_transaction_detail')) {
                $objSellerPayment = new WkMpCustomerPayment();
                $paymentModeDetail = $objSellerPayment->getPaymentDetailByIdCustomer($idCustomerSeller);
                if ($paymentModeDetail) {
                    if ($paymentModeDetail['payment_mode']) {
                        $this->context->smarty->assign(array(
                            'payment_mode' => $paymentModeDetail['payment_mode']
                        ));
                    }
                    if ($paymentModeDetail['payment_detail']) {
                        $this->context->smarty->assign(array(
                            'payment_mode_details' => $paymentModeDetail['payment_detail']
                        ));
                    }
                }

                $this->toolbar_title = $this->l('Seller Transaction History');
                $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_order` so ON (a.`id_customer_seller` = so.`seller_customer_id`)';
                $this->_select = '
                    a.`date_add` as transaction_add_date,
                    sum(a.`seller_amount`) as seller_amount,
                    sum(a.`seller_tax`) as seller_tax,
                    sum(a.`admin_commission`) as admin_commission,
                    sum(a.`admin_tax`) as admin_tax,
                    a.`id_customer_seller` as temp_id_customer_seller,
                    CONCAT(so.`seller_firstname`, \' \', so.`seller_lastname`) `name`';

                $this->_where = ' AND a.`id_customer_seller` = '.(int) $idCustomerSeller;
                $this->_group = 'GROUP BY a.`id_transaction`';
                $this->_orderBy = 'date_add';
                $this->_orderWay = 'DESC';

                $this->initSellerTransaction();

                $this->actions = array();
                $this->actions[0] = 'viewDetail';

                return parent::renderList();
            }
        } elseif (Tools::getValue('mp_transaction_detail') && Tools::getValue('all')) {
            $orderTotal = WkMpSellerTransactionHistory::getSellerOrderTotalByIdCustomer(false, (int)Tools::getValue('id_currency'));
            if ($orderTotal) {
                WkMpSellerTransactionHistory::assignSellerTransactionTotal($orderTotal);
            }
            $this->context->smarty->assign(array(
                'allTransaction' => 1
            ));
            $this->toolbar_title = $this->l('All Seller Transactions');
            $this->_select = '
                    sum(a.`seller_amount`) as seller_amount,
                    sum(a.`seller_tax`) as seller_tax,
                    sum(a.`admin_commission`) as admin_commission,
                    sum(a.`admin_tax`) as admin_tax';
            $this->_where = ' AND a.`id_currency` = '.(int) Tools::getValue('id_currency');
            $this->_group = 'GROUP BY a.`id_transaction`';
            $this->_orderBy = 'date_add';
            $this->_orderWay = 'DESC';

            $this->initAllSellerTransaction();
            return parent::renderList();
        }
    }

    public function processFilter()
    {
        parent::processFilter();

        $prefix = $this->getCookieFilterPrefix();

        if (isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '') {
                    unset($this->context->cookie->{$prefix.$key});
                } elseif (stripos($key, $this->list_id.'Filter_') === 0) {
                    $this->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id.'Filter_') === 0) {
                    $this->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
                if (stripos($key, $this->list_id.'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix.$key});
                    } else {
                        $this->context->cookie->{$prefix.$key} = $value;
                    }
                } elseif (stripos($key, $this->list_id.'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $this->_defaultOrderWay) {
                        unset($this->context->cookie->{$prefix.$key});
                    } else {
                        $this->context->cookie->{$prefix.$key} = $value;
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix.$this->list_id.'Filter_');
        $definition = false;
        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix.$this->list_id.'Filter_', 7 + Tools::strlen($prefix.$this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix.$this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = Tools::unSerialize($value);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0].'.`'.$tmp_tab[1].'`' : '`'.$tmp_tab[0].'`';

                    // Assignment by reference
                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sql_filter = & $this->_tmpTableFilter;
                    } elseif (array_key_exists('havingFilter', $field)) {
                        $sql_filter = & $this->_filterHaving;
                    } else {
                        $sql_filter = & $this->_filter;
                    }

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND '.pSQL($key).' >= \''.pSQL(Tools::dateFrom($value[0])).'\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND '.pSQL($key).' <= \''.pSQL(Tools::dateTo($value[1])).'\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`'.$this->identifier.'`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ?  $alias.'.' : '').pSQL($key).' = '.(int)$value.' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = '.(float)$value.' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = \''.pSQL($value).'\' ';
                        } elseif ($type == 'price') {
                            $value = (float)str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = '.pSQL(trim($value)).' ';
                            if ($key == '`seller_amount`') {
                                $sql_filter = 'AND `seller_amount` = '.$value.' OR `seller_receive` = '.$value.' OR `seller_receive` = '.'-'.$value;
                            }
                        } else {
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL(trim($value)).'%\' ';
                        }
                    }
                }
            }
        }
    }

    protected function filterToField($key, $filter)
    {
        if (Tools::getValue('mp_seller_transaction')) {
            $this->initSellerTransaction();
        } elseif (Tools::getValue('mp_transaction_detail')) {
            $this->initAllSellerTransaction();
        }

        return parent::filterToField($key, $filter);
    }

    public function checkFieldValue($val)
    {
        if (!$val) {
            return $this->l('N/A');
        }
        return $val;
    }

    public function checkTransactionID($val, $arr)
    {
        if ($arr['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT) {
            $val = explode('#', $val);
            if (isset($val[1])) {
                if (!$val[1]) {
                    return $this->l('N/A');
                } else {
                    return $val[1];
                }
            } else {
                return $val[0];
            }
        } elseif ($arr['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL) {
            return $this->l('N/A');
        } else {
            if (!$val) {
                return $this->l('N/A');
            }
        }

        return $val;
    }

    public function checkPaymentType($val, $arr)
    {
        if ($val == WkMpSellerTransactionHistory::MP_SELLER_ORDER) {
            $order = new Order($arr['id_transaction']);
            return $this->l('Prestashop Order').'<br>'.'('.$this->l('Ref: ').$order->reference.')';
        } elseif ($val == WkMpSellerTransactionHistory::MP_ORDER_CANCEL) {
            return $this->l('Order Cancelled');
        } elseif ($val == WkMpSellerTransactionHistory::MP_ORDER_REFUND) {
            return $this->l('Order Refunded');
        } elseif ($val == WkMpSellerTransactionHistory::MP_SETTLEMENT) {
            $idTransaction = $this->checkTransactionID($arr['id_transaction'], $arr);
            return $this->l('Seller Settlement').'<br>'.'('.$this->l('Ref: ').$idTransaction.')';
        } elseif ($val == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL) {
            $val = explode('#', $arr['id_transaction']);
            if (isset($val[1])) {
                if (!$val[1]) {
                    $id_transaction = $this->l('N/A');
                } else {
                    $id_transaction = $val[1];
                }
            } else {
                $id_transaction = $val[0];
            }
            return $this->l('Settlement Cancelled').'<br>'.'('.$this->l('Ref: ').$id_transaction.')';
        } else {
            return '--';
        }
    }

    public static function setOrderCurrency($val, $arr)
    {
        return Tools::displayPrice($val, (int) $arr['id_currency']);
    }

    public function checkTransactionType($val, $arr)
    {
        if ($arr['seller_amount'] > 0) {
            $amount = Tools::displayPrice($arr['seller_amount'], (int) $arr['id_currency']);
            if ($arr['seller_refunded_amount'] > 0) {
                return '<span class="badge badge-danger">'.$amount.' '.$this->l('(Dr)').'</span>';
            } else {
                return '<span class="badge badge-success">'.$amount.' '.$this->l('(Cr)').'</span>';
            }
        } elseif ($arr['seller_receive'] > 0) {
            $amount = Tools::displayPrice($arr['seller_receive'], (int) $arr['id_currency']);
            return '<span class="badge badge-danger">'.$amount.' '.$this->l('(Dr)').'</span>';
        } else {
            $amount = Tools::displayPrice($arr['seller_amount'], (int) $arr['id_currency']);
            return '<span class="badge badge-success">'.$amount.' '.$this->l('(Cr)').'</span>';
        }
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        $nb_items = count($this->_list);
        for ($i = 0; $i < $nb_items; ++$i) {
            $item = &$this->_list[$i];
            $item['badge_success'] = true;
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitReset'.$this->table)) {
            $this->processResetFilters();
        }

        if (Tools::isSubmit('submitFilter')) {
            $this->processFilter();
        }
        // Settlement seller amount
        if (Tools::isSubmit('submit_payment')) {
            $amount = Tools::getValue('amount');
            $idCustomerSeller = Tools::getValue('id_customer_seller');
            $idCurrency = Tools::getValue('id_currency');
            $sellerDue = 0;
            $wkMpPaymentMethod = trim(Tools::getValue('wk_mp_payment_method'));
            $wkMpTransactionID = trim(Tools::getValue('wk_mp_transaction_id'));
            $wkMpRemark = trim(Tools::getValue('wk_mp_remark'));

            $sellerTotal = WkMpSellerTransactionHistory::getSellerOrderTotalByIdCustomer($idCustomerSeller, $idCurrency);
            $sellerShippingInfo = WkMpAdminShipping::getTotalSellerShipping($idCustomerSeller, $idCurrency);
            if (!$sellerShippingInfo) {
                $sellerShippingInfo = array(
                    'seller_shipping' => '0',
                    'id_currency' => $idCurrency
                );
            }

            if (!empty($sellerTotal)) {
                $sellerDue = $sellerTotal[0]['seller_total_earned'] - $sellerTotal[0]['seller_receive'];
            }

            if (isset($sellerShippingInfo['seller_shipping'])) {
                $sellerDue += $sellerShippingInfo['seller_shipping'];
            }

            if ($sellerDue) {
                if (!$amount) {
                    $this->errors[] = $this->l('Amount can not be empty');
                } elseif ($amount <= 0) {
                    $this->errors[] = $this->l('Amount must be greater than zero');
                } elseif (!Validate::isFloat($amount)) {
                    $this->errors[] = $this->l('Amount is not valid');
                } elseif ($amount > $sellerDue) {
                    $this->errors[] = $this->l('Amount can not be greater than total due');
                }

                $objSellerPayment = new WkMpCustomerPayment();
                $paymentModeDetail = $objSellerPayment->getPaymentDetailByIdCustomer($idCustomerSeller);

                if ($wkMpPaymentMethod) {
                    if (!Validate::isGenericName($wkMpPaymentMethod)) {
                        $this->errors[] = $this->l('Payment method is not valid.');
                    }
                } elseif ($paymentModeDetail) {
                    $wkMpPaymentMethod = $paymentModeDetail['payment_mode'];
                }

                if ($wkMpTransactionID) {
                    if (!Validate::isAnything($wkMpTransactionID)) {
                        $this->errors[] = $this->l('Transaction is not valid.');
                    }
                } else {
                    $wkMpTransactionID = 'N/A';
                }

                if ($wkMpRemark) {
                    if (!Validate::isGenericName($wkMpRemark)) {
                        $this->errors[] = $this->l('Remark is not valid.');
                    }
                }

                $lastRowValue = WkMpSellerTransactionHistory::getlastRowValue();
                if (!$lastRowValue) {
                    $lastRowValue = '1#';
                } else {
                    $lastRowValue++;
                }
                $wkMpTransactionID = $lastRowValue.'#'.$wkMpTransactionID;
                $sellerDetail = WkMpSeller::getSellerDetailByCustomerId($idCustomerSeller);
                if (empty($this->errors)) {
                    $sellerSplit = new WkMpSellerPaymentSplit();
                    if ($sellerSplit->settleSellerAmount(
                        $sellerDetail['id_seller'],
                        $amount,
                        $idCurrency,
                        true,
                        $wkMpPaymentMethod,
                        WkMpSellerTransactionHistory::MP_SETTLEMENT,    // Settling amount to seller
                        $wkMpRemark,
                        $wkMpTransactionID
                    )) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.$this->token.'&view'.$this->table.'&mp_seller_transaction=1&id_customer_seller='.(int) $idCustomerSeller);
                    } else {
                        $this->errors[] = $this->l('Something went wrong!');
                    }
                }
            } else {
                $this->errors[] = $this->l('Currently No due amount running for this seller');
            }
        }

        // Cancel settlled seller amount
        if (Tools::isSubmit('wk_settlement_cancel')) {
            if ($idTransaction = Tools::getValue('wk_id_settlement')) {
                $objTransactionHistory = new WkMpSellerTransactionHistory($idTransaction);
                Hook::exec(
                    'actionBeforeCancelSellerTransaction',
                    array(
                        'id_seller_transaction_history' => $objTransactionHistory->id,
                        'id_seller_customer' => $objTransactionHistory->id_customer_seller
                    )
                );
                if (empty($this->errors)) {
                    if ($objTransactionHistory->status == 2) {
                        $this->errors[] = $this->l('Transaction has already cancelled');
                    } else {
                        $objTransactionHistory->status = 2;
                        $objTransactionHistory->update();
                        $wkMpRemark = $this->l('Revert seller settlement amount');
                        $sellerDetail = WkMpSeller::getSellerDetailByCustomerId($objTransactionHistory->id_customer_seller);
                        $sellerSplit = new WkMpSellerPaymentSplit();
                        if ($sellerSplit->settleSellerAmount(
                            $sellerDetail['id_seller'],
                            $objTransactionHistory->seller_receive,
                            $objTransactionHistory->id_currency,
                            false,
                            'N/A',
                            WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL, // Cancelling seller settlement amount
                            $wkMpRemark,
                            $this->l('Ref:').$objTransactionHistory->id_transaction
                        )) {
                            Hook::exec(
                                'actionAfterCancelSellerTransaction',
                                array(
                                    'id_seller_transaction_history' => $objTransactionHistory->id,
                                    'id_seller_customer' => $objTransactionHistory->id_customer_seller
                                )
                            );
                            Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token.'&view'.$this->table.'&mp_seller_transaction=1&id_customer_seller='.(int) $objTransactionHistory->id_customer_seller);
                        } else {
                            $this->errors[] = $this->l('Something went wrong!');
                        }
                    }
                }
            } else {
                $this->errors[] = $this->l('Something went wrong!');
            }
        }

        Media::addJsDef(array(
            'current_url' => $this->context->link->getAdminLink('AdminSellerTransactions')
        ));
        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_MODULE_DIR_.'marketplace/views/css/seller_payment.css');
        $this->addJS(_MODULE_DIR_.'marketplace/views/js/sellertransaction.js');
    }

    public function ajaxProcessOrderDetail()
    {
        $output = false;
        $idOrder = Tools::getValue('id_order');
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        $orderDetail = new WkMpSellerOrderDetail();
        $result = $orderDetail->getSellerProductFromOrder($idOrder, $idCustomerSeller);
        if ($idCustomerSeller) {
            $result = $orderDetail->getSellerProductFromOrder($idOrder, $idCustomerSeller);
        } else {
            $result = $orderDetail->getAllSellerProductFromOrder($idOrder);
        }
        if ($result) {
            foreach ($result as $key => &$data) {
                $mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($data['product_id']);
                $result[$key]['seller_amount'] = Tools::displayPrice($data['seller_amount'], new Currency($data['id_currency']));
                $result[$key]['seller_tax'] = Tools::displayPrice($data['seller_tax'], new Currency($data['id_currency']));
                $result[$key]['admin_commission'] = Tools::displayPrice($data['admin_commission'], new Currency($data['id_currency']));
                $result[$key]['admin_tax'] = Tools::displayPrice($data['admin_tax'], new Currency($data['id_currency']));
                $result[$key]['price_ti'] = Tools::displayPrice($data['price_ti'], new Currency($data['id_currency']));
                $result[$key]['product_link'] = $this->context->link->getAdminLink('AdminSellerProductDetail').'&updatewk_mp_seller_product&id_mp_product='.(int) $mpProduct['id_mp_product'];
            }
            $this->context->smarty->assign(array(
                'result' => $result,
                'orderInfo' => $orderDetail->getSellerOrderDetail((int) $idOrder),
                'orderlink' => $this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.(int) $idOrder.'#start_products',
            ));
            $output = $this->context->smarty->fetch(
                _PS_MODULE_DIR_.'marketplace/views/templates/admin/seller-product-line.tpl'
            );
        }
        die($output);
    }

    public function ajaxProcessTransactionDetail()
    {
        $output = false;
        $idTransaction = Tools::getValue('id_transaction');
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        if ($idCustomerSeller && $idTransaction) {
            $objTransaction = new WkMpSellerTransactionHistory($idTransaction);
            if (Validate::isLoadedObject($objTransaction)) {
                if ($objTransaction->seller_receive > 0) {
                    $amount = -($objTransaction->seller_receive);
                } elseif ($objTransaction->seller_amount > 0) {
                    $amount = $objTransaction->seller_amount;
                } else {
                    $amount = $objTransaction->seller_amount;
                }

                if ($objTransaction->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT
                    || $objTransaction->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL
                ) {
                    $idTransaction = $this->checkTransactionID(
                        $objTransaction->id_transaction,
                        array(
                            'transaction_type' => WkMpSellerTransactionHistory::MP_SETTLEMENT
                        )
                    );
                    $objTransaction->id_transaction = $idTransaction;
                }

                $this->context->smarty->assign(
                    array(
                        'objTransaction' => $objTransaction,
                        'payment_mode_details' => '',
                        'amount' => Tools::displayPrice($amount, new Currency($objTransaction->id_currency)),
                        'transactionDetail' => (array) $objTransaction
                    )
                );
                $objSellerPayment = new WkMpCustomerPayment();
                $paymentModeDetail = $objSellerPayment->getPaymentDetailByIdCustomer($objTransaction->id_customer_seller);
                if ($paymentModeDetail && $paymentModeDetail['payment_detail']) {
                    $this->context->smarty->assign(
                        array(
                            'payment_mode_details' => $paymentModeDetail['payment_detail']
                        )
                    );
                }
                $output = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.'marketplace/views/templates/admin/seller-transaction-view.tpl'
                );
            }
        }
        die($output);
    }
}
