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


namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher Grid Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Giftvoucher\Model\GiftvoucherFactory
     */
    protected $_giftvoucherFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    ) {
        $this->_giftvoucherFactory = $giftvoucherFactory;
        $this->_datetime = $datetime;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftvoucherGrid');
        $this->setDefaultSort('giftvoucher_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_giftvoucherFactory->create()->getCollection();
        $collection->joinHistory();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'giftvoucher_id',
            array(
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'giftvoucher_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'filter_index' => 'main_table.giftvoucher_id'
            )
        );

        $this->addColumn(
            'gift_code',
            array(
                'header' => __('Gift Code'),
                'index' => 'gift_code',
                'class' => 'xxx'
            )
        );


        $this->addColumn(
            'history_amount',
            array(
                'header' => __('Initial Value'),
                'index' => 'history_amount',
                'type' => 'currency',
                'currency' => 'history_currency',
                'filter_index' => 'history.amount',
                'rate'  => '1'
            )
        );
        
        $this->addColumn(
            'balance',
            array(
                'header' => __('Current Balance'),
                'index' => 'balance',
                'type' => 'currency',
                'currency' => 'currency',
                'filter_index' => 'main_table.balance',
                'rate'  => '1'
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options'   => \Magestore\Giftvoucher\Model\Status::getOptionArray(),
                'filter_index' => 'main_table.status'
            )
        );
        
        $this->addColumn('customer_name', array(
            'header' => __('Customer'),
            'align' => 'left',
            'index' => 'customer_name',
            'filter_index' => 'main_table.customer_name'
        ));
        
        $this->addColumn('order_increment_id', array(
            'header' => __('Order'),
            'align' => 'left',
            'index' => 'order_increment_id',
            'filter_index' => 'history.order_increment_id'
        ));
        
        $this->addColumn('recipient_name', array(
            'header' => __('Recipient'),
            'align' => 'left',
            'index' => 'recipient_name',
            'filter_index' => 'main_table.recipient_name'
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
        
        $this->addColumn('is_sent', array(
            'header' => __('Send To Customer'),
            'align' => 'left',
            'index' => 'is_sent',
            'type' => 'options',
            'options' => \Magestore\Giftvoucher\Model\Status::getOptionEmail(),
            'filter_index' => 'main_table.is_sent'
        ));
        
        $this->addColumn('extra_content', array(
            'header' => __('Action Created by'),
            'align' => 'left',
            'index' => 'extra_content',
            'filter_index' => 'history.extra_content'
        ));

        $this->addColumn(
            'edit',
            array(
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array(
                            'base' => '*/*/edit',
                            'params' => array('store' => $this->getRequest()->getParam('store'))
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            )
        );
        
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('giftvoucher_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete', ['_current' => true]),
                'confirm' => __('Are you sure you want to delete the selected Giftcode(s)?')
            ]
        );
        
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => \Magestore\Giftvoucher\Model\Status::getOptionArray()
                    ]
                ]
            ]
        );
        
        $this->getMassactionBlock()->addItem(
            'sendemail',
            [
                'label' => __('Send Email'),
                'url' => $this->getUrl('*/*/massEmail', ['_current' => true]),
                'confirm' => __('Are you sure you want to send email for the selected Giftcode(s)?')
            ]
        );
        
        $this->getMassactionBlock()->addItem(
            'printgiftcode',
            [
                'label' => __('Print Gift Code'),
                'url' => $this->getUrl('*/*/massPrint', ['_current' => true]),
                'confirm' => __('Are you sure you want to print the selected Giftcode(s)?'),
            ]
        );

        return $this;
    }

    private function _prepareExportGrid()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $this->addColumn('currency', array('index' => 'currency'));
        $this->addColumn('customer_id', array('index' => 'customer_id'));
        $this->addColumn('customer_email', array('index' => 'customer_email'));
        $this->addColumn('recipient_email', array('index' => 'recipient_email'));
        $this->addColumn('recipient_address', array('index' => 'recipient_address'));
        $this->addColumn('message', array('index' => 'message'));
        $this->removeColumn('edit');
        return $this;
    }

    public function getCsv()
    {
        $csv = '';
        $this->_prepareExportGrid();
        $data = array();
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getIndex() . '"';
            }
        }

        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        array('"', '\\', chr(13), chr(10)),
                        array('""', '\\\\', '', '\n'),
                        $item->getData($column->getIndex())
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = array();
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        array('"', '\\'),
                        array('""', '\\\\'),
                        $column->getRowFieldExport($this->getTotals())
                    ) . '"';
                }
            }
            $csv.= implode(',', $data) . "\n";
        }

        return $csv;
    }

    public function getXml()
    {
        $this->_prepareExportGrid();
        $indexes = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $indexes[] = $column->getIndex();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<items>';
        foreach ($this->getCollection() as $item) {
            $xml .= $item->toXml($indexes);
        }
        $xml .= '</items>';
        return $xml;
    }

    public function getExcel()
    {
        $this->_prepareExportGrid();
        $headers = [];
        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = $column->getIndex();
            }
        }
        $data[] = $headers;

        foreach ($this->getCollection() as $item) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $item->getData($column->getIndex());
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals()) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getTotals());
                }
            }
            $data[] = $row;
        }

        $arrayInterator = $this->_objectManager->create('ArrayIterator', ['array' => $data]);

        $convert = $this->_objectManager->create('Magento\Framework\Convert\Excel', ['iterator' => $arrayInterator]);

        //$convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        return $convert->convert('single_sheet');
    }


    public function getGridUrl()
    {
        return $this->getUrl('giftvoucheradmin/*/grid', array('_current' => true));
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'giftvoucheradmin/*/edit',
            array('store' => $this->getRequest()->getParam('store'), 'id' => $row->getId())
        );
    }
    
    public function filterByGiftvoucherStoreId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (isset($value) && $value) {
            $collection->addFieldToFilter("main_table.store_id", $value);
        }
    }
}
