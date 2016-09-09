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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab;

/**
 * Adminhtml Giftvoucher Edit Tab Shipping Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Shipping extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'shipping_and_tracking',
            array(
                'legend' => __('Shipping and Tracking Information')
            )
        );

        $fieldset->addField('shipped_to_customer', 'select', array(
            'label' => __('Shipped to Customer'),
            'required' => false,
            'name' => 'shipped_to_customer',
            'values' => $this->_yesno->toOptionArray()
        ));

        if (is_object($this->getShipment())) {
            $shipment = $this->getShipment();
            $this->_coreRegistry->register('current_shipment', $shipment);
            $fieldset->addField('adminhtml_shipment', 'note', array(
                'label' => __('Shipment'),
                'text' => '<a href="' . $this->getUrl('sales/shipment/view', array(
                        'shipment_id' => $shipment->getId()
                    ))
                    . '" title="">#' . $shipment->getIncrementId() . '</a>',
            ));
            $fieldset->addField('adminhtml_tracking', 'note', array(
                'label' => __('Tracking Information'),
                'text' => $this->getLayout()->createBlock('Magento\Shipping\Block\Adminhtml\Order\Tracking\View')
                        ->setShipment($shipment)
                        ->setTemplate('giftvoucher/tracking.phtml')->toHtml(),
            ));
        }

        if ($this->_coreRegistry->registry('giftvoucher_data')) {
            $model = $this->_coreRegistry->registry('giftvoucher_data');
        } else {
            $model = $this->_giftvoucher;
        }
        $form->addValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
