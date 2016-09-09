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
namespace Magestore\Giftvoucher\Observer;

use Magento\Framework\DataObject;

class UpdateShippedGiftCardObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Update the shipping information of Gift Card
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return type
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipmentItem = $observer->getEvent()->getShipmentItem();
        $orderItemId = $shipmentItem->getOrderItemId();

        $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher\Collection')
                            ->addItemFilter($orderItemId);
        foreach ($giftVouchers as $giftCard) {
            if ($giftCard->getShippedToCustomer()
                || !$this->_helperData->getStoreConfig('giftvoucher/general/auto_shipping', $giftCard->getStoreId())
            ) {
                return;
            }
            try {
                $giftCard->setShippedToCustomer(1)
                        ->save();
            } catch (\Exception $e) {
            }
        }
    }
}
