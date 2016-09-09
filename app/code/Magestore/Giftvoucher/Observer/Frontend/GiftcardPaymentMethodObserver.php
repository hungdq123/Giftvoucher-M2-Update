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

class GiftcardPaymentMethodObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Render Gift Card form
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        if ($this->_helperData->getGeneralConfig('active', $storeId)) {
            $action = $observer->getEvent()->getControllerAction();
            if ($observer['element_name']=='checkout.cart.coupon') {
                $data = $observer['transport']->getData('output');
                $htmlAddgiftcardform = $observer['layout']->createBlock('Magestore\Giftvoucher\Block\Cart\Giftcard')
                                            ->toHtml();
                $observer['transport']->setData('output', $data.$htmlAddgiftcardform);
            };
        }
    }
}
