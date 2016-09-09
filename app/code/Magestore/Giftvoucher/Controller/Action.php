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

namespace Magestore\Giftvoucher\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Action extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->priceCurrency = $priceCurrency;
    }
    
    public function execute()
    {
        
    }
    
    protected function getResultRawFactory()
    {
        return $this->_objectManager->create('Magento\Framework\Controller\Result\RawFactory');
    }

    protected function getResultJsonFactory()
    {
        return $this->_objectManager->create('Magento\Framework\Controller\Result\JsonFactory')->create();
    }

    protected function getResultJson()
    {
        return $this->_objectManager->create('Magento\Framework\Controller\Result\Json');
    }

    protected function getForwardFactory()
    {
        return $this->_objectManager->create('Magento\Framework\Controller\Result\Forward');
    }

    protected function getRedirectFactory()
    {
        return $this->_objectManager->create('Magento\Framework\Controller\Result\Redirect');
    }

    protected function getPageFactory()
    {
        return $this->_objectManager->create('Magento\Framework\View\Result\PageFactory')->create();
    }

    protected function getLayoutFactory()
    {
        return $this->_objectManager->create('Magento\Framework\View\Result\LayoutFactory')->create();
    }

    protected function getCusomterSessionModel()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    protected function getHttpContextObj()
    {
        return $this->_objectManager->create('Magento\Framework\App\Http\Context');
    }
    
    protected function getHelperData()
    {
        return $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data');
    }

    protected function initFunction($title = '')
    {
        if ($this->customerLoggedIn()) {
            $resultPageFactory = $this->getPageFactory();
            $resultPageFactory->getConfig()->getTitle()->set($title);
            return $resultPageFactory;
        } else {
            $resultRedirectFactory = $this->getRedirectFactory()
                ->setPath('customer/account/login', array('_secure' => true));
            return $resultRedirectFactory;
        }
    }
    
    protected function customerLoggedIn()
    {
        return $this->getCusomterSessionModel()->isLoggedIn();
    }
    
    protected function getCustomer()
    {
        return $this->getCusomterSessionModel()->getCustomer();
    }
    
    public function getModel($modelName)
    {
        return $this->_objectManager->create($modelName);
    }
    
    public function getSingleton($modelName)
    {
        return $this->_objectManager->get($modelName);
    }
    
    public function getHelper()
    {
        return $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data');
    }
    
    public function getGiftvoucherModel()
    {
        return $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
    }
    
    public function getFileSystem()
    {
        return $this->_objectManager->create('\Magento\Framework\Filesystem');
    }
}
