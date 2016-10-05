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
namespace Magestore\Giftvoucher\Observer\Backend;

use Magento\Framework\DataObject;

class OrderSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Process Gift Card data after order is saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_COMPLETE) {
            $this->_addGiftVoucherForOrder($order);
        }
        
        $refundState = array('canceled');
        if (in_array($order->getStatus(), $refundState)) {

            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() != 'giftvoucher') {
                    continue;
                }
                $req = $item->getProductOptions()['info_buyRequest'];
                $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                    ->getCollection()
                    ->addItemFilter($item->getQuoteItemId());
                $itemQtyInvoice =$req['qty'];

                foreach ($giftVouchers as $giftVoucher) {
                    if ($giftVoucher->getUsed() ==1) {
                        $giftVoucher->addData(array(
                            'used' => 2,
                            'status' => \Magestore\Giftvoucher\Model\Status::STATUS_DISABLED,
                            'comments' => __('Canceled order'),
                            'amount' => $giftVoucher->getBalance(),
                            'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_UPDATE,
                            'order_increment_id'    => $order->getIncrementId()
                        ))->setIncludeHistory(true);
                        try {

                            $giftVoucher->save();

                        } catch (\Exception $e) {
                            $this->messageManager->addError($e->getMessage());
                        }
                        $itemQtyInvoice -= 1;
                        if (!$itemQtyInvoice) {
                            break;
                        }
                    }
                }

            }
            $this->_refundOffline($order, $order->getBaseGiftVoucherDiscount());
        }
    }
}
