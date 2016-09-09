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
 * Adminhtml Giftvoucher Edit Tab Abstractgiftvoucher Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Abstractgiftvoucher extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Gifttemplate
     */
    protected $_giftTemplate;
    
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;
    
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;
    
    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_actions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * Abstractgiftvoucher constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magestore\Giftvoucher\Model\Gifttemplate $giftTemplate
     * @param \Magestore\Giftvoucher\Helper\Data $helperData
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Rule\Block\Actions $actions
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\Currency $currency,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magestore\Giftvoucher\Model\Gifttemplate $giftTemplate,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Rule\Block\Actions $actions,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = array()
    ) {
        $this->_giftvoucher = $giftvoucher;
        $this->_giftTemplate = $giftTemplate;
        $this->_helperData = $helperData;
        $this->_systemStore = $systemStore;
        $this->_currency = $currency;
        $this->_locale = $locale;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_conditions = $conditions;
        $this->_actions = $actions;
        $this->_yesno = $yesno;
        
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Gift Code Information');
    }
    
    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Gift Code Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Get Gift Card template options
     *
     * @return array
     */
    public function getGiftTemplate()
    {
        /**
         * gifttemplate
         */
        $dataTemp = $this->_giftTemplate->getCollection();
        $option = array();
        $option[] = array('value' => '',
            'label' => __('Please select a template')
        );
        foreach ($dataTemp as $template) {
            $option[] = array('value' => $template->getGiftcardTemplateId(),
                'label' => $template->getTemplateName()
            );
        }
        
        return $option;
    }
}
