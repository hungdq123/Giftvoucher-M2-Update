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
namespace Magestore\Giftvoucher\Block\Order\Creditmemo;

/**
 * Giftvoucher Creditmemo Totals Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataObject = $dataObject;
        parent::__construct($context, $registry, $data);
    }

    public function initTotals()
    {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getCreditmemo();
        if ($order->getGiftVoucherDiscount() && $order->getGiftVoucherDiscount() > 0) {
//            $dataObject = $this->_dataObject->setData(
//                [
//                    'code' => 'giftvoucher',
//                    'label' => __('Gift Card (%1)', $order->getOrder()->getGiftCodes()),
//                    'value' => -$order->getGiftVoucherDiscount(),
//                    'base_value' => -$order->getBaseGiftVoucherDiscount(),
//                ]
//            );
//            $orderTotalsBlock->addTotal($dataObject, 'subtotal');

            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => 'giftvoucher',
                    'label' => __('Gift Card (%1)', $order->getOrder()->getGiftCodes()),
                    'value' => -$order->getGiftVoucherDiscount(),
                    'base_value' => -$order->getBaseGiftVoucherDiscount(),
                ]
            ), 'subtotal');
        }
        $refund = (float) $order->getGiftcardRefundAmount();
        if (($refund >0 || $refund === 0.0) && ($order->getOrder()->getUseGiftCreditAmount()
            || $order->getOrder()->getGiftVoucherDiscount())) {
            if ($order->getOrder()->getCustomerIsGuest()) {
                $label = __('Refund to your gift card code');
            } else {
                $label = __('Refund to your credit balance');
            }
            $dataObject = $this->_dataObject->setData(
                [
                    'code' => 'giftcard_refund',
                    'label' => $label,
                    'value' => $refund,
                    'area' => 'footer',
                ]
            );
            $orderTotalsBlock->addTotal($dataObject, 'subtotal');
        }
    }
}
