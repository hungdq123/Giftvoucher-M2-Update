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
namespace Magestore\Giftvoucher\Block\Adminhtml\Customer\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml Giftvoucher Customer Tab Credit Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Credit extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_customerCredit;

    /**
     * Credit constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Giftvoucher\Model\Credit $creditModel
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
        \Magestore\Giftvoucher\Model\Credit $creditModel,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_currencyFactory = $currencyFactory;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_objectManager = $objectManager;
        $this->_creditModel = $creditModel;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('creditgiftcard_fieldset', array('legend' =>__('Gift Card Credit Information')));

        $fieldset->addField('credit_balance', 'note', array(
            'label' => __('Balance'),
            'title' => __('Balance'),
            'text' => $this->getBalanceCredit(),
        ));
        $fieldset->addField('change_balance', 'text', array(
            'label' => __('Change Balance'),
            'title' => __('Change Balance'),
            'name' => 'change_balance',
            'data-form-part' => $this->getData('target_form'),
            'note' => __('Add or subtract customer\'s balance. For ex: 99 or -99.'),
        ));

        $form->addFieldset('balance_history_fieldset', array(
        'legend' =>__('Balance History')))->setRenderer($this->_layout
            ->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
            ->setTemplate('Magestore_Giftvoucher::giftvoucher/balancehistory.phtml'));


        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getCredit()
    {
        if (is_null($this->_customerCredit)) {
            $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            $this->_customerCredit = $this->_creditModel->getCreditByCustomerId($customerId);
        }
        return $this->_customerCredit;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Gift Card Credit');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Gift Card Credit');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return true;
        }
        return false;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return false;
        }
        return true;
    }


    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Returns formatted Gift Card credit balance
     *
     * @return string
     */
    public function getBalanceCredit()
    {
        $currency = $this->_objectManager->get('Magento\Directory\Model\Currency')
            ->load($this->getCredit()->getCurrency());
        return $currency->format($this->getCredit()->getBalance());
    }
}
