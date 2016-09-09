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

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifthistory;

/**
 * Adminhtml Giftvoucher Gifthistory Grid Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    /**
     * @var \Magestore\Giftvoucher\Model\ResourceModel\History\Collection
     */
    protected $_history;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\History $history
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\History $history,
        array $data = array()
    ) {
        $this->_history = $history;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gifthistoryGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->_history->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('history_id', array(
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'history_id'
        ));
        $this->addColumn('created_at', array(
            'header'    =>  'Created At',
            'type'      =>  'datetime',
            'index'     =>  'created_at'
        ));
        $this->addColumn('action', array(
            'header'    =>  'Action',
            'type'      =>  'options',
            'index'     =>  'action',
            'options'   => \Magestore\Giftvoucher\Model\Actions::getOptionArray()
        ));
        $this->addColumn('amount', array(
            'header'    =>  'Value',
            'type'      =>  'currency',
            'index'     =>  'amount',
            'align'     =>  'right',
            'currency'  =>  'currency',
            'rate' => 1//$this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
        ));
        $this->addColumn('status', array(
            'header'    =>  'Status',
            'index'     =>  'status',
            'type'      =>  'options',
            'options'   =>  \Magestore\Giftvoucher\Model\Status::getOptionArray()
        ));
        $this->addColumn('order_increament_id', array(
            'header'    =>  'Order',
            'index'     =>  'order_increment_id',
            'type'      =>  'text'
        ));
        $this->addColumn('extra_content', array(
            'header'    =>  'Create By',
            'index'     =>  'extra_content',
            'type'      =>  'text'
        ));
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));
        parent::_prepareColumns();
    }
}
