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
 * Adminhtml Giftvoucher Edit Tab Message Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Message extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('customer_fieldset', array('legend' => __('Customer')));

        $fieldset->addField('customer_name', 'text', array(
            'label' => __('Customer Name'),
            'required' => false,
            'name' => 'customer_name',
        ));

        $fieldset->addField('customer_email', 'text', array(
            'label' => __('Customer Email'),
            'required' => false,
            'name' => 'customer_email',
        ));

        $fieldset = $form->addFieldset('recipient_fieldset', array('legend' => __('Recipient')));

        $fieldset->addField('recipient_name', 'text', array(
            'label' => __('Recipient Name'),
            'required' => false,
            'name' => 'recipient_name',
        ));

        $fieldset->addField('recipient_email', 'text', array(
            'label' => __('Recipient Email'),
            'required' => false,
            'name' => 'recipient_email',
        ));

         $fieldset = $form->addFieldset('shipping_address', array('legend' => __('Shipping Address')));

        $fieldset->addField('recipient_address', 'editor', array(
            'label' => __('Recipient Address'),
            'name' => 'recipient_address',
            'style' => 'height:75px;',
        ));

        $fieldset = $form->addFieldset('message_fieldset', array('legend' => __('Message')));

        $fieldset->addField('message', 'editor', array(
            'label' => __('Message'),
            'required' => false,
            'name' => 'message',
        ));

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
