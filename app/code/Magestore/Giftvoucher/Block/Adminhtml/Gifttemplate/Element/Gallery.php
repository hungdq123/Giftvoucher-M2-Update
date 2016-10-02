<?php

/**
 * Magestore.
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

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Element;

/**
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */

class Gallery extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Registry object.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Model Url instance.
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magestore\Giftvoucher\Model\ResourceModel\Image\CollectionFactory
     */
    protected $_imageCollectionFactory;

    /**
     * @var \Magestore\Giftvoucher\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magestore\Giftvoucher\Model\SystemConfig
     */
    protected $_systemConfig;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        \Magento\Framework\File\Size $fileConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_backendUrl = $backendUrlFactory->create();
        $this->_fileConfig = $fileConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * Get label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Images');
    }

    /**
     * get images json data of store.
     *
     * @return string
     */
    public function getImageJsonData()
    {
        $imageArray = [];
        return $this->_jsonHelper->jsonEncode($imageArray);
    }

    /**
     * Get url to upload files.
     *
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->_backendUrl->getUrl('giftvoucheradmin/gifttemplate/upload');
    }
}
