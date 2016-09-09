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
namespace Magestore\Giftvoucher\Model\Plugin;

use Closure;
use Magento\Sales\Model\Order\Item;

/**
 * Giftvoucher Plugin QuoteItem Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class QuoteItem
{
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magestore\Giftvoucher\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magestore\Giftvoucher\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        if ($item->getUseGiftCreditAmount()) {
            $orderItem->setUseGiftCreditAmount($item->getUseGiftCreditAmount());
            $orderItem->setBaseUseGiftCreditAmount($item->getBaseUseGiftCreditAmount());
            $orderItem->setGiftcardCreditAmount($item->getGiftcardCreditAmount());
            $orderItem->setGiftcreditHiddenTaxAmount($item->getGiftcreditHiddenTaxAmount());
            $orderItem->setGiftcreditBaseHiddenTaxAmount($item->getGiftcreditBaseHiddenTaxAmount());
        }
        if ($item->getGiftVoucherDiscount()) {
            $orderItem->setGiftVoucherDiscount($item->getGiftVoucherDiscount());
            $orderItem->setBaseGiftVoucherDiscount($item->getBaseGiftVoucherDiscount());
            $orderItem->setGiftvoucherBaseHiddenTaxAmount($item->getGiftvoucherBaseHiddenTaxAmount());
            $orderItem->setGiftvoucherHiddenTaxAmount($item->getGiftvoucherHiddenTaxAmount());
        }
        return $orderItem;
    }
}
