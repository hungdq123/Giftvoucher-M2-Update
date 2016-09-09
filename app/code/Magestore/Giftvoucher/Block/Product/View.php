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

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Product View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class View extends \Magento\Catalog\Block\Product\View\AbstractView
{

    /**
     * @var array
     */
    protected $options;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;

    /**
     * @var \Magento\Bundle\Model\Product\PriceFactory
     */
    protected $productPriceFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * Giftproduct data
     *
     * @var \Magento\Bundle\Helper\Giftproduct
     */
    protected $_giftproductData = null;
    
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;
    
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    
    /**
     * Giftvoucher data
     *
     * @var \Magento\Bundle\Helper\Giftvoucher
     */
    protected $_giftvoucherData = null;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * View constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magestore\Giftvoucher\Helper\Giftproduct $helperData
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Giftvoucher\Model\Product\PriceFactory $productPrice
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magestore\Giftvoucher\Helper\Giftproduct $helperData,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Giftvoucher\Model\Product\PriceFactory $productPrice,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_giftvoucherData = $giftvoucherData;
        $this->_priceCurrency = $priceCurrency;
        $this->_catalogHelper = $context->getCatalogHelper();
        $this->_giftproductData = $helperData;
        $this->catalogProduct = $catalogProduct;
        $this->productPriceFactory = $productPrice;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_localeFormat = $localeFormat;
        $this->_dataObject = $dataObject;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * Add meta information from product to head block
     *
     * @return \Magestore\Giftvoucher\Block\Product\View
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $product = $this->getProduct();
        $media = $this->getLayout()->getBlock('product.info.media.image');
        
        if ($media && $product->getTypeId() == 'giftvoucher') {
            $media->setTemplate('Magestore_Giftvoucher::giftvoucher/product/media.phtml');
        }
    }
    
    /**
     * Get the price information of Gift Card product
     *
     * @param \Magestore\Giftvoucher\Model\Product $product
     * @return array
     */
    public function getGiftAmount($product)
    {
        $giftValue = $this->_giftproductData->getGiftValue($product);
        switch ($giftValue['type']) {
            case 'range':
                $giftValue['from'] = $this->convertPrice($product, $giftValue['from']);
                $giftValue['to'] = $this->convertPrice($product, $giftValue['to']);
                $giftValue['from_txt'] = $this->_priceCurrency->format($giftValue['from']);
                $giftValue['to_txt'] = $this->_priceCurrency->format($giftValue['to']);
                break;
            case 'dropdown':
                $giftValue['options'] = $this->_convertPrices($product, $giftValue['options']);
                $giftValue['prices'] = $this->_convertPrices($product, $giftValue['prices']);
                $giftValue['prices'] = array_combine($giftValue['options'], $giftValue['prices']);
                $giftValue['options_txt'] = $this->_formatPrices($giftValue['options']);
                break;
            case 'static':
                $giftValue['value'] = $this->convertPrice($product, $giftValue['value']);
                $giftValue['value_txt'] = $this->_priceCurrency->format($giftValue['value']);
                $giftValue['price'] = $this->convertPrice($product, $giftValue['gift_price']);
                break;
            default:
                $giftValue['type'] = 'any';
        }
        return $giftValue;
    }
    
    /**
     * Convert Gift Card base price
     *
     * @param \Magestore\Giftvoucher\Model\Product $product
     * @param float $basePrices
     * @return float
     */
    protected function _convertPrices($product, $basePrices)
    {
        foreach ($basePrices as $key => $price) {
            $basePrices[$key] = $this->convertPrice($product, $price);
        }
        return $basePrices;
    }
    
    /**
     * Get Gift Card product price with all tax settings processing
     *
     * @param \Magestore\Giftvoucher\Model\Product $product
     * @param float $price
     * @return float
     */
    public function convertPrice($product, $price)
    {
        $includeTax = ( $this->_taxData->getPriceDisplayType() != 1 );

        $priceWithTax = $this->_catalogHelper->getTaxPrice($product, $price, $includeTax);
        return $this->_priceCurrency->convert($priceWithTax);
    }

    /**
     * Formatted Gift Card price
     *
     * @param array $prices
     * @return array
     */
    protected function _formatPrices($prices)
    {
        foreach ($prices as $key => $price) {
            $prices[$key] = $this->_priceCurrency->format($price);
        }
        return $prices;
    }

    public function messageMaxLen()
    {
        return (int) $this->_giftvoucherData->getInterfaceConfig('max');
    }

    public function enablePhysicalMail()
    {
        return $this->_giftvoucherData->getInterfaceConfig('postoffice');
    }

    public function getFormConfigData()
    {
        $store = $this->_storeManager->getStore();
        $request = $this->_request;
        $formData = array();
        $result = array();
        if ($this->isInConfigurePage()) {
            $options = $this->_objectManager->create('Magento\Quote\Model\Quote\Item\Option')->getCollection()
                ->addItemFilter($request->getParam('id'));

            foreach ($options as $option) {
                $result[$option->getCode()] = $option->getValue();
            }

            if (isset($result['base_gc_value'])) {
                if (isset($result['gc_product_type']) && $result['gc_product_type'] == 'range') {
                    $currency = $store->getCurrentCurrencyCode();
                    $baseCurrencyCode = $store->getBaseCurrencyCode();

                    if ($currency != $baseCurrencyCode) {
                        $currentCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                            ->load($currency);
                        $baseCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                            ->load($baseCurrencyCode);

                        $value = $this->_priceCurrency
                            ->round($baseCurrency->convert($result['base_gc_value'], $currentCurrency));
                    } else {
                        $value = $this->_priceCurrency->round($result['base_gc_value']);
                    }
                }
            }

            foreach ($options as $option) {
                if ($option->getCode() == 'amount') {
                    if (isset($value)) {
                        $formData[$option->getCode()] = $value;
                    } else {
                        $formData[$option->getCode()] = $option->getValue();
                    }
                } else {
                    $formData[$option->getCode()] = $option->getValue();
                }
            }
        }
        $dataObject = $this->_dataObject->setData($formData);

        return $dataObject;
    }

    public function enableScheduleSend()
    {
        return $this->_giftvoucherData->getInterfaceConfig('schedule');
    }

    public function getGiftAmountDescription()
    {
        if (!$this->hasData('gift_amount_description')) {
            $product = $this->getProduct();
            $this->setData('gift_amount_description', '');
            if ($product->getShowGiftAmountDesc()) {
                if ($product->getGiftAmountDesc()) {
                    $this->setData('gift_amount_description', $product->getGiftAmountDesc());
                } else {
                    $this->setData(
                        'gift_amount_description',
                        $this->_giftvoucherData->getInterfaceConfig('description')
                    );
                }
            }
        }
        return $this->getData('gift_amount_description');
    }

    public function getAvailableTemplate()
    {
        $product = $this->getProduct();
        $productTemplate = $product->getGiftTemplateIds();
        if ($productTemplate) {
            $productTemplate = explode(',', $productTemplate);
        } else {
            $productTemplate = array();
        }

        $templates = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')->getCollection()
                ->addFieldToFilter('status', '1')
                ->addFieldToFilter('giftcard_template_id', array('in' => $productTemplate));

        return $templates->getData();
    }

    public function getPriceFormatJs()
    {
        $priceFormat = $this->_localeFormat->getPriceFormat();
        return $this->_jsonEncoder->encode($priceFormat);
    }

    public function isInConfigurePage()
    {
        $request = $this->_request;
        $action = $request->getFullActionName();
        
        if ($action == 'checkout_cart_configure' && $request->getParam('id')) {
            return true;
        }
        return false;
    }
    public function contentCondition()
    {
        $giftProduct = $this->_objectManager->create('Magestore\Giftvoucher\Model\Product')
            ->loadByProduct($this->getProduct());
        if ($giftProduct->getGiftcardDescription()) {
            return $giftProduct->getGiftcardDescription();
        }
        return false;
    }
    
    public function getGiftvoucherHelper()
    {
        return $this->_giftvoucherData;
    }
    
    public function getRequestInterface()
    {
        return $this->_request;
    }
    
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    
    public function getJsonEncode()
    {
        return $this->_jsonEncoder;
    }
    
    public function getTaxHelper()
    {
        return $this->_taxData;
    }
    
    public function getCatalogHelper()
    {
        return $this->_catalogHelper;
    }
    
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
    
    public function getPriceCurrency()
    {
        return $this->_priceCurrency;
    }
}
