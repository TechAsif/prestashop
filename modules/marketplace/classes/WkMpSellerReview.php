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

class WkMpSellerReview extends ObjectModel
{
    public $id_seller;
    public $id_customer;
    public $customer_email;
    public $rating;
    public $review;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_mp_seller_review',
        'primary' => 'id_review',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT),
            'id_customer' => array('type' => self::TYPE_INT),
            'customer_email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128),
            'rating' => array('type' => self::TYPE_INT),
            'review' => array('type' => self::TYPE_STRING),
            'active' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function delete()
    {
        // check if delete from render list then delete all comment of this seller
        if (Tools::getValue('viewwk_mp_seller_review') === false && Tools::getValue('controller') == 'AdminSellerReviews') {
            Db::getInstance()->delete(
                'wk_mp_seller_review',
                '`id_seller` = '.(int) $this->id_seller.' AND `id_review` != '.(int) $this->id
            );
        }

        return parent::delete();
    }

    /**
     * Get Seller reviews by using Seller ID
     *
     * @param  int  $idSeller Seller ID
     * @param  boolean $active true/false
     * @return array
     */
    public static function getSellerReviewByIdSeller($idSeller, $active = true)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review`
			WHERE `id_seller` ='.(int) $idSeller.'
			AND `active` ='.((int) $active ? '1' : '0').' ORDER BY date_upd DESC'
        );
    }

    /**
     * Get Seller Reviews by using Customer id and Seller ID
     *
     * @param  int $idCustomer Customer ID
     * @param  int $mpSellerID Seller ID
     * @return array
     */
    public static function getReviewByCustomerIdAndSellerId($idCustomer, $mpSellerID)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review`
            WHERE `id_customer` = '.(int) $idCustomer.'
            AND `id_seller` = '.(int) $mpSellerID);
    }

    /**
     * Get Seller Reviews by using primary id and customer ID
     *
     * @param  int $idCustomer Customer ID
     * @param  int $id Primary ID
     * @return array
     */
    public static function getReviewByIdAndCustomerId($idCustomer, $id)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review`
            WHERE `id_customer` = '.(int) $idCustomer.'
            AND `id_review` = '.(int) $id);
    }

    /**
     * Get Seller Avg rating by using Seller ID
     *
     * @param  int $idSeller Seller ID
     * @return float
     */
    public static function getSellerAvgRating($idSeller)
    {
        $reviews = self::getSellerReviewByIdSeller($idSeller);
        if ($reviews) {
            $rating = 0;
            foreach ($reviews as $review) {
                $rating = $rating + $review['rating'];
            }

            $avgRating = $rating / count($reviews);
            $avgRating = self::getDisplayRating($avgRating);

            return (double) ($avgRating);
        }

        return false;
    }

    public static function getDisplayRating($avgRating)
    {
        //Display rating in Interger or only Float of .5 only (i.e 3, 3.5, 4, 4.5, 5)
        // if rating is 3.1, 3.2 return 3
        // if rating is 3.3, 3.4, 3.6, 3.7 return 3.5
        // if rating is 3.8, 3.9 return 4

        return Tools::ps_round($avgRating * 2) / 2;
    }

    public static function getSellerRatingSummary($mpIdSeller, $totalReview)
    {
        $sellerRatings = array();
        $sellerRatingDetail = self::getSellerNumberOfGroupByRating($mpIdSeller);
        if ($sellerRatingDetail) {
            $ratingArray = array();
            foreach ($sellerRatingDetail as $ratingDetail) {
                $ratingArray[] = $ratingDetail['rating'];
            }
            $ratingKey = 0;
            for ($level = 5; $level >= 1; $level--) {
                if (in_array($level, $ratingArray)) {
                    $levelKey = array_search($level, $ratingArray);
                    $sellerRatings[$ratingKey]['count'] = $sellerRatingDetail[$levelKey]['count'];
                    $sellerRatings[$ratingKey]['rating'] = $sellerRatingDetail[$levelKey]['rating'];
                    $sellerRatings[$ratingKey]['percent'] = Tools::ps_round(
                        (($sellerRatingDetail[$levelKey]['count']*100) / $totalReview),
                        0
                    );
                } else {
                    $sellerRatings[$ratingKey]['count'] = 0;
                    $sellerRatings[$ratingKey]['rating'] = $level;
                    $sellerRatings[$ratingKey]['percent'] = 0;
                }
                $ratingKey++;
            }
        }

        return $sellerRatings;
    }

    public static function getSellerNumberOfGroupByRating($idSeller, $active = true)
    {
        return Db::getInstance()->executeS(
            'SELECT COUNT(`id_review`) as count, `rating` FROM `'._DB_PREFIX_.'wk_mp_seller_review`
            WHERE `id_seller` = '.(int) $idSeller.'
            AND `active` = '.((int) $active ? '1' : '0').'
            GROUP BY `rating` ORDER BY `rating` DESC'
        );
    }

    public function getReviewHelpfulDetailsByReviewId($idReview)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review_likes`
            WHERE `id_review` = '.(int) $idReview
        );
    }

    /**
     * Get total number of likes(helpful) of particular review
     *
     * @param  int $idReview Review ID
     * @return array
     */
    public function getReviewTotalHelpful($idReview)
    {
        $totalLikes = Db::getInstance()->getValue(
            'SELECT COUNT(`id_review_like`) as `total_likes` FROM `'._DB_PREFIX_.'wk_mp_seller_review_likes`
            WHERE `id_review` = '.(int) $idReview.'
            AND `like` = 1'
        );

        if (empty($totalLikes)) {
            $totalLikes = 0;
        }

        return $totalLikes;
    }

    /**
     * Get total number of dislikes(not helpful) of particular review
     *
     * @param  int $idReview Review ID
     * @return array
     */
    public function getReviewTotalNotHelpful($idReview)
    {
        $totalDislikes = Db::getInstance()->getValue(
            'SELECT COUNT(`id_review_like`) as `total_likes` FROM `'._DB_PREFIX_.'wk_mp_seller_review_likes`
            WHERE `id_review` = '.(int) $idReview.'
            AND `like` = 0'
        );

        if (empty($totalDislikes)) {
            $totalDislikes = 0;
        }

        return $totalDislikes;
    }

    public function getReviewHelpfulSummary($idReview)
    {
        //Get total likes(helpful) and dislikes(not helpful) data on particular review
        $objReview = new self();
        $totalLikes = $objReview->getReviewTotalHelpful($idReview);
        $totalDislikes = $objReview->getReviewTotalNotHelpful($idReview);

        return array(
            'total_likes' => $totalLikes,
            'total_dislikes' => $totalDislikes,
        );
    }

    public function isReviewHelpfulForCustomer($idCustomer, $idReview)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review_likes`
            WHERE `id_customer` = '.(int) $idCustomer.'
            AND `id_review` = '.(int) $idReview
        );
    }

    public function setReviewHelpfulRecord($idCustomer, $idReview, $isHelpful)
    {
        return Db::getInstance()->insert(
            'wk_mp_seller_review_likes',
            array(
                'id_review' => (int) $idReview,
                'id_customer' => (int) $idCustomer,
                'like' => (int) $isHelpful,
                'date_add' => pSQL(date('Y-m-d H:i:s')),
                'date_upd' => pSQL(date('Y-m-d H:i:s')),
            )
        );
    }

    public function updateReviewHelpfulRecord($idCustomer, $idReview, $isHelpful)
    {
        return Db::getInstance()->update(
            'wk_mp_seller_review_likes',
            array(
                'like' => (int) $isHelpful,
                'date_upd' => pSQL(date('Y-m-d H:i:s')),
            ),
            'id_review ='.(int) $idReview.' AND id_customer ='.(int) $idCustomer
        );
    }

    public function deleteReviewHelpfulRecord($idCustomer, $idReview)
    {
        return Db::getInstance()->delete(
            'wk_mp_seller_review_likes',
            'id_review ='.(int) $idReview.' AND id_customer ='.(int) $idCustomer
        );
    }

    public function sortingReviewList($reviewList)
    {
        //Sort review list according to admin configuration
        usort($reviewList, function ($reviewFirst, $reviewSecond) {
            if ($reviewFirst['total_likes'] != $reviewSecond['total_likes']) {
                return $reviewFirst['total_likes'] > $reviewSecond['total_likes'] ? -1 : 1;
            } else {
                return 0;
            }
        });

        return $reviewList;
    }

    public static function getCustomerReview($idCustomer)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_review`
            WHERE `id_customer` = '.(int) $idCustomer
        );
    }

    /**
     * Get reviews according admin configuration
     * @param [int] $idSeller marketplace seller id
     * @return [array or false]               [details in array]
     */
    public function getReviewsByConfiguration($idSeller)
    {
        $allReviews = self::getSellerReviewByIdSeller($idSeller);
        if ($allReviews) {
            $objReview = new WkMpSellerReview();
            $this->context = Context::getContext();
            $this->context->smarty->assign('count_all_reviews', count($allReviews));

            //Display seller rating summary
            if ($sellerRating = WkMpSellerReview::getSellerAvgRating($idSeller)) {
                $totalReview = count($allReviews);

                //Get seller rating full summary
                $sellerRatingDetail = WkMpSellerReview::getSellerRatingSummary($idSeller, $totalReview);

                $this->context->smarty->assign(
                    array(
                        'sellerRating' => $sellerRating,
                        'sellerRatingDetail' => $sellerRatingDetail,
                        'totalReview' => $totalReview,
                    )
                );

                Media::addJsDef(array(
                    'sellerRating' => $sellerRating,
                    'totalReview' => $totalReview,
                ));
            }

            foreach ($allReviews as &$review) {
                $customer = new Customer($review['id_customer']);
                $review['customer_name'] = $customer->firstname.' '.$customer->lastname;

                //Get Customer review record - Is helpful or not
                if ($this->context->customer->id) {
                    $customerReviewDetails = $objReview->isReviewHelpfulForCustomer(
                        $this->context->customer->id,
                        $review['id_review']
                    );
                    if ($customerReviewDetails) {
                        $review['like'] = $customerReviewDetails['like'];
                    }
                }

                //Get Total likes(helpful) or dislikes (not helpful) on particular review
                $reviewDetails = $objReview->getReviewHelpfulSummary($review['id_review']);
                if ($reviewDetails) {
                    $review['total_likes'] = $reviewDetails['total_likes'];
                    $review['total_dislikes'] = $reviewDetails['total_dislikes'];
                }
            }

            //Sort review list according to admin configuration (By default it will display sort by recent review)
            if (Configuration::get('WK_MP_REVIEW_DISPLAY_SORT') == '2') { // 2 for most helpful
                $allReviews = $objReview->sortingReviewList($allReviews);
            }

            //Get number of reviews according to configuration
            if (Configuration::get('WK_MP_REVIEW_DISPLAY_COUNT')) {
                $wkReviewDisplayCount = Configuration::get('WK_MP_REVIEW_DISPLAY_COUNT');
            } else {
                // We have added 'else' condition so that if client update marketplace files over old version(V5.2.1)
                // And if didn't fill this review count then default value can be used.
                $wkReviewDisplayCount = 2;
            }
            $topTwoReviews = array_slice($allReviews, 0, $wkReviewDisplayCount);

            return array(
                'avg_rating' => $sellerRating,
                'reviews' => $topTwoReviews,
            );
        } else {
            return false;
        }
    }
}
