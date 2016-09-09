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
 * Adminhtml Giftvoucher Edit Tab Actions Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Actions extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
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
        $model->setData('conditions', $model->getData('actions_serialized'));
        $renderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
                ->setTemplate(
                    'Magento_CatalogRule::promo/fieldset.phtml'
                )->setNewChildUrl(
                    $this->getUrl('sales_rule/promo_quote/newActionHtml/form/rule_actions_fieldset')
                );
        
        $fieldset = $form->addFieldset(
            'rule_actions_fieldset',
            array(
                'legend' => __('Allow using the gift code only if products in cart meet the following conditions (leave blank for all products)')
            )
        )->setRenderer($renderer);
        
        $fieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 'label' => __('Apply To'), 'title' => __('Apply To')]
        )->setRule(
            $model
        )->setRenderer(
            $this->_actions
        );
        
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
