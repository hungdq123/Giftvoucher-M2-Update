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
 * Adminhtml Giftvoucher Product Tab Conditions Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;
    
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
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_conditions = $conditions;
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
        $model->setData('conditions', $model->getData('conditions_serialized'));
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('giftvoucher_');
        $fieldset = $form->addFieldset('description_fieldset', array('legend' => __('Description')));
        
        $fieldset->addField(
            'giftcard_description',
            'editor',
            [
                'name' => 'giftcard_description',
                'label' => __('Describe conditions applied to shopping cart when using this gift code'),
                'title' => __('Describe conditions applied to shopping cart when using this gift code'),
                'style' => 'height:18em',
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );
        
        $renderer = $this->_layout->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
                ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/giftvoucher_conditions_fieldset'));
        
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Allow using Gift Card only if the following shopping cart conditions are met (leave blank for all shopping carts)')]
        )->setRenderer(
            $renderer
        );
        
        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'required' => true]
        )->setRule(
            $model
        )->setRenderer(
            $this->_conditions
        );
        
        $fieldset->addField(
            'hidden',
            'hidden',
            [
                'name' => __('Hidden'),
                'after_element_html' => '
                    <script type="text/javascript">
                        //Add validate data
                        require([
                            "jquery",
                            "jquery/ui",
                            "prototype",
                            "mage/translate"
                        ], function(){
                            if ($("gift_type")) {
                                $("gift_type").on("change", function() {
                                    hidesettingGC();
                                });
                            }
                            if ($("gift_price_type")) {
                                $( "gift_price_type" ).on("change", function() {
                                    hidesettingGC();
                                });
                            }    

                            hidesettingGC = function(){
                                if($("gift_price_type").value==' . \Magestore\Giftvoucher\Model\Giftpricetype::GIFT_PRICE_TYPE_DEFAULT . ')
                                {
                                    $("gift_price").disabled=true;
                                    $("gift_price_type").up("div").down(".note").hide();
                                }
                                else if($("gift_price_type").value==' . \Magestore\Giftvoucher\Model\Giftpricetype::GIFT_PRICE_TYPE_FIX . ')
                                {
                                    $("attribute-gift_price-container").down("label").update("<span>' . __("Gift Card price") . '</span>");
                                    $("gift_price").disabled=false;
                                    $("gift_price_type").up("div").down(".note").hide();
                                    $("gift_price").up("div").down(".note").update("' . __("Enter fixed price(s) corresponding to Gift Card value(s).For example:<br />Type of Gift Card value: Dropdown values<br />Gift Card values : 20,30,40<br />Gift Card price: 15,25,35<br />So customers only have to pay $25 for a $30 Gift card.") . '");
                                    $("attribute-gift_price-container").className +=" required ";
                                }
                                else if($("gift_price_type").value==' . \Magestore\Giftvoucher\Model\Giftpricetype::GIFT_PRICE_TYPE_PERCENT . ')
                                {
                                    $("attribute-gift_price-container").down("label").update("<span>' . __("Percentage") . '</span>");
                                    $("gift_price").disabled=false;
                                    $("gift_price_type").up("div").down(".note").hide();
                                    $("gift_price").up("div").down(".note").update("' . __("Enter percentage(s) of Gift Card value(s) to calculate Gift Card price(s). For example:<br />Type of Gift Card value: Dropdown values<br />Gift Card values: 20,30,40<br />Percentage: 90,90,90<br />So customers only have to pay 90% of Gift Card value, $36 for a $40 Gift card for instance.") . '");
                                        $("attribute-gift_price-container").className +=" required ";
                                }
                                if($("gift_type").value == ' . \Magestore\Giftvoucher\Model\Gifttype::GIFT_TYPE_FIX . '){
                                    $("gift_value").disabled=false;
                                    $("gift_from").disabled=true;
                                    $("gift_to").disabled=true;
                                    $("gift_dropdown").disabled=true;
                                    $("attribute-gift_value-container").show();
                                    $("attribute-gift_from-container").hide();
                                    $("attribute-gift_to-container").hide();
                                    $("attribute-gift_dropdown-container").hide();
                                    $("gift_price_type")[1].show();
                                    $("attribute-gift_value-container").className += " required ";
                                }
                                else if($("gift_type").value == ' . \Magestore\Giftvoucher\Model\Gifttype::GIFT_TYPE_RANGE . '){
                                    $("gift_value").disabled=true;
                                    $("gift_from").disabled=false;
                                    $("gift_to").disabled=false;
                                    $("gift_dropdown").disabled=true;
                                    $("attribute-gift_value-container").hide();
                                    $("attribute-gift_from-container").show();
                                    $("attribute-gift_to-container").show();
                                    $("gift_price_type")[1].hide();
                                    $("attribute-gift_dropdown-container").hide();
                                    if($("gift_price_type").value=="1")
                                    $("gift_price_type")[0].selected=true;
                                    if($("gift_price_type").value=="2")
                                    $("gift_price_type")[2].selected=true;
                                    $("attribute-gift_from-container").className += " required ";
                                    $("attribute-gift_to-container").className += " required ";
                                }
                                else if($("gift_type").value == ' . \Magestore\Giftvoucher\Model\Gifttype::GIFT_TYPE_DROPDOWN . '){
                                    $("gift_value").disabled=true;
                                    $("gift_from").disabled=true;
                                    $("gift_to").disabled=true;
                                    $("gift_dropdown").disabled=false;
                                    $("attribute-gift_value-container").hide();
                                    $("attribute-gift_from-container").hide();
                                    $("attribute-gift_to-container").hide();
                                    $("attribute-gift_dropdown-container").show();
                                    $("gift_price_type")[1].show();
                                    $("attribute-gift_dropdown-container").className += " required ";
                                }
                                if($("gift_price_type").value==' . \Magestore\Giftvoucher\Model\Giftpricetype::GIFT_PRICE_TYPE_DEFAULT . '){
                                    $("attribute-gift_price-container").hide();
                                } else { 
                                    $("attribute-gift_price-container").show();
                                }
                            }
                            jQuery(window).load(function() {
                                $("gift_value").className+=" required-entry required-entry _required validate-greater-than-zero";
                                $("gift_from").className+=" validate-greater-than-zero required-entry required-entry _required validate-gift-range";
                                $("gift_to").className+=" validate-greater-than-zero required-entry required-entry _required validate-zero-or-greater ";
                                $("gift_dropdown").className+=" validate-greater-than-zero required-entry required-entry _required validate-gift-dropdown ";
                                $("gift_price").className+=" required-entry required-entry _required validate-gift-dropdown-price ";
                                hidesettingGC();
                            });
                    });
                    </script>
                    <script>
                        require([
                          "jquery",
                          "mage/backend/validation"
                        ], function(jQuery){
                            error_dropdown ="' . __("Input not correct") . '";
                            error_range ="' . __("Minimum Gift Card value must be lower than maximum Gift Card value.") . '";
                            jQuery.validator.addMethod("validate-gift-range", function(v) {
                                if(parseInt($("gift_from").value)>parseInt($("gift_to").value))
                                return false;
                                else return true;
                            }, error_range);
                            jQuery.validator.addMethod("validate-gift-dropdown", function(v) {
                                parten=/^(\d\.{0,1},{0,1})+$/;
                                return (parten.test($("gift_dropdown").value));
                            }, error_dropdown);
                            jQuery.validator.addMethod("validate-gift-dropdown-price", function(v) {
                                if($("gift_dropdown").value && $("gift_type").value == ' . \Magestore\Giftvoucher\Model\Gifttype::GIFT_TYPE_DROPDOWN . ')
                                    {
                                        cnt_dropdown = $("gift_dropdown").value.split(",").length-1;
                                        if($("gift_price").value) {
                                                cnt_giftprice = $("gift_price").value.split(",").length-1;
                                                if(cnt_dropdown !== cnt_giftprice) {
                                                    return false;
                                                } else {
                                                    return true;
                                                }	
                                        }

                                    } else {
                                            return true;
                                    }
                            }, error_dropdown);
                        })
                    </script>'
            ]
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
        return __('Shopping Cart Conditions ');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Shopping Cart Conditions ');
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
