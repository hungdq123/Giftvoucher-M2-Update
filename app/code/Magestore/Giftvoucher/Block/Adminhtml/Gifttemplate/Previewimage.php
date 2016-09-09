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
 * Adminhtml Giftvoucher Gifttemplate Preview Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Previewimage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    public $logo;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * Previewimage constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->logo = $logo;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function getGifttemplate()
    {
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        if ($registryObject->registry('gifttemplate_data')) {
            return $registryObject->registry('gifttemplate_data');
        }
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate');
        $model->load($id);
        return $model;
    }
    
    public function getDesignPattern()
    {
        $giftTemplate = $this->getGifttemplate();
        return $giftTemplate->getDesignPattern();
    }
    
    public function getHelper($name = null)
    {
        if (is_null($name)) {
            $name = 'Magestore\Giftvoucher\Helper\Data';
        }
        return $this->_objectManager->get($name);
    }
    
    public function getModel($name)
    {
        return $this->_objectManager->create($name);
    }
    
    public function getSingleton($name)
    {
        return $this->_objectManager->get($name);
    }
    
    public function getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }
    
    public function getDefaultPrintLogo()
    {
        return $this->logo->getLogoSrc();
    }
    
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
