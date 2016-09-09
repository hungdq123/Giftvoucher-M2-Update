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
namespace Magestore\Giftvoucher\Block\Product;

/**
 * Giftvoucher Product Upload Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Upload extends \Magento\Backend\Block\Media\Uploader
{
       
    /**
     * Giftvoucher data
     *
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherData = null;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;


    /**
     * Upload constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_giftvoucherData = $giftvoucherData;
        parent::__construct($context, $fileSize, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId($this->getId() . '_Uploader');
        $this->setTemplate('');
        $this->getConfig()->setUrl($this->getUrl('giftvoucher/index/customUpload'));
        $this->getConfig()->setParams();
        $this->getConfig()->setFileField('image');
        $this->getConfig()->setFilters(array(
            'images' => array(
                'label' => __('Images (.gif, .jpg, .png)'),
                'files' => array('*.gif', '*.jpg', '*.png')
            )
        ));
        $this->getConfig()->setWidth(32);
    }

    public function getDeleteButtonHtml()
    {
        $this->setChild(
            'delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(array(
                    'id'      => '{{id}}-delete',
                    'class'   => 'delete',
                    'type'    => 'button',
                    'label'   => __(''),
                    'onclick' => $this->getJsObjectName() . '.removeFile(\'{{fileId}}\')',
                    'style'     => 'display:none'
                ))
        );
        return $this->getChildHtml('delete_button');
    }
    
    public function getDataMaxSize()
    {
        $dataSize = $this->_giftvoucherData->getInterfaceConfig('upload_max_size');
        if (is_nan($dataSize) || $dataSize <=0) {
            $dataSize = 512000;
        } else {
            $dataSize = $dataSize * 1024;
        }
            
        return $dataSize;
    }
    
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
    
    public function getBaseDir($name = null)
    {
        return $this->_filesystem->getDirectoryRead($name);
    }
}
