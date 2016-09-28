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
 * Adminhtml Giftvoucher Edit Tab Form Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $isElementDisabled = false;

        $fieldset = $form->addFieldset('giftvoucher_form', array('legend' => __('General Information')));
        if ($this->_backendSession->getGiftvoucherData()) {
            $model = $this->_backendSession->getGiftvoucherData();
            $this->_backendSession->setGiftvoucherData(null);
        } elseif ($this->_coreRegistry->registry('giftvoucher_data')) {
            $model = $this->_coreRegistry->registry('giftvoucher_data');
        } else {
            $model = $this->_giftvoucher;
        }
         
        if (isset($model) && $model->getId()) {
            $fieldset->addField('giftvoucher_id', 'hidden', array('name' => 'giftvoucher_id'));
        }

        $fieldset->addField(
            'gift_code',
            'text',
            array(
                'name' => 'gift_code',
                'label' => __('Gift Code Pattern'),
                'title' => __('Gift Code Pattern'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'note' => __('Examples:<br/><strong>[A.8] : 8 alpha<br/>[N.4] : 4 numeric<br/>[AN.6] : 6 alphanumeric<br/>GIFT-[A.4]-[AN.6] : GIFT-ADFA-12NF0O</strong>'),
            )
        );
        
        $fieldset->addField(
            'balance',
            'text',
            array(
                'name' => 'balance',
                'label' => __('Gift Code Value'),
                'title' => __('Gift Code Value'),
                'style' =>  'max-width: 250px',
                'required' => true,
                'disabled' => $isElementDisabled,
            )
        );
        
        $fieldset->addField(
            'currency',
            'select',
            array(
                'label' => __('Currency'),
                'style' =>  'min-width: 250px',
                'required' => false,
                'name' => 'currency',
                'value' => $this->_storeManager->getStore()->getDefaultCurrencyCode(),
                'values' => $this->_helperData->getAllowedCurrencies(),
            )
        );
        
        $template = $this->getGiftTemplate();
        
        if (isset($model['giftcard_custom_image']) && $model['giftcard_custom_image']) {
            $fieldset->addField(
                'giftcard_template_id',
                'hidden',
                array(
                    'label' => __('Template'),
                    'name' => 'giftcard_template_id',
                    'style' =>  'min-width: 250px',
                    'values' => (isset($model['giftcard_template_id'])) ? $model['giftcard_template_id'] : '',
                    'after_element_html' => (isset($model['giftcard_template_image'])
                        && isset($model['giftcard_template_id'])) ?
                        '<script> window.onload = function(){loadImageTemplate(\'' . $model['giftcard_template_id']
                        . '\',\'' . $model['giftcard_template_image'] . '\',true);}</script>' : '',
                )
            );

            $fieldset->addField(
                'list_images',
                'note',
                array(
                    'label' => __('Customer\'s Image'),
                    'name' => 'list_images',
                    'text' => sprintf(''),
                )
            );

            $fieldset->addField(
                'giftcard_template_image',
                'hidden',
                array(
                    'name' => 'giftcard_template_image',
                    'value' => $model['giftcard_template_image'],
                )
            );
        } elseif ($template && count($template)) {
            $fieldset->addField(
                'giftcard_template_id',
                'select',
                array(
                    'label' => __('Template'),
                    'name' => 'giftcard_template_id',
                    'values' => $template,
                    'style' =>  'min-width: 250px',
                    'required' => true,
                    'onchange' => 'loadImageTemplate(this.value)',
                    'after_element_html' => (isset($model['giftcard_template_image'])
                        && isset($model['giftcard_template_id'])) ?
                        '<script>  window.onload = function(){loadImageTemplate(\'' . $model['giftcard_template_id']
                    . '\',\'' . $model['giftcard_template_image'] . '\',false);}</script>' : '',
                )
            );

            $fieldset->addField(
                'list_images',
                'note',
                array(
                    'label' => __('Template image'),
                    'name' => 'list_images',
                    'text' => sprintf(''),
                )
            );
            $fieldset->addField(
                'giftcard_template_image',
                'hidden',
                array(
                    'name' => 'giftcard_template_image',
                )
            );
        }
        
        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => __('Status'),
                'name' => 'giftvoucher_status',
                'style' =>  'min-width: 250px',
                'values' => \Magestore\Giftvoucher\Model\Status::getOptions()
            )
        );
        
        $fieldset->addField(
            'expired_at',
            'date',
            array(
                'label' => __('Expired on'),
                'required' => false,
                'name' => 'expired_at',
                'input_format' => 'yyyy-MM-dd',
                'readonly' => true,
                'style' =>  'min-width: 215px;opacity:1;background-color:#fff',
                'date_format' => 'MM/dd/yyyy',
            )
        );
        
        $fieldset->addField(
            'store_id',
            'select',
            array(
                'label' => __('Store view'),
                'name' => 'store_id',
                'style' =>  'min-width: 250px',
                'required' => false,
                'values' => $this->_systemStore->getStoreValuesForForm(false, true)
            )
        );

        $fieldset->addField(
            'giftvoucher_comments',
            'editor',
            array(
                'label' => __('Last comment'),
                'required' => false,
                'name' => 'giftvoucher_comments',
                'style' => 'height:100px;',
            )
        );
//        $fieldset->addField(
//            'used',
//            'select',
//            array(
//                'label' => __('Used'),
//                'name' => 'giftvoucher_used',
//                'style' =>  'min-width: 250px',
//                'values' => \Magestore\Giftvoucher\Model\Used::getOptions(),
//                'note' => __('Yes:The gift code has been purchased;'<\br>'No:The gift code has not been purchased'<\br>'None:The gift code ')
//            )
//        );


        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }


    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
