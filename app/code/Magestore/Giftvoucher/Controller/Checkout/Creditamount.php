<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Giftvoucher\Controller\Checkout;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Checkout Creditamount Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Creditamount extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        if (!$this->getHelperData()->getGeneralConfig('enablecredit')) {
            return;
        }
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $quote = $session->getQuote();
        $result = array();
        if ($quote->getCouponCode() && !$this->getHelperData()->getGeneralConfig('use_with_coupon')
            && (!$session->getUseGiftCreditAmount() || !$session->getGiftVoucherDiscount())
        ) {
            $notice= __('A coupon code has been used. You cannot apply Gift Card credit with the coupon to get discount.');
        } else {
            $amount = floatval($this->getRequest()->getParam('amount'));
            if ($amount > -0.0001 && (abs($amount - $session->getMaxCreditUsed()) > 0.0001
                || !$session->getMaxCreditUsed())
            ) {
                $session->setUseGiftCardCredit(1);
                $customerSession = $this->getCusomterSessionModel();
                $customerId = $customerSession->getCustomerId();
                $credit = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credit')->load(
                    $customerId,
                    'customer_id'
                );
                $session->setGiftcreditBalance(floatval($credit->getBalance()));
                $session->setMaxCreditUsed($amount);
                $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
                $session->getQuote()->collectTotals()->save();
                
                if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
                    $result['updatepayment'] = 1;
                }
            }
        }
        $result = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        if (isset($notice)) {
            $result['notice'] = $notice;
        }
        
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
