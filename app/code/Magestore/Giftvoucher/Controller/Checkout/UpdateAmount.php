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
 * Giftvoucher Checkout UpdateAmount Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class UpdateAmount extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $quote = $session->getQuote();
        $codes = $session->getGiftCodes();

        $code = $this->getRequest()->getParam('code');
        $amount = floatval($this->getRequest()->getParam('amount'));
        $result = array();
        if ($quote->getCouponCode() && !$this->getHelperData()->getGeneralConfig('use_with_coupon')
            && (!$session->getUseGiftCreditAmount() || !$session->getGiftVoucherDiscount())) {
            $result['notice'] = __('Coupon code have been using. You can\'t apply gift code along with coupon code to discount');
        } else {
            if (in_array($code, explode(',', $codes))) {
                $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                if (!is_array($giftMaxUseAmount)) {
                    $giftMaxUseAmount = array();
                }
                $giftMaxUseAmount[$code] = $amount;
                $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
                $quote->collectTotals()->save();
            } else {
                $result['error'] = __('Gift card "%1" is not added.', $code);
            }
        }
        $result['html'] = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
