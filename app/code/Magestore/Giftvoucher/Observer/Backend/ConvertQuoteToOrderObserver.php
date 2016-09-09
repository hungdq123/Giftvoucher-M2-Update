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

class ConvertQuoteToOrderObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Convert Quote To Order
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer['order'];
        $quote = $observer['quote'];
        $session = $this->_checkoutSession;
        if ($quote->getGiftCodes()) {
            $order->setGiftCodes($quote->getGiftCodes());
            $order->setBaseGiftCodes($quote->getBaseGiftCodes());
        }
        if ($session->getUseGiftCreditAmount()) {
            $order->setUseGiftCreditAmount($session->getUseGiftCreditAmount());
            $order->setBaseUseGiftCreditAmount($session->getBaseUseGiftCreditAmount());
            $order->setGiftcardCreditAmount($session->getGiftcardCreditAmount());
            $order->setBaseGiftcreditDiscountForShipping($session->getBaseGiftcreditDiscountForShipping());
            $order->setGiftcreditDiscountForShipping($session->getGiftcreditDiscountForShipping());
            $order->setGiftcardCreditAmount($session->getGiftcardCreditAmount());
        }
        if ($quote->getCodesDiscount()) {
            $order->setCodesDiscount($quote->getCodesDiscount());
            $order->setCodesBaseDiscount($quote->getCodesBaseDiscount());
        }
        if ($quote->getGiftVoucherDiscount()) {
            $order->setGiftVoucherDiscount($quote->getGiftVoucherDiscount());
            $order->setBaseGiftVoucherDiscount($quote->getBaseGiftVoucherDiscount());
            $order->setGiftvoucherBaseHiddenTaxAmount($session->getGiftvoucherBaseHiddenTaxAmount());
            $order->setGiftvoucherHiddenTaxAmount($session->getGiftvoucherHiddenTaxAmount());
            $order->setBaseGiftvoucherDiscountForShipping($session->getBaseGiftvoucherDiscountForShipping());
            $order->setGiftvoucherDiscountForShipping($session->getGiftvoucherDiscountForShipping());
            $order->setGiftvoucherBaseShippingHiddenTaxAmount($session->getGiftvoucherBaseShippingHiddenTaxAmount());
            $order->setGiftvoucherShippingHiddenTaxAmount($session->getGiftvoucherShippingHiddenTaxAmount());
        }
        $applyGiftAfterTax = (bool) $this->_helperData->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        
        if (!$applyGiftAfterTax) {
            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                            $child->setDiscountAmount($child->getDiscountAmount()-$child->getGiftVoucherDiscount()
                                -$child->getUseGiftCreditAmount());
                            $child->setBaseDiscountAmount($child->getBaseDiscountAmount()
                                -$child->getBaseGiftVoucherDiscount()-$child->getBaseUseGiftCreditAmount());
                        }
                    }
                } elseif ($item->getProduct()) {
                    if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                        $item->setDiscountAmount($item->getDiscountAmount()-$item->getGiftVoucherDiscount()
                            -$item->getUseGiftCreditAmount());
                        $item->setBaseDiscountAmount($item->getBaseDiscountAmount()-$item->getBaseGiftVoucherDiscount()
                            -$item->getBaseUseGiftCreditAmount());
                    }
                }
            }
            
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildrenItems() as $child) {
                        if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                            $child->setDiscountAmount($child->getDiscountAmount()-$child->getGiftVoucherDiscount()
                                -$child->getUseGiftCreditAmount());
                            $child->setBaseDiscountAmount($child->getBaseDiscountAmount()
                                -$child->getBaseGiftVoucherDiscount()-$child->getBaseUseGiftCreditAmount());
                        }
                    }
                } elseif ($item->getProduct() && !$item->getParentItem()) {
                    if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                        $item->setDiscountAmount($item->getDiscountAmount()-$item->getGiftVoucherDiscount()
                            -$item->getUseGiftCreditAmount());
                        $item->setBaseDiscountAmount($item->getBaseDiscountAmount()-$item->getBaseGiftVoucherDiscount()
                            -$item->getBaseUseGiftCreditAmount());
                    }
                }
            }
        }
    }
}
