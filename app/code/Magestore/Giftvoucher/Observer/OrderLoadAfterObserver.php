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

class OrderLoadAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Loading Gift Card information after order loaded
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\OrderLoadAfterObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_loadOrderData($order);

        if ((abs($order->getGiftVoucherDiscount()) < 0.0001 && abs($order->getUseGiftCreditAmount()) < 0.0001)
            || $order->getState() === \Magento\Sales\Model\Order::STATE_CLOSED || $order->isCanceled()
            || $order->canUnhold()) {
            return;
        }
        foreach ($order->getAllItems() as $item) {
            if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                $order->setForcedCanCreditmemo(true);
                return;
            }
        }
    }
}
