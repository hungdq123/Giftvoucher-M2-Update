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

class InvoiceSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Process Gift Card data after invoice is saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
          
        foreach ($invoice->getAllItems() as $itemCredit) {
            $item = $order->getItemById($itemCredit->getOrderItemId());
            if (isset($item) && $item != null) {
                if ($item->getProductType() != 'giftvoucher') {
                    continue;
                }
                
                $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                    ->getCollection()
                    ->addItemFilter($item->getQuoteItemId());
                $itemQtyInvoice = $itemCredit->getQty();
                foreach ($giftVouchers as $giftVoucher) {
                    if ($giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_PENDING) {
                        $giftVoucher->addData(array(
                            'status' => \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE,
                            'comments' => __('Active when order is complete'),
                            'amount' => $giftVoucher->getBalance(),
                            'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_UPDATE,
                            'order_increment_id'    => $order->getIncrementId()
                        ))->setIncludeHistory(true);
                        try {
                            if ($giftVoucher->getDayToSend() && strtotime($giftVoucher->getDayToSend()) > time()
                            ) {
                                $giftVoucher->setData('dont_send_email_to_recipient', 1);
                            }
                            if (!empty($buyRequest['recipient_ship'])) {
                                $giftVoucher->setData('is_sent', 2);
                                if (!$this->_helperData->getEmailConfig('send_with_ship', $order->getStoreId())) {
                                    $giftVoucher->setData('dont_send_email_to_recipient', 1);
                                }
                            }
                            $giftVoucher->save();
                            if ($this->_helperData->getEmailConfig('enable', $order->getStoreId())) {
                                //Hai.Tran
                                $giftVoucher->setIncludeHistory(false);
                               
                                if ($giftVoucher->getRecipientEmail()) {
                                    if ($giftVoucher->sendEmailToRecipient() && $giftVoucher->getNotifySuccess()) {
                                        $giftVoucher->sendEmailSuccess();
                                    }
                                } else {
                                    $giftVoucher->sendEmail();
                                }
                            }
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
        }
    }
}
