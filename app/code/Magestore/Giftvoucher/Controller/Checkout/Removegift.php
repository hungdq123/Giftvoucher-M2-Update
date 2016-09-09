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
 * Giftvoucher Checkout Removegift Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Removegift extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $helper = $this->getHelperData();
        $session = $helper->getCheckoutSession();
        $code = trim($this->getRequest()->getParam('code'));
        $codes = trim($session->getGiftCodes());
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
        
        if ($success) {
            $codes = implode(',', $codesArray);
            $session->setGiftCodes($codes);
            $this->messageManager->addSuccess(__('Gift Card "%1" has been removed successfully!', $helper->getHiddenCode($code)));
        } else {
            $this->messageManager->addError(__('Gift card "%1" not found!', $code));
        }
        $session->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $session->getQuote()->collectTotals();
        $this->_objectManager->get('Magento\Quote\Model\QuoteRepository')->save($session->getQuote());
        $this->_redirect('checkout/cart');
    }
}
