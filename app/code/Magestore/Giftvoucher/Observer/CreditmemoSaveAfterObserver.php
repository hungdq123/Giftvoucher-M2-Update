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

class CreditmemoSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Process Gift Card data after creditmemo is saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\CreditmemoSaveAfterObserver
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $baseGrandTotal = $creditmemo->getBaseGrandTotal();
        $order = $creditmemo->getOrder();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getId());

        foreach ($creditmemo->getAllItems() as $itemCredit) {
            $item = $order->getItemById($itemCredit->getOrderItemId());
            if (isset($item) && $item != null) {
                if ($item->getProductType() != 'giftvoucher') {
                    continue;
                }
                $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                                    ->getCollection()
                                    ->addItemFilter($item->getQuoteItemId());
                $productOptions = $item->getProductOptions();
                $cantRefundGiftvoucherProduct = $item->getQtyInvoiced() - $item->getQtyRefunded();
                foreach ($giftVouchers as $giftVoucher) {
                    $giftVoucher->setCanRefund(true);
                    if (($giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                        && $giftVoucher->getBalance() < $productOptions['info_buyRequest']['amount'])
                        || $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_USED) {
                        $cantRefundGiftvoucherProduct -= 1;
                        $giftVoucher->setCanRefund(false);
                    }
                }

                if ($cantRefundGiftvoucherProduct < ($item->getQtyInvoiced() - $item->getQtyRefunded())) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('There is atleast one of products which is giftvoucher and being used.')
                    );
                }

                $itemQtyRefund = $itemCredit->getQty();
                foreach ($giftVouchers as $giftVoucher) {
                    if ($giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                        && $giftVoucher->getCanRefund()) {
                        $itemQtyRefund -= 1;
                        $giftVoucher->addData(array(
                            'status' => \Magestore\Giftvoucher\Model\Status::STATUS_DISABLED,
                            'comments' => __('Refund order %1', $order->getIncrementId()),
                            'amount' => $giftVoucher->getBalance(),
                            'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_REFUND,
                            'order_increment_id' => $order->getIncrementId(),
                            'currency' => $giftVoucher->getCurrency(),
                        ))->setIncludeHistory(true);
                        try {
                            $giftVoucher->save();
                            if ($this->_helperData->getEmailConfig('send_refund', $order->getStoreId())
                                && $giftVoucher->getData('is_sent')) {
                                $giftVoucher->sendEmailRefundToRecipient();
                            }
                        } catch (\Exception $e) {
                        }
                        if (!$itemQtyRefund) {
                            break;
                        }
                    }
                }
            }
        }
        // manual save in Backend
        if ($this->_appState->getAreaCode() == 'adminhtml' && $creditmemo->getGiftcardRefundAmount()) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
            if ($customer->getId() && $this->_helperData->getGeneralConfig('enablecredit', $creditmemo->getStoreId())) {
                $credit = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credit')
                            ->load($customer->getId(), 'customer_id');
                if (!$credit->getId()) {
                    $credit->setCustomerId($customer->getId())
                            ->setCurrency($order->getBaseCurrencyCode())
                            ->setBalance(0);
                }
                $refundAmount = 0;
                
                $refundAmount = $creditmemo->getGiftcardRefundAmount();
                if ($refundAmount) {
                    $creditBalance = $refundAmount;
                    try {
                        $credit->setBalance($credit->getBalance() + $creditBalance)
                                ->save();

                        if ($order->getOrderCurrencyCode() != $order->getBaseCurrencyCode()) {
                            $baseCurrency = $this->_objectManager->create('Magentp\Directory\Model\Currency')
                                                    ->load($order->getBaseCurrencyCode());
                            $currentCurrency = $this->_objectManager->create('Magentp\Directory\Model\Currency')
                                                    ->load($order->getOrderCurrencyCode());
                            $currencyBalance = $baseCurrency
                                                    ->convert(round($credit->getBalance(), 4), $currentCurrency);
                        } else {
                            $currencyBalance = round($credit->getBalance(), 4);
                        }

                        $credithistory = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credithistory')
                                            ->setData($credit->getData());
                        $credithistory->addData(array(
                            'action' => 'Refund',
                            'currency_balance' => $currencyBalance,
                            'order_id' => $order->getId(),
                            'order_number' => $order->getIncrementId(),
                            'balance_change' => $creditmemo->getGiftcardRefundAmount(),
                            'created_date' => date("Y-m-d H:i:s"),
                            'currency' => $order->getOrderCurrencyCode(),
                            'base_amount' => $refundAmount,
                            'amount' => $creditmemo->getGiftcardRefundAmount()
                        ))->setId(null)->save();
                    } catch (\Exception $e) {
                    }
                }
            } else {
                $refundAmount = 0;
                $baseCurrency = $this->_storeManager->getStore($order->getStoreId())->getBaseCurrency();
                if ($rate = $baseCurrency->getRate($order->getOrderCurrencyCode())) {
                    $refundAmount = $creditmemo->getGiftcardRefundAmount() / $rate;
                }
                if ($refundAmount) {
                    $this->_refundOffline($order, $refundAmount);
                }
            }
            return;
        }
        // online save in frontend
        if (!$this->_appState->getAreaCode() == 'admin' && $this->_helperData->getGeneralConfig('online_refund')) {
            if ($creditmemo->getBaseGiftVoucherDiscount()) {
                $maxAmount = floatval($creditmemo->getBaseGiftVoucherDiscount());
                $this->_refundOffline($order, $maxAmount);
            }
        }
        // refund for Giftvoucher payment method
        if ($order->getPayment()->getMethod() == 'giftvoucher') {
            $this->_refundOffline($order, $baseGrandTotal);
        }
    }
}
