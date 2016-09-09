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
 * Adminhtml Giftvoucher Edit Tab History Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class History extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Giftvoucher\Model\HistoryFactory
     */
    protected $_giftvoucherFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\HistoryFactory $giftvoucherFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\HistoryFactory $giftvoucherFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        array $data = array()
    ) {
        $this->_historyFactory = $giftvoucherFactory;
        $this->_datetime = $datetime;
        parent::__construct($context, $backendHelper, $data);
    }
    
    public function _construct()
    {
        parent::_construct();
        $this->setId('historyGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_historyFactory->create()
                ->getCollection()
                ->addFieldToFilter('giftvoucher_id', $this->getGiftvoucher());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header' => __('Created at'),
            'align' => 'left',
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '160px',
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'align' => 'left',
            'index' => 'action',
            'type' => 'options',
            'options' => \Magestore\Giftvoucher\Model\Actions::getOptionArray(),
        ));

        $this->addColumn('amount', array(
            'header' => __('Value'),
            'align' => 'left',
            'index' => 'amount',
            'type' => 'currency',
            'currency' => 'currency',
            'rate'  => 1
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => \Magestore\Giftvoucher\Model\Status::getOptionArray(),
        ));

        $this->addColumn('order_increment_id', array(
            'header' => __('Order'),
            'align' => 'left',
            'index' => 'order_increment_id',
        ));

        $this->addColumn('comments', array(
            'header' => __('Comments'),
            'align' => 'left',
            'index' => 'comments',
        ));

        $this->addColumn('extra_content', array(
            'header' => __('Action Created by'),
            'align' => 'left',
            'index' => 'extra_content',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/historygrid', array(
                    '_current' => true,
                    'id' => $this->getGiftvoucher(),
        ));
    }
}
