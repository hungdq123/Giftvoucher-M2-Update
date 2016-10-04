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


namespace Magestore\Giftvoucher\Block\Order;

/**
 * Giftvoucher Order Escape Item Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Item extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;

    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Item constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magestore\Giftvoucher\Helper\Data $helper
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magestore\Giftvoucher\Helper\Data $helper,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_giftvoucher = $giftvoucher;
        $this->_currencyFactory = $currencyFactory;
        $this->_objectManager = $objectManager;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }
    
    /**
     * Get Gift Card item options in the order
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = parent::getItemOptions();
        $item = $this->getOrderItem();
        $cartType = $item->getGiftCardType();
        if ($item->getProductType() != 'giftvoucher') {
            return $result;
        }

        $templates = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')
                        ->getCollection()
                        ->addFieldToFilter('status', '1');
        
        if ($options = $item->getProductOptionByCode('info_buyRequest')) {
            foreach ($this->_helper->getGiftVoucherOptions() as $code => $label) {
                if (isset($options[$code]) && $options[$code]) {
                    if ($code == 'giftcard_template_id' && $cartType !=1) {
                        foreach ($templates as $template) {
                            if ($template->getId() == $options[$code]) {
                                $valueTemplate = $template;
                            }
                        }
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

                $balance = $this->_localeCurrency->getCurrency($giftVoucher->getCurrency())
                    ->toCurrency($giftVoucher->getBalance(), array());
                if($giftVoucher->getSetId()>0){
                    $giftVouchersCode[] = 'XXXXXXXXX'. ' (' . $balance . ') ';
                }else {
                    $giftVouchersCode[] = $giftVoucher->getGiftCode() . ' (' . $balance . ') ';
                }
            }
            $codes = implode('<br />', $giftVouchersCode);
            $result[] = array(
                'label' => __('Gift Card Code'),
                'value' => $codes,
                'option_value' => $codes,
            );
        }

        return $result;
    }
    /**
     * Get the html for item price
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}
