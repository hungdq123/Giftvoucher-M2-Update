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
namespace Magestore\Giftvoucher\Block\Adminhtml\Order\Invoice;

/**
 * Adminhtml Giftvoucher Invoice Credit Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Credit extends \Magento\Sales\Block\Adminhtml\Totals
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * Credit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataObject = $dataObject;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function initTotals()
    {
        parent::_initTotals();
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getInvoice();
        if ($order->getUseGiftCreditAmount() && $order->getUseGiftCreditAmount() > 0) {
//            $dataObject = $this->_dataObject->setData(
//                [
//                    'code' => 'giftcardcredit',
//                    'label' => __('Gift Card credit'),
//                    'value' => -$order->getUseGiftCreditAmount(),
//                    'base_value' => -$order->getBaseUseGiftCreditAmount(),
//                ]
//            );
//            $orderTotalsBlock->addTotal($dataObject, 'subtotal');
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => 'giftcardcredit',
                    'label' => __('Gift Card credit'),
                    'value' => -$order->getUseGiftCreditAmount(),
                    'base_value' => -$order->getBaseUseGiftCreditAmount(),
                ]
            ), 'subtotal');
        }
    }
}
