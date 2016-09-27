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
namespace Magestore\Giftvoucher\Block\Adminhtml\Generategiftcard;

/**
 * Adminhtml Giftvoucher Generategiftcard Grid Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;
 
    /**
     * @var \Magestore\Giftvoucher\Model\GenerategiftcardFactory
     */
    protected $_generategiftcardFactory;
 
    /**
     * @var \SR\Weblog\Model\Status
     */
    protected $_status;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\GenerategiftcardFactory $generategiftcardFactory
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\GenerategiftcardFactory $generategiftcardFactory,
        \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard,
        array $data = array()
    ) {
        $this->_generategiftcardFactory = $generategiftcardFactory;
        $this->_generategiftcard = $generategiftcard;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('generategiftcardGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_generategiftcardFactory->create()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'template_id',
            array(
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'template_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );
        
        $this->addColumn(
            'template_name',
            array(
                'header' => __('Pattern Name'),
                'index' => 'template_name',
                'class' => 'xxx'
            )
        );
 
        $this->addColumn(
            'pattern',
            array(
                'header' => __('Pattern'),
                'index' => 'pattern'
            )
        );
 
        $this->addColumn(
            'balance',
            array(
                'header' => __('Balance'),
                'align' => 'left',
                'index' => 'balance',
                'type' => 'currency',
                'currency' => 'currency',
                'rate' => '1'
            )
        );
       
        $this->addColumn(
            'currency',
            array(
                'header' => __('Currency'),
                'align' => 'left',
                'index' => 'currency',
            )
        );
 
        $this->addColumn(
            'amount',
            array(
                'header' => __('Gift Code Qty'),
                'align' => 'left',
                'index' => 'amount',
                'type' => 'number'
            )
        );

        $this->addColumn(
            'store_id',
            array(
                'header' => __('Store view'),
                'align' => 'left',
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'filter_index' => 'main_table.store_id',
                'skipEmptyStoresLabel' => true,
                'filter_condition_callback' => array($this, 'filterByGiftvoucherStoreId')
            )
        );
 
        $this->addColumn(
            'action',
            array(
                'header' => __('Action'),
                'width' => '70px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            )
        );
 
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        
        
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
 
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('template_id');
        $this->getMassactionBlock()->setFormFieldName('template');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?')
        ));

        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function filterByGiftvoucherStoreId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (isset($value) && $value) {
            $collection->addFieldToFilter("main_table.store_id", $value);
        }
    }
}
