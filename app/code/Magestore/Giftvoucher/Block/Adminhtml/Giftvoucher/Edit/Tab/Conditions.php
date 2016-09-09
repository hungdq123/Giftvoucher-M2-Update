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
 * Adminhtml Giftvoucher Edit Tab Conditions Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Conditions extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        
        if ($this->_coreRegistry->registry('giftvoucher_data')) {
            $model = $this->_coreRegistry->registry('giftvoucher_data');
        } else {
            $model = $this->_giftvoucher;
        }
        
        $data = $model->getData();
        $model->setData('conditions', $model->getData('conditions_serialized'));

        $renderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/giftvoucher_conditions_fieldset')
            );
        
        $fieldset = $form->addFieldset('description_fieldset', array('legend' => __('Description')));

        $fieldset->addField('description', 'editor', array(
            'label' => __('Describe conditions applied to shopping cart when using this gift code'),
            'title' => __('Describe conditions applied to shopping cart when using this gift code'),
            'name' => 'description',
            'wysiwyg' => true,
            'config' => $this->_wysiwygConfig->getConfig(),
        ));
        $fieldset = $form->addFieldset(
            'rule_conditions_fieldset',
            array(
                'legend' => __('Allow using the gift code only if the following shopping cart conditions are met (leave blank for all shopping carts)')
            )
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer($this->_conditions);

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
