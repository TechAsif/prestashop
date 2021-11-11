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

class MarketplaceContactSellerProcessModuleFrontController extends ModuleFrontController
{
    // Send mail to seller when customer contact with seller
    public function displayAjaxContactSeller()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        $result = array();
        $result['status'] = 'ko';
        $result['msg'] = $this->module->l('Some error while sending message to seller.', 'contactsellerprocess');

        $customerEmail = Tools::getValue('customer_email');
        $querySubject = Tools::getValue('query_subject');
        $queryDescription = Tools::getValue('query_description');
        $idSeller = Tools::getValue('id_seller');

        if ($customerEmail == '') {
            $this->errors = $this->module->l('Email is required.', 'contactsellerprocess');
        } elseif (!Validate::isEmail($customerEmail)) {
            $this->errors = $this->module->l('Email must be valid.', 'contactsellerprocess');
        }
        if ($querySubject == '') {
            $this->errors = $this->module->l('Subject is required.', 'contactsellerprocess');
        } elseif (!Validate::isGenericName($querySubject)) {
            $this->errors = $this->module->l('Subject must be valid.', 'contactsellerprocess');
        }
        if ($queryDescription == '') {
            $this->errors = $this->module->l('Description is required.', 'contactsellerprocess');
        } elseif (!Validate::isGenericName($queryDescription)) {
            $this->errors = $this->module->l('Description must be valid.', 'contactsellerprocess');
        }

        if (empty($this->errors)) {
            $mpSeller = new WkMpSeller($idSeller);
            $sellerEmail = $mpSeller->business_email;
            if ($sellerEmail) {
                $sellerName = $mpSeller->seller_firstname.' '.$mpSeller->seller_lastname;

                $mpCustomerQuery = new WkMpSellerHelpDesk();
                $mpCustomerQuery->id_product = 0;
                if ($this->context->customer->id) {
                    $mpCustomerQuery->id_customer = $this->context->customer->id;
                } else {
                    $mpCustomerQuery->id_customer = 0;
                }
                $mpCustomerQuery->id_seller = $idSeller;
                $mpCustomerQuery->subject = $querySubject;
                $mpCustomerQuery->description = $queryDescription;
                $mpCustomerQuery->customer_email = $customerEmail;
                $mpCustomerQuery->active = 1;
                if ($mpCustomerQuery->save()) {
                    $templateVars = array(
                        '{customer_email}' => $customerEmail,
                        '{query_subject}' => $querySubject,
                        '{seller_name}' => $sellerName,
                        '{query_description}' => $queryDescription,
                    );

                    if (Mail::Send(
                        (int) $this->context->language->id,
                        'contact_seller_mail',
                        $querySubject,
                        $templateVars,
                        $sellerEmail,
                        null,
                        null,
                        null,
                        null,
                        null,
                        _PS_MODULE_DIR_.'marketplace/mails/',
                        false,
                        null,
                        null
                    )) {
                        $result['status'] = 'ok';
                        $result['msg'] = $this->module->l('Mail successfully sent.', 'contactsellerprocess');
                    } else {
                        $result['status'] = 'ko';
                        $result['msg'] = $this->module->l('Some error while sending mail', 'contactsellerprocess');
                    }
                }
            }
        } else {
            $result['msg'] = $this->errors;
        }
        die(json_encode($result)); //Ajax complete
    }

    //When customer choose that review is helpful or not
    public function displayAjaxReviewHelpful()
    {
        $result = array();
        $result['status'] = 'ko';
        $result['like'] = '-1';
        if (($idCustomer = $this->context->customer->id) && ($idReview = Tools::getValue('id_review'))) {
            $objReview = new WkMpSellerReview();
            $btnAction = Tools::getValue('btn_action');
            if ($btnAction == 1) {
                $isHelpful = 1;
                //Review is helpful(like)
                if ($reviewDetails = $objReview->isReviewHelpfulForCustomer($idCustomer, $idReview)) {
                    //if like or dislike
                    if ($reviewDetails['like']) {
                        //delete if already like
                        $objReview->deleteReviewHelpfulRecord($idCustomer, $idReview);
                    } else {
                        //update if already dislike
                        $objReview->updateReviewHelpfulRecord($idCustomer, $idReview, $isHelpful);
                        $result['like'] = '1';
                    }
                } else {
                    //if review is never liked nor disliked by this customer
                    $objReview->setReviewHelpfulRecord($idCustomer, $idReview, $isHelpful);
                    $result['like'] = '1';
                }
            } elseif ($btnAction == 2) {
                $isHelpful = 0;
                //Review is not helpful(dislike)
                if ($reviewDetails = $objReview->isReviewHelpfulForCustomer($idCustomer, $idReview)) {
                    //if like or dislike
                    if ($reviewDetails['like']) {
                        //update if already like
                        $objReview->updateReviewHelpfulRecord($idCustomer, $idReview, $isHelpful);
                        $result['like'] = '0';
                    } else {
                        //delete if already dislike
                        $objReview->deleteReviewHelpfulRecord($idCustomer, $idReview);
                    }
                } else {
                    //if review is never liked nor disliked by this customer
                    $objReview->setReviewHelpfulRecord($idCustomer, $idReview, $isHelpful);
                    $result['like'] = '0';
                }
            }
            //Get Total likes(helpful) or dislikes (not helpful) on particular review
            $reviewDetails = $objReview->getReviewHelpfulSummary($idReview);
            $result['status'] = 'ok';
            $result['data'] = $reviewDetails;
        }
        die(json_encode($result));
    }
}
