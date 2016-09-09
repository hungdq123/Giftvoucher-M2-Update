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

namespace Magestore\Giftvoucher\Model\Product;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Product Price Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Fixed bundle price type
     */
    const PRICE_TYPE_FIXED = 1;

    /**
     * Dynamic bundle price type
     */
    const PRICE_TYPE_DYNAMIC = 0;

    /**
     * Flag which indicates - is min/max prices have been calculated by index
     *
     * @var bool
     */
    protected $_isPricesCalculatedByIndex;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;
    
    /**
     * Giftproduct data
     *
     * @var \Magento\Bundle\Helper\Giftproduct
     */
    protected $_giftproductData = null;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;
    
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * Giftvoucher data
     *
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherData = null;

    /**
     * Price constructor.
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherData
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magestore\Giftvoucher\Helper\Giftproduct $helperData,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherData
    ) {
        $this->_giftproductData = $helperData;
        $this->_catalogData = $catalogData;
        $this->_taxData = $taxHelper;
        $this->_objectManager = $objectManager;
        $this->_giftvoucherData = $giftvoucherData;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
    }

    /**
     * Get Gift Card price information
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getGiftAmount($product = null)
    {
        $giftAmount = $this->_giftproductData->getGiftValue($product);
        switch ($giftAmount['type']) {
            case 'range':
                $giftAmount['min_price'] = $giftAmount['from'];
                $giftAmount['max_price'] = $giftAmount['to'];
                $giftAmount['price'] = $giftAmount['from'];
                if ($giftAmount['gift_price_type'] == 'percent') {
                    $giftAmount['price'] = $giftAmount['price'] * $giftAmount['gift_price_options'] /100;
                    $giftAmount['min_price'] = $giftAmount['from']* $giftAmount['gift_price_options'] /100;
                    $giftAmount['max_price'] = $giftAmount['to']* $giftAmount['gift_price_options'] /100;
                }
                
                if ($giftAmount['min_price'] == $giftAmount['max_price']) {
                    $giftAmount['price_type'] = self::PRICE_TYPE_FIXED;
                } else {
                    $giftAmount['price_type'] = self::PRICE_TYPE_DYNAMIC;
                }
                break;
            case 'dropdown':
                $giftAmount['min_price'] = min($giftAmount['prices']);
                $giftAmount['max_price'] = max($giftAmount['prices']);
                $giftAmount['price'] = $giftAmount['prices'][0];
                if ($giftAmount['min_price'] == $giftAmount['max_price']) {
                    $giftAmount['price_type'] = self::PRICE_TYPE_FIXED;
                } else {
                    $giftAmount['price_type'] = self::PRICE_TYPE_DYNAMIC;
                }
                break;
            case 'static':
                $giftAmount['price'] = $giftAmount['gift_price'];
                $giftAmount['price_type'] = self::PRICE_TYPE_FIXED;
                break;
            default:
                $giftAmount['min_price'] = 0;
                $giftAmount['price_type'] = self::PRICE_TYPE_DYNAMIC;
                $giftAmount['price'] = 0;
        }
        return $giftAmount;
    }
    
    /**
     * Default action to get price of product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return decimal
     */
    public function getPrice($product)
    {
        $giftAmount = $this->getGiftAmount($product);
        return $giftAmount['price'];
    }

    /**
     * Apply options price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     * @param float $finalPrice
     * @return float
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($amount = $product->getCustomOption('price_amount')) {
            $finalPrice = $amount->getValue();
        }

        return parent::_applyOptionsPrice($product, $qty, $finalPrice);
    }

    /**
     * Get product's price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $which
     * @return array
     */
    public function getPrices($product, $which = null)
    {
        return $this->getPricesDependingOnTax($product, $which);
    }

    /**
     * Get price depending on Tax
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $which
     * @param bool $includeTax
     * @return array
     */
    public function getPricesDependingOnTax($product, $which = null, $includeTax = null)
    {
        $giftAmount = $this->getGiftAmount($product);
        if (isset($giftAmount['min_price']) && isset($giftAmount['max_price'])) {
            $minimalPrice = $this->_catalogData->getTaxPrice($product, $giftAmount['min_price'], $includeTax);
            $maximalPrice = $this->_catalogData->getTaxPrice($product, $giftAmount['max_price'], $includeTax);
        } else {
            $minimalPrice = $maximalPrice = $this->_catalogData
                                                ->getTaxPrice($product, $giftAmount['price'], $includeTax);
        }

        if ($which == 'max') {
            return $maximalPrice;
        } elseif ($which == 'min') {
            return $minimalPrice;
        }
        return array($minimalPrice, $maximalPrice);
    }

    /**
     * Get min price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getMinimalPrice($product)
    {
        return $this->getPrices($product, 'min');
    }

    /**
     * Get max price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getMaximalPrice($product)
    {
        return $this->getPrices($product, 'max');
    }

    /**
     * Retrieve product final price
     *
     * @param float|null $qty
     * @param Product $product
     * @return float
     */
    public function getFinalPrice($qty, $product)
    {
        $finalPrice = $this->getPrice($product);
        $product->setFinalPrice($finalPrice);

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }
    
    /**
     * Set default value for Gift card product
     */
    public function setDefaultValues($product)
    {
        $result = array();
        $giftAmount = $this->getGiftAmount($product);
        if ($giftAmount['type'] == 'static') {
            $result['amount'] = $giftAmount['value'];
            $result['price_amount'] = $giftAmount['price'];
        } elseif ($giftAmount['type'] == 'dropdown') {
            $result['amount'] = $giftAmount['options'][0];
            $result['price_amount'] = $giftAmount['min_price'];
        } else {
            $result['amount'] = strval($giftAmount['from']);
            $result['price_amount'] = strval($giftAmount['min_price']);
        }
        if ($product->getGiftTemplateIds()) {
            $productTemplate = $product->getGiftTemplateIds();
            if ($productTemplate) {
                $productTemplate = explode(',', $productTemplate);
            } else {
                $productTemplate = array();
            }

            $templates = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')->getCollection()
                ->addFieldToFilter('status', '1')
                ->addFieldToFilter('giftcard_template_id', array('in' => $productTemplate));
            if (count($templates)) {
                foreach ($templates as $template) {
                    $result['giftcard_template_id'] = $template['giftcard_template_id'];
                    if ($template['images']) {
                        $images = explode(',', $template['images']);
                        $result['giftcard_template_image'] = $images[0];
                    }
                    break;
                }
            } else {
                return __('There is not the Gift card template available.');
            }
        } else {
            return __('You didn\'t assign the Gift Card template to this product.');
        }
        
        $result['giftcard_use_custom_image'] = '0';
        $result['recipient_address'] = '';
        if ($this->_giftvoucherData->getCustomerSession()->isLoggedIn()) {
            $customerName = $this->_objectManager->create('Magento\Customer\Helper\View')
                                ->getCustomerName($this->_giftvoucherData->getCustomerSession()->getCustomerData());
        } else {
            $customerName = '';
        }
        $result['customer_name'] = '';
        $result['recipient_name'] = '';
        $result['recipient_email'] = '';
        $result['message'] = '';
        $result['notify_success'] = '1';
        $result['day_to_send'] = '';
        $result['timezone_to_send'] = $this->_storeManager->getStore()->getConfig('general/locale/timezone');
        
        return $this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($result);
    }

    public function getObjectManager()
    {
        return $this->_objectManager;
    }
}
