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

class OrderPlaceAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Process Gift Card data after placing order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_addGiftVoucherForOrder($order);
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $adminSession = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');

        if ($this->_appState->getAreaCode() == 'admin') {
            $store = $adminSession->getStore();
            $isAdmin = true;
        } else {
            $store = $this->_storeManager->getStore();
        }
            
        if ($session->getMessageApplyGiftcardWithCouponCode()) {
            $session->setMessageApplyGiftcardWithCouponCode(false);
            throw new \Magento\Framework\Exception\LocalizedException(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
        }
        if (isset($isAdmin) && $isAdmin && $adminSession->getQuote()->getCouponCode()
            && !$this->_helperData->getGeneralConfig('use_with_coupon') && ($session->getUseGiftCreditAmount() > 0
            || $session->getGiftVoucherDiscount() > 0)) {
            if ($session->getUseGiftCard()) {
                $session->setUseGiftCard(null)
                        ->setGiftCodes(null)
                        ->setBaseAmountUsed(null)
                        ->setBaseGiftVoucherDiscount(null)
                        ->setGiftVoucherDiscount(null)
                        ->setCodesBaseDiscount(null)
                        ->setCodesDiscount(null)
                        ->setGiftMaxUseAmount(null);
            }
            if ($session->getUseGiftCardCredit()) {
                $session->setUseGiftCardCredit(null)
                        ->setMaxCreditUsed(null)
                        ->setBaseUseGiftCreditAmount(null)
                        ->setUseGiftCreditAmount(null);
            }

            throw new \Magento\Framework\Exception\LocalizedException(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
        }

        if (!$session->getUseGiftCard() && !($session->getUseGiftCardCredit())) {
            return;
        }
        
        $codes = $order->getGiftCodes();
        if ($codes = $order->getGiftCodes()) {
            $order->setGiftvoucherForOrderCodes($codes)
                    ->setGiftvoucherForOrderAmount($order->getGiftVoucherDiscount());
            $codesArray = explode(',', $codes);
            $codesBaseDiscount = explode(',', $order->getCodesBaseDiscount());
            $codesDiscount = explode(',', $order->getCodesDiscount());
            $baseDiscount = array_combine($codesArray, $codesBaseDiscount);
            $discount = array_combine($codesArray, $codesDiscount);
            foreach ($codesArray as $code) {
                if (!$baseDiscount[$code] || $this->_priceCurrency->round($baseDiscount[$code]) == 0) {
                    continue;
                }
                $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                    ->loadByCode($code);

                $baseCurrencyCode = $order->getBaseCurrencyCode();
                $baseCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                    ->load($baseCurrencyCode);
                $currentCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                    ->load($giftVoucher->getData('currency'));

                $codeDiscount = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($baseDiscount[$code], $baseCurrencyCode, $giftVoucher->getData('currency'));
                $codeCurrentDiscount = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($baseDiscount[$code], $baseCurrencyCode, $store->getCurrentCurrencyCode());
                $balance = $giftVoucher->getBalance() - $codeDiscount;
                if ($balance > 0) {
                    $baseBalance = $balance * $balance / $baseCurrency->convert($balance, $currentCurrency);
                } else {
                    $baseBalance = 0;
                }
                $currentBalance = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($baseBalance, $baseCurrencyCode, $store->getCurrentCurrencyCode());
                $giftVoucher->setData('balance', $balance)->save();
                $history = $this->_objectManager->create('Magestore\Giftvoucher\Model\History')->setData(array(
                            'order_increment_id' => $order->getIncrementId(),
                            'giftvoucher_id' => $giftVoucher->getId(),
                            'created_at' => date("Y-m-d H:i:s"),
                            'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_SPEND_ORDER,
                            'amount' => $codeCurrentDiscount,
                            'balance' => $currentBalance,
                            'currency' => $store->getCurrentCurrencyCode(),
                            'status' => $giftVoucher->getStatus(),
                            'order_amount' => $discount[$code],
                            'comments' => __('Spent on order %1', $order->getIncrementId()),
                            'extra_content' => __('Used by %1 %1', $order->getData('customer_firstname'), $order->getData('customer_lastname')),
                            'customer_id' => $order->getData('customer_id'),
                            'customer_email' => $order->getData('customer_email')
                        ))->save();

                // add gift code to customer list
                if ($order->getCustomerId()) {
                    $collection = $this->_objectManager
                            ->create('Magestore\Giftvoucher\Model\ResourceModel\Customervoucher\Collection')
                            ->addFieldToFilter('customer_id', $order->getCustomerId())
                            ->addFieldToFilter('voucher_id', $giftVoucher->getId());
                    if (!$collection->getSize()) {
                        try {
                            $timeSite = date(
                                "Y-m-d",
                                $this->_helperData->getObjectManager()
                                    ->get('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time())
                            );
                            $this->_objectManager->create('Magestore\Giftvoucher\Model\Customervoucher')
                                    ->setCustomerId($order->getCustomerId())
                                    ->setVoucherId($giftVoucher->getId())
                                    ->setAddedDate($timeSite)
                                    ->save();
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }
        
        if ($order->getGiftcardCreditAmount() && $order->getCustomerId()) {
            $credit = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credit')
                        ->load($order->getCustomerId(), 'customer_id');
            if ($credit->getId()) {
                try {
                    $credit->setBalance($credit->getBalance() - $order->getGiftcardCreditAmount());
                    $credit->save();
                    if ($store->getCurrentCurrencyCode() != $order->getBaseCurrencyCode()) {
                        $currencyBalance = $this->_priceCurrency->convert(round($credit->getBalance(), 4));
                    } else {
                        $currencyBalance = round($credit->getBalance(), 4);
                    }
                    $credithistory = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credithistory')
                                        ->setData($credit->getData());
                    $credithistory->addData(array(
                        'action' => 'Spend',
                        'currency_balance' => $currencyBalance,
                        'order_id' => $order->getEntityId(),
                        'order_number' => $order->getIncrementId(),
                        'balance_change' => $order->getUseGiftCreditAmount(),
                        'created_date' => date("Y-m-d H:i:s"),
                        'currency' => $store->getCurrentCurrencyCode(),
                        'base_amount' => $order->getBaseUseGiftCreditAmount(),
                        'amount' => $order->getUseGiftCreditAmount()
                    ))->setId(null)->save();
                } catch (\Exception $e) {
                    //Mage::logException($e);
                }
            }
        }

        // Create invoice for Order payed by Giftvoucher
        if ($this->_priceCurrency->round($order->getGrandTotal()) == 0
            && $order->getPayment()->getMethod() == 'free'
            && $order->canInvoice()) {
            try {
                $invoice = $order->prepareInvoice()->register();
                $order->addRelatedObject($invoice);
                if ($order->getState() == 'new') {
                    $order->setState('processing');
                }
                
                if ($order->getStatus() == 'pending') {
                    $order->setStatus('processing');
                }
            } catch (\Exception $e) {
            }
        }

        if ($session->getUseGiftCard()) {
            $session->setUseGiftCard(null)
                    ->setGiftCodes(null)
                    ->setBaseAmountUsed(null)
                    ->setBaseGiftVoucherDiscount(null)
                    ->setGiftVoucherDiscount(null)
                    ->setCodesBaseDiscount(null)
                    ->setCodesDiscount(null)
                    ->setGiftMaxUseAmount(null);
        }
        if ($session->getUseGiftCardCredit()) {
            $session->setUseGiftCardCredit(null)
                    ->setMaxCreditUsed(null)
                    ->setBaseUseGiftCreditAmount(null)
                    ->setUseGiftCreditAmount(null);
        }
    }
}
