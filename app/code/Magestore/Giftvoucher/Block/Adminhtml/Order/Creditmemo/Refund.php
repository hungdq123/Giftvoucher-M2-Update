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
namespace Magestore\Giftvoucher\Block\Adminhtml\Order\Creditmemo;

/**
 * Adminhtml Giftvoucher Creditmemo Refund Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Refund extends \Magento\Framework\View\Element\Template
{
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    public function getCustomer()
    {
        $order = $this->getOrder();
        if ($order->getCustomerIsGuest()) {
            return false;
        }
        $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($order->getCustomerId());
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    public function getIsShow()
    {
        return ($this->getCreditmemo()->getUseGiftCreditAmount() || $this->getCreditmemo()->getGiftVoucherDiscount());
    }

    public function getMaxAmount()
    {
        $maxAmount = 0;
        if ($this->getCreditmemo()->getUseGiftCreditAmount() && $this->isEnableCredit()) {
            $maxAmount += floatval($this->getCreditmemo()->getUseGiftCreditAmount());
        }
        if ($this->getCreditmemo()->getGiftVoucherDiscount()) {
            $maxAmount += floatval($this->getCreditmemo()->getGiftVoucherDiscount());
        }
        return $this->priceCurrency->round($maxAmount);
    }

    public function formatPrice($price)
    {
        return $this->getOrder()->format($price);
    }
    
    public function isEnableCredit()
    {
        return $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')
            ->getGeneralConfig('enablecredit', $this->getOrder()->getStoreId());
    }
}
