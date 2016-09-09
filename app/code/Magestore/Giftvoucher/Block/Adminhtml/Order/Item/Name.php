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
namespace Magestore\Giftvoucher\Block\Adminhtml\Order\Item;

/**
 * Adminhtml Giftvoucher Order Item Name Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Name extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{

    /**
     * Name constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Magestore\Giftvoucher\Helper\Data $helper
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magestore\Giftvoucher\Helper\Data $helper,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_giftvoucher = $giftvoucher;
        $this->_currencyFactory = $currencyFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }
    
    public function getOrderOptions()
    {
        $result = parent::getOrderOptions();
        $item = $this->getItem();

        if ($item->getProductType() != 'giftvoucher') {
            return $result;
        }
        
        if ($options = $item->getProductOptionByCode('info_buyRequest')) {
            foreach ($this->_helper->getGiftVoucherOptions() as $code => $label) {
                if (isset($options[$code]) && $options[$code]) {
                    if ($code == 'giftcard_template_id') {
                        $valueTemplate = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')
                            ->load($options[$code]);
                        
                        $result[] = array(
                            'label' => $label,
                            'value' => $this->_escaper->escapeHtml($valueTemplate->getTemplateName()),
                            'option_value' => $this->_escaper->escapeHtml($valueTemplate->getTemplateName()),
                        );
                    } else {
                        $result[] = array(
                            'label' => $label,
                            'value' => $this->_escaper->escapeHtml($options[$code]),
                            'option_value' => $this->_escaper->escapeHtml($options[$code]),
                        );
                    }
                }
            }
        }

        $giftVouchers = $this->_giftvoucher->getCollection()->addItemFilter($item->getQuoteItemId());
        if ($giftVouchers->getSize()) {
            $giftVouchersCode = array();
            foreach ($giftVouchers as $giftVoucher) {
                $currency = $this->_currencyFactory->create()->load($giftVoucher->getCurrency());
                $balance = $giftVoucher->getBalance();
                if ($currency) {
                    $balance = $currency->format($balance, array(), false);
                }
                $giftVouchersCode[] = $giftVoucher->getGiftCode() . ' (' . $balance . ') ';
            }
            $codes = implode(' , ', $giftVouchersCode);
            $result[] = array(
                'label' => __('Gift Card Code'),
                'value' => $codes,
                'option_value' => $codes,
            );
        }

        return $result;
    }
}
