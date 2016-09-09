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
 * Adminhtml Giftvoucher Generategiftcard Edit Tab Giftcodelist Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftcodelist extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;
    
    /**
     * @var \Magestore\Giftvoucher\Model\GiftvoucherFactory
     */
    protected $_giftvoucherFactory;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        array $data = array()
    ) {
        $this->_giftvoucherFactory = $giftvoucherFactory;
        $this->_giftvoucher = $giftvoucher;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftvoucherGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_giftvoucherFactory->create()->getCollection()
            ->addFieldToFilter('template_id', $id)
            ->joinHistory();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('giftvoucher_id', array(
            'header' => __('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'giftvoucher_id',
            'filter_index' => 'main_table.giftvoucher_id'
        ));

        $this->addColumn('gift_code', array(
            'header' => __('Gift Card Code'),
            'align' => 'left',
            'index' => 'gift_code',
            'filter_index' => 'main_table.gift_code'
        ));

        $this->addColumn('history_amount', array(
            'header' => __('Initial value'),
            'align' => 'left',
            'index' => 'history_amount',
            'type' => 'currency',
            'currency' => 'history_currency',
            'filter_index' => 'history.amount',
            'rate' => '1'
        ));

        $this->addColumn('balance', array(
            'header' => __('Current balance'),
            'align' => 'left',
            'index' => 'balance',
            'type' => 'currency',
            'currency' => 'currency',
            'filter_index' => 'main_table.balance',
            'rate' => '1'
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => \Magestore\Giftvoucher\Model\Status::getOptionArray(),
            'filter_index' => 'main_table.status'
        ));


        $this->addColumn('created_at', array(
            'header' => __('Created at'),
            'align' => 'left',
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'history.created_at'
        ));

        $this->addColumn('expired_at', array(
            'header' => __('Expired at'),
            'align' => 'left',
            'index' => 'expired_at',
            'type' => 'datetime',
            'filter_index' => 'main_table.expired_at'
        ));

        $this->addColumn('store_id', array(
            'header' => __('Store view'),
            'align' => 'left',
            'index' => 'store_id',
            'type' => 'store',
            'store_all' => true,
            'store_view' => true,
            'filter_index' => 'main_table.store_id',
            'skipEmptyStoresLabel' => true,
            'filter_condition_callback' => array($this, 'filterByGiftvoucherStoreId')
        ));
        
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '70px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/giftvoucher/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportGiftCodeCsv', __('CSV'));
        $this->addExportType('*/*/exportGiftCodeXml', __('XML'));
        $this->addExportType('*/*/exportGiftCodePdf', __('PDF'));

        return parent::_prepareColumns();
    }
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

    public function getRowUrl($row)
    {
        return $this->getUrl('*/giftvoucher/edit', array('id' => $row->getId()));
    }

    public function filterByGiftvoucherStoreId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (isset($value) && $value) {
            $collection->addFieldToFilter("main_table.store_id", $value);
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/giftcodelist', array(
                    '_current' => true,
        ));
    }
}
