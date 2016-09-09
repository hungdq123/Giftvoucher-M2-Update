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
 * Adminhtml Giftvoucher Customer Tab History Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class History extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Giftvoucher\Model\Credithistory
     */
    protected $_credithistory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magestore\Giftvoucher\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \Magestore\Giftvoucher\Model\Creditaction
     */
    protected $_creditaction;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magestore\Giftvoucher\Model\CredithistoryFactory $credithistoryFactory
     * @param \Magestore\Giftvoucher\Model\Credithistory $credithistory
     * @param \Magestore\Giftvoucher\Model\Creditaction $creditaction
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\Giftvoucher\Model\CredithistoryFactory $credithistoryFactory,
        \Magestore\Giftvoucher\Model\Credithistory $credithistory,
        \Magestore\Giftvoucher\Model\Creditaction $creditaction,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_credithistory = $credithistory;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_creditaction = $creditaction;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {

        parent::_construct();
        $this->setId('historyGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        if (!$customerId) {
            $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        }
        $collection = $this->_credithistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('history_id', array(
            'header' => __('ID'),
            'align' => 'left',
            'width' => '50px',
            'type' => 'number',
            'index' => 'history_id',
        ));
        $this->addColumn('action', array(
            'header' => __('Action'),
            'align' => 'left',
            'index' => 'action',
            'type' => 'options',
            'options' => $this->_creditaction->getOptionArray(),
        ));

        $this->addColumn('balance_change', array(
            'header' => __('Balance Change'),
            'align' => 'left',
            'index' => 'balance_change',
            'type' => 'currency',
            'currency' => 'currency',
        ));
        $this->addColumn('giftcard_code', array(
            'header' => __('Gift Card Code'),
            'align' => 'left',
            'index' => 'giftcard_code',
        ));
        $this->addColumn('order_number', array(
            'header' => __('Order'),
            'align' => 'left',
            'index' => 'order_number',
            'renderer' => 'Magestore\Giftvoucher\Block\Adminhtml\Customer\Tab\Renderer',
        ));
        $this->addColumn('currency_balance', array(
            'header' => __('Current Balance'),
            'align' => 'left',
            'index' => 'currency_balance',
            'type' => 'currency',
            'currency' => 'currency',
        ));
        $this->addColumn('created_date', array(
            'header' => __('Created Time'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'created_date',
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('giftvoucheradmin/customer/balancehistorygrid', array(
                '_current' => true,
                'customer_id' => $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID),
        ));
    }
}
