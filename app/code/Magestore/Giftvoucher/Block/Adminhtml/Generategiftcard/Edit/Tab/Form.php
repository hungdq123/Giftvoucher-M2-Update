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
 * Adminhtml Giftvoucher Generategiftcard Edit Tab Form Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_currencyFactory = $currencyFactory;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_objectManager = $objectManager;
        $this->_generategiftcard = $generategiftcard;
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
        
        $form = $this->_formFactory->create();
        
        $isElementDisabled = false;
        
        $fieldset = $form->addFieldset('generategiftcard_form', array('legend' =>__('General Information')));
         $data = $this->_coreRegistry->registry('generategiftcard_data');
        if ($this->_backendSession->getGenerategiftcardData()) {
            $model = $this->_backendSession->getGenerategiftcardData();
            $this->_backendSession->setGenerategiftcardData(null);
        } elseif ($this->_coreRegistry->registry('generategiftcard_data')) {
            $model = $this->_coreRegistry->registry('generategiftcard_data');
        } else {
            $model = $this->_generategiftcard;
        }
         
        if (isset($model) && $model->getId()) {
            $fieldset->addField('template_id', 'hidden', array('name' => 'template_id'));
        }
        
        $disabled = false;
        $style = 'opacity:1;background-color:#fff';
        if (isset($data['is_generated']) && $data['is_generated']) {
            $disabled = true;
            $style = '';
        }
        $fieldset->addField('template_name', 'text', array(
            'label' =>__('Pattern name '),
            'required' => true,
            'name' => 'template_name',
            'disabled' => $disabled,
        ));
        
        $note = __('Pattern examples:<br/><strong>[A.8] : 8 alpha<br/>[N.4] : 4 numeric<br/>[AN.6] : 6 alphanumeric'
            . '<br/>GIFT-[A.4]-[AN.6] : GIFT-ADFA-12NF0O</strong>');
        $fieldset->addField('pattern', 'text', array(
            'label' => __('Gift code pattern '),
            'required' => true,
            'name' => 'pattern',
            'value' => $this->_giftvoucherHelper->getGeneralConfig('pattern'),
            'note' => $note,
            'disabled' => $disabled,
        ));

        $fieldset->addField('balance', 'text', array(
            'label' => __('Gift code value'),
            'required' => true,
            'name' => 'balance',
            'disabled' => $disabled,
            'class' => 'validate-number validate-greater-than-zero',
        ));

        $fieldset->addField('currency', 'select', array(
            'label' => __('Currency'),
            'required' => false,
            'name' => 'currency',
            'value' => $this->_storeManager->getStore()->getDefaultCurrencyCode(),
            'values' => $this->_giftvoucherHelper->getAllowedCurrencies(),
            'disabled' => $disabled,
        ));

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $fieldset->addField('expired_at', 'date', array(
            'label' =>__('Expired on'),
            'required' => false,
            'name' => 'expired_at',
            'input_format' => 'yyyy-MM-dd',
            'date_format' => 'MM/dd/yyyy',
            'readonly' => true,
            'disabled' => $disabled,
            'style' => $style,
        ));

        $template = $this->getGiftTemplate();
        if ($template && count($template)) {
            $fieldset->addField('giftcard_template_id', 'select', array(
                'label' => __('Template'),
                'name' => 'giftcard_template_id',
                'values' => $template,
                'required' => true,
                'onchange' => 'loadImageTemplate(this.value)',
                'disabled' => $disabled,
                'after_element_html' => (isset($data['giftcard_template_image'])
                    && isset($data['giftcard_template_id'])) ?
                '<script> window.onload = function(){loadImageTemplate(\'' . $data['giftcard_template_id'] . '\',\''
                . $data['giftcard_template_image'] . '\',\''.$data['is_generated'].'\');};</script>' : '',
            ));
            $fieldset->addField('list_images', 'note', array(
                'label' => __('Template image'),
                'name' => 'list_images',
                'text' => sprintf(''),
            ));
            $fieldset->addField('giftcard_template_image', 'hidden', array(
                'name' => 'giftcard_template_image',
            ));
        }
        
        
        $fieldset->addField('amount', 'text', array(
            'label' => __('Gift code Qty'),
            'required' => true,
            'name' => 'amount',
            'disabled' => $disabled,
            'class' => 'validate-number validate-greater-than-zero',
        ));


        $fieldset->addField('store_id', 'select', array(
            'label' => __('Store view'),
            'name' => 'store_id',
            'required' => false,
            'disabled' => $disabled,
            'values' => $this->_systemStore->getStoreValuesForForm(false, true)
        ));
        
        $form->setValues($data);
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
    public function getGiftTemplate()
    {
        /**
         * gifttemplate
         */
        
        $dataTemp = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')->getCollection();
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
