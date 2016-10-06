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
namespace Magestore\Giftvoucher\Block\Cart;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Giftvoucher Cart Item Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Item extends Renderer implements IdentityInterface
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Item constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
        $this->_objectManager = $objectManager;
        $this->setTemplate('Magestore_Giftvoucher::giftvoucher/cart/item.phtml');
    }
    
    public function getProductOptions()
    {

        $options = parent::getProductOptions();
        $giftvoucherOptions = $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')
            ->getGiftVoucherOptions();
        $templates = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')
            ->getCollection()
            ->addFieldToFilter('status', '1');
        $item = parent::getItem();
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());;
        $cartType = $product->getGiftCardType();
        //var_dump($product->getGiftCardType());die('xxx');
        foreach ($giftvoucherOptions as $code => $label) {
            if ($option = $this->getItem()->getOptionByCode($code)) {
                if ($code == 'giftcard_template_id' ) {
                    foreach ($templates as $template) {
                        if ($template->getId() == $option->getValue()) {
                            $valueTemplate = $template;
                        }
                    }
                    if($cartType !=1) {
                        $options[] = array(
                            'label' => $label,
                            'value' => $this->escapeHtml($valueTemplate->getTemplateName() ?
                                $valueTemplate->getTemplateName() : $option->getValue()),
                        );
                    }
                } elseif ($code == 'amount') {
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->priceCurrency->format(
                            $option->getValue(),
                            true,
                            PriceCurrencyInterface::DEFAULT_PRECISION,
                            $this->_storeManager->getStore()
                        )
                    );
                } else {
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->escapeHtml($option->getValue()),
                    );
                }
            }
        }
        return $options;
    }

    public function getProductThumbnail()
    {
        if (!$this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')
            ->getInterfaceCheckoutConfig('display_image_item')
            || $this->getProduct()->getTypeId() != 'giftvoucher') {
            return parent::getProductThumbnail();
        }
        
        $item = $this->getItem();
        if ($item->getOptionByCode('giftcard_template_image')) {
            $filename = $item->getOptionByCode('giftcard_template_image')->getValue();
        } else {
            $filename = 'default.png';
        }
        if ($item->getOptionByCode('giftcard_use_custom_image')
            && $item->getOptionByCode('giftcard_use_custom_image')->getValue()) {
            $urlImage = '/tmp/giftvoucher/images/' . $filename;
        } else {
            if ($item->getOptionByCode('giftcard_template_id')) {
                $templateId = $item->getOptionByCode('giftcard_template_id')->getValue();
                $designPattern = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')
                    ->load($templateId)->getDesignPattern();
                if ($designPattern == \Magestore\Giftvoucher\Model\Designpattern::PATTERN_LEFT) {
                    $filename = 'left/' . $filename;
                } elseif ($designPattern == \Magestore\Giftvoucher\Model\Designpattern::PATTERN_TOP) {
                    $filename = 'top/' . $filename;
                }
            }
            $urlImage = '/giftvoucher/template/images/' . $filename;
        }
        
        $imageUrl = $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')
                ->getStoreManager()
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . $urlImage;
        $file = $this->_objectManager->get('Magestore\Giftvoucher\Helper\Drawgiftcard')
            ->getBaseDirMedia()
            ->getAbsolutePath($urlImage);
        if (file_exists($file)) {
            return $imageUrl;
        }
        return false;
        
    }
    
    public function getImageSrc()
    {
        $thumbnail = $this->getProductThumbnail();
        return $thumbnail;
    }

    public function getItem()
    {
        $item = parent::getItem();
        
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());

        $rowTotal = $item->getRowTotal();
        $qty = $item->getQty();
        $store = $item->getStore();
        $price = $this->priceCurrency->round($rowTotal) / $qty;

        $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $quoteCurrencyCode = $item->getQuote()->getQuoteCurrencyCode();
        $baseCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')->load($baseCurrencyCode);

        if ($baseCurrencyCode != $quoteCurrencyCode) {
            $quoteCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                ->load($quoteCurrencyCode);
            if ($product->getGiftType() == \Magestore\Giftvoucher\Model\Gifttype::GIFT_TYPE_RANGE) {
                $price = $price * $price / $baseCurrency->convert($price, $quoteCurrency);
                $item->setPrice($price);
            }
        }

        $options = $item->getOptions();
        $result = array();
        foreach ($options as $option) {
            $result[$option->getCode()] = $option->getValue();
        }

        if (isset($result['base_gc_value']) && isset($result['base_gc_currency'])) {
            $currency = $store->getCurrentCurrencyCode();
            $currentCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')->load($currency);
            $amount = $baseCurrency->convert($result['base_gc_value'], $currentCurrency);
            foreach ($options as $option) {
                if ($option->getCode() == 'amount') {
                    $option->setValue($amount);
                }
            }
            $item->setOptions($options)->save();
        }

        return $item;
    }
}
