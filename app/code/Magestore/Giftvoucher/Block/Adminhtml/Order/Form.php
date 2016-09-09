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
namespace Magestore\Giftvoucher\Block\Adminhtml\Order;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml Giftvoucher Order Form Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Order create
     *
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_orderCreate;

    /**
     * @var \Magestore\Giftvoucher\Model\Customervoucher
     */
    protected $_customervoucher;
    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magestore\Giftvoucher\Model\Customervoucher $customervoucher
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magestore\Giftvoucher\Model\Credit $credit
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magestore\Giftvoucher\Model\Customervoucher $customervoucher,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magestore\Giftvoucher\Model\Credit $credit,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_localeCurrency = $localeCurrency;
        $this->_customervoucher = $customervoucher;
        $this->_giftvoucher = $giftvoucher;
        $this->_credit = $credit;
        $this->_checkoutSession = $checkoutSession;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }
    
    public function getGiftVoucherDiscount()
    {
        $session = $this->_checkoutSession;
        $discounts = array();
        if ($codes = $session->getGiftCodes()) {
            $codesArray = explode(',', $codes);
            $codesDiscountArray = explode(',', $session->getCodesDiscount());
            $discounts = array_combine($codesArray, $codesDiscountArray);
        }
        return $discounts;
    }

    public function getAddGiftVoucherUrl()
    {
        return trim($this->getUrl('giftvoucher/adminhtml_checkout/addgift'), '/');
    }

    /**
     * check customer use gift card to checkout
     *
     * @return boolean
     */
    public function getUseGiftVoucher()
    {
        return $this->_checkoutSession->getUseGiftCard();
    }

    public function checkCustomerIsLoggedIn()
    {
        return $this->getCustomerId();
    }

    /**
     * get existed gift Card
     *
     * @return array
     */
    public function getExistedGiftCard()
    {
        $customerId = $this->getCustomerId();
        $collection = $this->_customervoucher->getCollection()
                 ->addFieldToFilter('main_table.customer_id', $customerId);
        $collection->getExistedGiftcodes($customerId, $this->getQuote()->getCustomerEmail());

        $giftCards = array();
        $addedCodes = array();
        if ($codes = $this->_checkoutSession->getGiftCodes()) {
            $addedCodes = explode(',', $codes);
        }
        $conditions = $this->_giftvoucher->getConditions();
        $quote = $this->getQuote();
        $quote->setQuote($quote);
        foreach ($collection as $item) {
            if (in_array($item->getGiftCode(), $addedCodes)) {
                continue;
            }
            if ($item->getConditionsSerialized()) {
                $conditionsArr = unserialize($item->getConditionsSerialized());
                if (!empty($conditionsArr) && is_array($conditionsArr)) {
                    $conditions->setConditions(array())->loadArray($conditionsArr);
                    if (!$conditions->validate($quote)) {
                        continue;
                    }
                }
            }
            $giftCards[] = array(
                'gift_code' => $item->getGiftCode(),
                'balance' => $this->getGiftCardBalance($item)
            );
        }
        return $giftCards;
    }

    /**
     * Get the balance of Gift Card
     *
     * @param mixed $item
     * @return string
     */
    public function getGiftCardBalance($item)
    {
        $cardCurrency = $this->_objectManager->get('Magento\Directory\Model\Currency')->load($item->getCurrency());
        /* @var Mage_Core_Model_Store */
        $store = $this->_sessionQuote->getStore();
        $baseCurrency = $store->getBaseCurrency();
        $currentCurrency = $store->getCurrentCurrency();
        if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
            return $this->formatPrice($item->getBalance());
        }
        if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
            return $this->convertPrice($item->getBalance(), true);
        }
        if ($baseCurrency->convert(100, $cardCurrency)) {
            $amount = $item->getBalance() * $baseCurrency->convert(100, $currentCurrency)
                / $baseCurrency->convert(100, $cardCurrency);
            return $this->formatPrice($amount);
        }
        return $cardCurrency->format($item->getBalance(), array(), true);
    }

    /**
     * get customer Credit to checkout
     *
     * @return Magestore_Giftvoucher_Model_Credit
     */
    public function getCustomerCredit()
    {
        if ($this->checkCustomerIsLoggedIn()) {
            $credit = $this->_credit->getCollection()
                ->addFieldToFilter('customer_id', $this->getCustomerId())->getFirstItem();
            if ($credit->getBalance() > 0.0001) {
                return $credit;
            }
        }
        return false;
    }

    /**
     * Returns the formatted Gift Card balance
     *
     * @param mixed $credit
     * @param boolean $showUpdate
     * @return string
     */
    public function formatBalance($credit, $showUpdate = false)
    {
        if ($showUpdate) {
            $cardCurrency = $this->_objectManager->get('Magento\Directory\Model\Currency')
                ->load($credit->getCurrency());
            /* @var Mage_Core_Model_Store */
            $store = $this->_sessionQuote->getStore();
            $baseCurrency = $store->getBaseCurrency();
            $currentCurrency = $store->getCurrentCurrency();
            if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
                return $this->formatPrice($credit->getBalance() - $this->getUseGiftCreditAmount());
            }
            if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
                $amount = $store->convertPrice($credit->getBalance(), false);
                return $this->formatPrice($amount - $this->getUseGiftCreditAmount());
            }
            if ($baseCurrency->convert(100, $cardCurrency)) {
                $amount = $credit->getBalance() * $baseCurrency->convert(100, $currentCurrency)
                    / $baseCurrency->convert(100, $cardCurrency);
                return $this->formatPrice($amount - $this->getUseGiftCreditAmount());
            }
            return $cardCurrency->format($credit->getBalance(), array(), true);
        }
        return $this->getGiftCardBalance($credit);
    }

    /**
     * check customer use gift credit to checkout
     *
     * @return boolean
     */
    public function getUseGiftCredit()
    {
        return $this->_checkoutSession->getUseGiftCardCredit();
    }

    public function getUsingAmount()
    {
        return $this->formatPrice($this->_checkoutSession->getUseGiftCreditAmount());
    }

    public function getUseGiftCreditAmount()
    {
        return $this->_checkoutSession->getUseGiftCreditAmount();
    }

    public function getItems()
    {
        return $this->getQuote()->getAllItems();
    }

    public function getCountItems()
    {
        $items = $this->getItems();
        $count = 0;
        foreach ($items as $item) {
            $data = $item->getData();
            if ($data['product_type'] == 'giftvoucher') {
                $count++;
            }
        }
        return $count;
    }
}
