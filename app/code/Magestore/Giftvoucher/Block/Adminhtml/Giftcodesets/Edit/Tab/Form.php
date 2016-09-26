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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftcodesets\Edit\Tab;

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

        $fieldset = $form->addFieldset('giftcodesets_form', array('legend' =>__('Import Gift Code Sets')));

        $data = $this->_coreRegistry->registry('giftcodesets_data');
        if ($this->_backendSession->getGenerategiftcardData()) {
            $model = $this->_backendSession->getGenerategiftcardData();
            $this->_backendSession->setGenerategiftcardData(null);
        } elseif ($this->_coreRegistry->registry('giftcodesets_data')) {
            $model = $this->_coreRegistry->registry('giftcodesets_data');
        } else {
            $model = $this->_generategiftcard;
        }

        if (isset($model) && $model->getId()) {
            $fieldset->addField('set_id', 'hidden', array('name' => 'set_id'));
        }

        $disabled = false;
        $style = 'opacity:1;background-color:#fff';

        $fieldset->addField('set_name', 'text', array(
            'label' =>__('Sets Name '),
            'required' => true,
            'name' => 'template_name',
            'disabled' => $disabled,
        ));


        $fieldset->addField('import_code','file',array(
            'label' => __('Import Gift Code Sets'),
            'name' => 'import_code',
            'required' => false,

        ));
        $notes=  __('Status of Used : 1-Yes,2-No');
        $fieldset->addField('sample', 'note', array(
            'label' => __('Download Sample CSV File'),
            'note' =>$notes,
            'text' => '<a href="' .
                $this->getUrl('*/*/downloadSampleSets') .
                '" title="' .
                __('Download Sample Gift Code Set CSV File') .
                '">import_giftcodesets_sample.csv</a>'
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
