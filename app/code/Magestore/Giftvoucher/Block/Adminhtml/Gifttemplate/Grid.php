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

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate;

/**
 * Adminhtml Giftvoucher Gifttemplate Grid Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Giftvoucher\Model\ResourceModel\Gifttemplate\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Gifttemplate\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\Giftvoucher\Model\Gifttemplate $template
     * @param \Magestore\Giftvoucher\Model\Gifttemplate\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Giftvoucher\Model\Gifttemplate $template,
        \Magestore\Giftvoucher\Model\Gifttemplate\Status $status,
        array $data = array()
    ) {
        $this->_collectionFactory = $template;
        $this->_status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gifttemplateGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('giftcard_template_id', array(
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'giftcard_template_id'
        ));
        $this->addColumn('template_name', array(
            'header'    =>  __('Template Name'),
            'type'      =>  'text',
            'index'     =>  'template_name'
        ));
        $this->addColumn('design_pattern', array(
            'header'    =>  __('Template Design'),
            'type'      =>  'options',
            'index'     =>  'design_pattern',
            'options'   =>  \Magestore\Giftvoucher\Model\Gifttemplate\Type::getOptionArray()
        ));
//         $this->addColumn('caption', array(
//             'header'    =>  __('Title'),
//             'type'      =>  'text',
//             'index'     =>  'caption'
//         ));
        $this->addColumn('status', array(
            'header'    =>  __('Status'),
            'type'      =>  'options',
            'index'     =>  'status',
            'options'   =>  \Magestore\Giftvoucher\Model\Gifttemplate\Status::getOptionArray()
        ));
        
        $this->addColumn('action', array(
            'header'    =>  __('Action'),
            'type'      =>  'action',
            'getter' => 'getId',
            'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array(
                            'base' => '*/*/edit'
                        ),
                        'field' => 'id'
                    )
                ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action'
        ));
        parent::_prepareColumns();
    }
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('giftcard_template_id');
        $this->getMassactionBlock()->setFormFieldName('giftcard_template_ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, ['label' => '', 'value' => '']);
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
                        'values' => $statuses
                    ]
                ]
            ]
        );
        
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('giftvoucheradmin/*/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'giftvoucheradmin/*/edit',
            array(
                'store' => $this->getRequest()->getParam('store'),
                'id' => $row->getId()
            )
        );
    }
}
