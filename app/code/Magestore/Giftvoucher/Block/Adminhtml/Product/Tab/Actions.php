<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Product alerts tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magestore\Giftvoucher\Block\Adminhtml\Product\Tab;

/**
 * Adminhtml Giftvoucher Product Tab Actions Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    
    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_objectManager;
    
    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,    
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_ruleActions = $ruleActions;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $product = $this->getProduct();
        $model = $this->_objectManager->get('Magestore\Giftvoucher\Model\Product');
        if (!$model->getId() && $product->getId()) {
            $model->loadByProduct($product);
        }
        $data = $model->getData();
        $model->setData('conditions', $model->getData('actions_serialized'));
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('giftvoucher_');
        
        $renderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
                ->setTemplate(
                    'Magento_CatalogRule::promo/fieldset.phtml'
                )->setNewChildUrl(
                    $this->getUrl('sales_rule/promo_quote/newActionHtml/form/giftvoucher_actions_fieldset')
                );
        
        $fieldset = $form->addFieldset(
            'actions_fieldset',
            ['legend' => __('Allow using Gift Card only if products in cart meet the following conditions (leave blank for all products)')]
        )->setRenderer(
            $renderer
        );
        
        $fieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 'label' => __('Apply To'), 'title' => __('Apply To'), 'required' => true]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );
        
        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    /**
     * Retrieve product object from object if not from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product') instanceof \Magento\Catalog\Model\Product) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
        return __('Cart Item Conditions ');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Cart Item Conditions ');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        if ($this->_coreRegistry->registry('current_product')->getTypeId() == 'giftvoucher') {
            return true;
        }
        return false;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        if ($this->_coreRegistry->registry('current_product')->getTypeId() == 'giftvoucher') {
            return false;
        }
        return true;
    }
}
