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

namespace Magestore\Giftvoucher\Block\Adminhtml\Generategiftcard\Edit\Tab;

/**
 * Adminhtml Giftvoucher Generategiftcard Edit Tab Conditions Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    
    protected $_localeDate;
       
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = array()
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */

        if ($this->_coreRegistry->registry('generategiftcard_data')) {
            $model = $this->_coreRegistry->registry('generategiftcard_data');
        } else {
            $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Generategiftcard');
        }
        $data = $model->getData();

        $model->setData('conditions', $model->getData('conditions_serialized'));

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $url = $this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/giftvoucher_conditions_fieldset');
        $renderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($url);
        

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );


        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer($this->_conditions);
         
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Gift Code Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Gift Code Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
