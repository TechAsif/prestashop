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

class AdminSellerReviewsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wk_mp_seller_review';
        $this->className = 'WkMpSellerReview';
        $this->list_id = 'id_review';
        $this->identifier = 'id_review';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Seller Reviews');

        if (!Tools::getValue('id_review')) {
            $this->_defaultOrderBy = 'id_review';
            $this->_select = 'msi.`business_email` AS seller_email,
			msi.`shop_name_unique`,
			COUNT(a.`id_seller`) AS count_seller_reviews,
			COUNT(CASE WHEN a.`active` = 0 THEN 1 ELSE NULL END) AS inactive_review,
			AVG(a.`rating`) AS avg_seller_rating';

            $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` msi ON (a.`id_seller` = msi.`id_seller`)';
            $this->_group = 'GROUP BY a.`id_seller`';
        }

        $this->fields_list = array(
            'id_review' => array(
                'title' => $this->l('Id'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'shop_name_unique' => array(
                'title' => $this->l('Shop Name'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'seller_email' => array(
                'title' => $this->l('Seller Email'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'avg_seller_rating' => array(
                'title' => $this->l('Avg. Rating'),
                'align' => 'center',
                'search' => false,
                'havingFilter' => true,
                'callback' => 'showRatingStar'
            ),
            'count_seller_reviews' => array(
                'title' => $this->l('Total Review'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'inactive_review' => array(
                'title' => $this->l('Pending Reviews'),
                'align' => 'center',
                'havingFilter' => true,
                'badge_warning' => true,
            ),
        );

        $this->bulk_actions = array(
            'delete' => array('text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
                ),
            );
        if (!Tools::getValue('id_review')) {
            $this->addRowAction('view');
        }
        $this->addRowAction('delete');
    }

    public function showRatingStar($val, $arr)
    {
        if (isset($this->display)) {
            $this->context->smarty->assign(array(
                'seller_wise_rating' => 1,
                ));
        }
        $this->context->smarty->assign(array(
            'sellerRating' => $val,
            'list' => $arr,
            'rating_start_path' => _MODULE_DIR_.$this->module->name.'/views/img/'
            ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.'marketplace/views/templates/admin/seller_rating.tpl');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);

        if ($this->_list) {
            foreach ($this->_list as &$row) {
                $row['badge_warning'] = $row['active'] < 1;
            }
        }
    }

    public function renderView()
    {
        if (($id_review = Tools::getValue('id_review')) && Tools::getIsset('viewwk_mp_seller_review') && (!Tools::getValue('view_review') || Tools::getValue('submitFiltertemp_id_review'))) {
            $obj_review = new WkMpSellerReview($id_review);
            $id_seller = $obj_review->id_seller;
            $this->_join .= 'JOIN `'._DB_PREFIX_.'wk_mp_seller` msi ON (a.`id_seller` = msi.`id_seller`)';
            $this->list_no_link = true;
            $this->_select .= 'msi.`business_email` AS seller_email,
                msi.`shop_name_unique`,
                a.`id_seller` AS count_seller_reviews,
                a.`active` AS inactive_review,
                a.`rating` AS avg_seller_rating';

            $this->_where = ' AND a.`id_seller` = '.(int) $id_seller;

            $this->table = 'wk_mp_seller_review';
            $this->className = 'WkMpSellerReview';
            $this->identifier = 'id_review';
            $this->list_id = 'temp_id_review';

            if (!Validate::isLoadedObject(new WkMpSellerReview((int) $id_review))) {
                $this->errors[] = $this->l('An error occurred while updating the status for an object.').' <b>'.$this->table.'</b> '.$this->l('(cannot load object)');

                return;
            }

            $this->fields_list = array(
                'id_review' => array(
                    'title' => $this->l('Id'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'havingFilter' => true,
                ),
                'customer_email' => array(
                    'title' => $this->l('Customer Email'),
                    'align' => 'center',
                ),
                'rating' => array(
                    'title' => $this->l('Rating'),
                    'align' => 'center',
                    'havingFilter' => true,
                    'search' => false,
                    'callback' => 'showRatingStar'
                ),
                'review' => array(
                    'title' => $this->l('Comment'),
                    'align' => 'text-left',
                    'maxlength' => 50,
                    'havingFilter' => true,
                ),
                'date_add' => array(
                    'title' => $this->l('Date'),
                    'type' => 'datetime',
                    'havingFilter' => true,
                ),
                'active' => array(
                    'title' => $this->l('Status'),
                    'active' => 'status',
                    'type' => 'bool',
                    'orderby' => false,
                    'havingFilter' => true,
                ),
            );

            self::$currentIndex = self::$currentIndex.'&id_review='.$id_review.'&viewwk_mp_seller_review';
            $this->context->smarty->assign(array(
                    'current' => self::$currentIndex,
                ));

            if (Tools::isSubmit('submitFilter')) {
                $this->processFilter();
            }

            if (Tools::isSubmit('submitResettemp_id_review')) {
                $this->processResetFilters();
            }

            return parent::renderList();
        } else {
            return parent::renderView();
        }
    }

    // Enable and disable the review by bulkaction tab
    public function reviewsStatusSet($status)
    {
        $result = true;
        if ($id_reviews_list = Tools::getValue('temp_id_reviewBox')) {
            foreach ($id_reviews_list as $reviews_list) {
                $object_review = new WkMpSellerReview($reviews_list);
                $object_review->active = $status;
                $result &= $object_review->update();
            }
        }
    }

    public function postProcess()
    {
        if (Tools::getValue('view_review') == 1 && !Tools::getValue('submitFiltertemp_id_review')) {
            $id_review = Tools::getValue('id_review');
            $obj_review = new WkMpSellerReview($id_review);
            // get seller information
            $obj_mp_seller = new WkMpSeller($obj_review->id_seller);

            // get customer information
            if ($obj_review->id_customer) {
                // if not a guest
                $obj_customer = new Customer($obj_review->id_customer);
                $customer_name = $obj_customer->firstname.' '.$obj_customer->lastname;
                $this->context->smarty->assign('customer_name', $customer_name);
            }

            $this->context->smarty->assign(
                array(
                    'review_detail' => $obj_review,
                    'obj_mp_seller' => $obj_mp_seller,
                    'module_dir' => _MODULE_DIR_,
                    'id_review' => $id_review,
                )
            );
        }

        // Bulk action operations perform
        if (Tools::isSubmit('submitBulkdeletewk_mp_seller_review')) {
            if ($id_reviews_list = Tools::getValue('temp_id_reviewBox')) {
                foreach ($id_reviews_list as $reviews_list) {
                    $delete_reviews_rec = new WkMpSellerReview($reviews_list);
                    $delete_reviews_rec->delete();
                }
            }
        } elseif (Tools::isSubmit('submitBulkenableSelectionwk_mp_seller_review')) {
            $this->reviewsStatusSet(1);
        } elseif (Tools::isSubmit('submitBulkdisableSelectionwk_mp_seller_review')) {
            $this->reviewsStatusSet(0);
        }

        if (Tools::isSubmit('temp_id_reviewOrderway')) {
            $this->processFilter();
        }

        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/libs/jquery.raty.min.js');
    }
}
