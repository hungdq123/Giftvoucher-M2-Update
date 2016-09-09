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
namespace Magestore\Giftvoucher\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Account block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Account extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Helper\View
     */
    public $viewHelper;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    public $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    public $httpContext;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $datetime;
    
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    public $urlDecoder;
    
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Account constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $accountManagement
     * @param \Magento\Customer\Helper\View $viewHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Url\DecoderInterface $decode
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $accountManagement,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Url\DecoderInterface $decode,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerRepository = $accountManagement;
        $this->viewHelper = $viewHelper;
        $this->httpContext = $httpContext;
        $this->currentCustomer = $currentCustomer;
        $this->objectManager = $objectManager;
        $this->_isScopePrivate = true;
        $this->datetime = $datetime;
        $this->urlDecoder = $decode;
        $this->_imageFactory = $imageFactory;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        return $this->getCustomerSessionModel()->isLoggedIn();
    }

    /**
     * Return the full name of the customer currently logged in
     *
     * @return string|null
     */
    public function getCurrentCustomerName()
    {
        try {
            $customer = $this->customerRepository->getById($this->currentCustomer->getCustomerId());
            return $this->escapeHtml($this->viewHelper->getCustomerName($customer));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    public function getCustomerSessionModel()
    {
        return $this->objectManager->create('Magento\Customer\Model\Session');
    }

    public function getMediaPath()
    {
        return $this->objectManager->get('Magento\Framework\Filesystem')->getUri(DirectoryList::MEDIA);
    }

    public function getBaseUrl()
    {
        return $this->objectManager->get('Magento\Framework\Url')->getBaseUrl();
    }

    public function getCustomer()
    {
        return $this->getCustomerSessionModel()->getCustomer();
    }

    public function getCustomerData()
    {
        if (!$this->getCustomerSessionModel()->getCustomer()) {
            return null;
        }
        return $this->getCustomerSessionModel()->getCustomer()->getCustomerData();
    }

    public function getCustomerDataObject()
    {
        if (!$this->getCustomerSessionModel()->getCustomer()) {
            return null;
        }
        return $this->getCustomerSessionModel()->getCustomer()->getCustomerDataObject();
    }

    public function getCustomerId()
    {
        return $this->getCustomerSessionModel()->getCustomer()->getId();
    }

    public function getCustomerFirstname()
    {
        return $this->getCustomerSessionModel()->getCustomer()->getFirstname();
    }

    public function getCustomerLastname()
    {
        return $this->getCustomerSessionModel()->getCustomer()->getLastname();
    }

    public function getCustomerEmail()
    {
        return $this->getCustomerSessionModel()->getCustomer()->getEmail();
    }

    public function getCurrencyModel()
    {
        return $this->objectManager->create('Magento\Framework\Locale\Currency');
    }

    public function getDefaultCurrency()
    {
        return $this->getCurrencyModel()->getDefaultCurrency();
    }

    public function getCurrencyFactory()
    {
        return $this->objectManager->create('Magento\Directory\Model\CurrencyFactory');
    }

    public function getCurrency()
    {
        $currencyCode = $this->getDefaultCurrency();
        $currency = $this->getCurrencyFactory()->create();
        $currency->load($currencyCode);
        return $currency;
    }

    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }

    public function getHelper()
    {
        return $this->objectManager->create('Magestore\Giftvoucher\Helper\Data');
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getModel($modelName)
    {
        return $this->objectManager->create($modelName);
    }

    public function getSingleton($modelName)
    {
        return $this->objectManager->get($modelName);
    }

    public function getMediaDirPath($file)
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($file);
    }

    public function getSkinUrl($file)
    {
        return $this->getViewFileUrl('Magestore_Giftvoucher::' . $file);
    }

    public function getImage()
    {
        return $this->_imageFactory->create();
    }

    /**
     * convert amount from base currency to current currency
     *
     * @param $value
     * @param bool $format
     * @param null $currency
     * @return float|string
     */
    public function converCurrency($value, $format = true, $currency = null)
    {
        return $format ? $this->_priceCurrency->convertAndFormat(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore(),
            $currency
        ) : $this->_priceCurrency->convert($value, $this->getStore(), $currency);
    }

    public function formatCurrency($amount, $currency = null)
    {
        return $this->getHelper()->getCurrencyFormat($amount, $currency);
    }
}
