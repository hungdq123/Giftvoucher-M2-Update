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

namespace Magestore\Giftvoucher\Block\Adminhtml\Customer\Tab;

/**
 * Adminhtml Giftvoucher Customer Tab Renderer Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Renderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Render order info to grid column html
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return sprintf(
            '<a href="%s" title="%s">%s</a>',
            $this->urlBuilder->getUrl(
                'sales/order/view',
                array('order_id' => $this->_getOrderId($row))
            ),
            __('View Order Detail'),
            $row->getOrderNumber()
        );
    }

    protected function _getOrderId($row)
    {
        if (!$row->getOrderId()) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')
                ->load($row->getOrderNumber(), 'increment_id');
            return $order->getEntityId();
        }
        return $row->getOrderId();
    }
}
