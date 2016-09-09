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
 * Giftvoucher Checkout Giftcard Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftcard extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $session->getQuote()->collectTotals()->save();
        if ($session->getQuote()->getCouponCode() && !$this->getHelperData()->getGeneralConfig('use_with_coupon')
            && (!$session->getUseGiftCreditAmount() || !$session->getGiftVoucherDiscount())
        ) {
            $result = array();
            $result['notice'] = __('A coupon code has been used. You cannot apply Gift Card credit with the coupon to get discount.');
        } else {
            $session->setUseGiftCard($this->getRequest()->getParam('giftvoucher'));
            $result = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        }
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
