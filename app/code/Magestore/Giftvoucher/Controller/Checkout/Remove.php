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
 * Giftvoucher Checkout Remove Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Remove extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $giftvoucherSession = $this->getModel('Magestore\Giftvoucher\Model\Session');
        $code = trim($this->getRequest()->getParam('code'));
        $codes = $session->getGiftCodes();
        $success = false;
        if ($code && $codes) {
            $codesArray = explode(',', $codes);
            foreach ($codesArray as $key => $value) {
                if ($value == $code) {
                    unset($codesArray[$key]);
                    $success = true;
                    $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                    if (is_array($giftMaxUseAmount) && array_key_exists($code, $giftMaxUseAmount)) {
                        unset($giftMaxUseAmount[$code]);
                        $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                    }
                    break;
                }
            }
        }

        $result = array();
        if ($success) {
            $codes = implode(',', $codesArray);
            $session->setGiftCodes($codes);
            $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
            $session->getQuote()->collectTotals()->save();
            $result['success'] = __('Gift Voucher "%1" has been removed from your order.', $this->getHelperData()->getHiddenCode($code));
        } else {
            $result['error'] = __('Gift card "%1" is not found.', $code);
        }
        $result['html'] = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
