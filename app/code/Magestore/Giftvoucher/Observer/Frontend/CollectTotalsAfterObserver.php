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
namespace Magestore\Giftvoucher\Observer\Frontend;

use Magento\Framework\DataObject;

class CollectTotalsAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Set Quote information about gift codes
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($code = trim($this->_request->getParam('coupon_code'))) {
            $quote = $observer->getEvent()->getQuote();
            if ($code != $quote->getCouponCode()) {
                $codes = $this->_objectManager->get('Magento\Checkout\Model\Session')->getCodes();
                $codes[] = $code;
                $codes = array_unique($codes);
                $this->_objectManager->get('Magento\Checkout\Model\Session')->setCodes($codes);
            }
        }
    }
}
