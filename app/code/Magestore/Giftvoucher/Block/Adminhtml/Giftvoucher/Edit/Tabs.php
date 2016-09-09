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

namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit;

/**
 * Adminhtml Giftvoucher Edit Tabs Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_objectManager = $objectManager;
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftvoucher_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Gift Code Information'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addTab(
            'form_section',
            [
                'label' => __('General Information'),
                'content' =>  $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Form'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'condition',
            [
                'label' => __('Shopping Cart Conditions'),
                'content' =>  $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'action',
            [
                'label' => __('Cart Item Conditions'),
                'content' =>  $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Actions'
                )->toHtml()
            ]
        );
        
        $this->addTab(
            'message_section',
            [
                'label' => __('Message Information'),
                'content' =>  $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Message'
                )->toHtml()
            ]
        );
        
        if ($id = $this->getRequest()->getParam('id')) {
            if ($shipment = $this->getShipment($id)) {
                $this->addTab('shipping_and_tracking', array(
                    'label' => __('Shipping and Tracking'),
                    'title' => __('Shipping and Tracking'),
                    'content' => $this->getLayout()
                        ->createBlock('Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Shipping')
                        ->setShipment($shipment)
                        ->toHtml(),
                ));
            }
            
            $this->addTab(
                'history_section',
                [
                    'label' => __('Transaction History'),
                    'content' =>  $this->getLayout()->createBlock(
                        'Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\History'
                    )->setGiftvoucher($id)->toHtml()
                ]
            );
            
        }
    }

    /**
     * Get shipment for gift card
     *
     * @param int $giftCardId
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment($giftCardId)
    {
        $history = $this->_objectManager->create('Magestore\Giftvoucher\Model\ResourceModel\History\Collection')
                ->addFieldToFilter('giftvoucher_id', $giftCardId)
                ->addFieldToFilter('action', \Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE)
                ->getFirstItem();
        if (!$history->getOrderIncrementId() || !$history->getQuoteItemId()) {
            return false;
        }
        $orderItem = $this->_objectManager->create('Magento\Sales\Model\Order\Item')->getCollection()
                ->addFieldToFilter('quote_item_id', $history->getQuoteItemId())->getFirstItem();
        $requestInfo = $orderItem->getProductOptionByCode('info_buyRequest');
        if (!isset($requestInfo['send_friend'])) {
            return false;
        }
        if (!$requestInfo['send_friend']) {
            return false;
        }
        $shipmentItem = $this->_objectManager
            ->create('Magento\Sales\Model\ResourceModel\Order\Shipment\Item\Collection')
            ->addFieldToFilter('order_item_id', $orderItem->getId())
            ->getFirstItem();
        if (!$shipmentItem || !$shipmentItem->getId()) {
            return true;
        }
        $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')
            ->load($shipmentItem->getParentId());
        if (!$shipment->getId()) {
            return true;
        }
        return $shipment;
    }
}
