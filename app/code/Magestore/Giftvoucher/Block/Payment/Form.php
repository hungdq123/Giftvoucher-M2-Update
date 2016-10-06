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
namespace Magestore\Giftvoucher\Block\Payment;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Payment Form Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magento\Payment\Block\Form
{
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_helperData;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * Form constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Giftvoucher\Helper\Data $helperData
     * @param \Magento\Directory\Model\Currency $currency
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Directory\Model\Currency $currency,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_helperData = $helperData;
        $this->_currency = $currency;
        $this->priceCurrency = $priceCurrency;
    }
    
    public function getStore($storeId = null)
    {
        return $this->_storeManager->getStore($storeId);
    }
    
    public function getHelperData()
    {
        return $this->_helperData;
    }

    public function isPassed()
    {
        return ($this->priceCurrency->round($this->getGrandTotal()) == 0);
    }

    public function getGiftVoucherDiscount($dimension = false)
    {
        $session = $this->_helperData->getCheckoutSession();
        $discounts = array();
        if ($codes = $session->getGiftCodes()) {
            $codesArray = explode(',', $codes);
            $codesDiscountArray = explode(',', $session->getCodesDiscount());
            foreach ($codesArray as $key => $value) {
                if (!$dimension) {
                    if (!isset($codesDiscountArray[$key])) {
                        $discounts[$value] = '';
                    } else {
                        $discounts[$value] = abs(round($codesDiscountArray[$key], 2));
                    }
                } else {
                    $discount = array();
                    if (!isset($codesDiscountArray[$key])) {
                        $discount['value'] = '';
                    } else {
                        $discount['value'] =  abs(round($codesDiscountArray[$key], 2));
                    }
                    $discount['code'] = $value;
                    $discount['hiddenCode'] = $this->_helperData->getHiddenCode($value);
                    $discount['discount'] = $this->priceCurrency
                        ->format(abs(round($codesDiscountArray[$key], 2)), false);
                    $discount['removeUrl'] = $this->getUrl('giftvoucher/checkout/remove', array('code' => $value));
                    $discounts[] = $discount;
                }
            }
        }
        return $discounts;
    }

    public function getGrandTotal()
    {
        if (!$this->hasData('grand_total')) {
            $quote = $this->_helperData->getCheckoutSession()->getQuote();
            $grandTotal = $quote->getGrandTotal();
            $this->setData('grand_total', $grandTotal);
        }
        return $this->getData('grand_total');
    }

    public function getAddGiftVoucherUrl()
    {
        return trim($this->getUrl('giftvoucher/checkout/addgift'), '/');
    }

    /**
     * check customer use gift card to checkout
     *
     * @return boolean
     */
    public function getUseGiftVoucher()
    {
        return $this->_helperData->getCheckoutSession()->getUseGiftCard();
    }

    public function checkCustomerIsLoggedIn()
    {
        return $this->_helperData->getCustomerSession()->isLoggedIn();
    }

    /**
     * get existed gift Card
     *
     * @return array
     */
    public function getExistedGiftCard()
    {
        $session = $this->_helperData->getCustomerSession();
        if (!$session->isLoggedIn()) {
            return array();
        }

        $customerId = $session->getCustomerId();
        $collection = $this->_objectManager->get('Magestore\Giftvoucher\Model\ResourceModel\Customervoucher\Collection')
                ->addFieldToFilter('main_table.customer_id', $customerId);
        $collection->getExistedGiftcodes($customerId, $session->getCustomer()->getEmail());
        $giftCards = array();
        $addedCodes = array();
        if ($codes = $this->_helperData->getCheckoutSession()->getGiftCodes()) {
            $addedCodes = explode(',', $codes);
        }
        
        $quote = $this->_helperData->getCheckoutSession()->getQuote();
        $quote->setQuote($quote);
        foreach ($collection as $item) {
            if (in_array($item->getGiftCode(), $addedCodes)) {
                continue;
            }
            $type = $this->_helperData->getSetIdOfCode($item->getGiftCode());
            if (!$type) {
                $giftCards[] = array(
                    'gift_code' => $item->getGiftCode(),
                    'hidden_code' => $this->_helperData->getHiddenCode($item->getGiftCode()),
                    'balance' => $this->getGiftCardBalance($item)
                );
            }
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
        $cardCurrency = $this->_currency->load($item->getCurrency());
        /* @var Mage_Core_Model_Store */
        $store = $this->_storeManager->getStore();
        $baseCurrency = $store->getBaseCurrency();
        $currentCurrency = $store->getCurrentCurrency();
        if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
            return $this->priceCurrency->format($item->getBalance(), false);
        }
        if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
            return $this->priceCurrency->convert($item->getBalance());
        }
        if ($baseCurrency->convert(100, $cardCurrency)) {
            $amount = $item->getBalance() * $baseCurrency->convert(100, $currentCurrency)
                / $baseCurrency->convert(100, $cardCurrency);
            return $this->priceCurrency->format($amount, false);
        }
        return $cardCurrency->format($item->getBalance(), array(), false);
    }

    /**
     * get customer Credit to checkout
     *
     * @return \Magestore\Giftvoucher\Model\Credit
     */
    public function getCustomerCredit()
    {
        if ($this->checkCustomerIsLoggedIn()) {
            $credit = $this->_objectManager->get('Magestore\Giftvoucher\Model\Credit')->load(
                $this->_helperData->getCustomerSession()->getCustomerId(),
                'customer_id'
            );
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
            $cardCurrency = $this->_currency->load($credit->getCurrency());
            /* @var Mage_Core_Model_Store */
            $store = $this->_storeManager->getStore();
            $baseCurrency = $store->getBaseCurrency();
            $currentCurrency = $store->getCurrentCurrency();
            if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
                return $this->priceCurrency->format($credit->getBalance() - $this->getUseGiftCreditAmount(), false);
            }
            if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
                $amount = $this->priceCurrency->convert($credit->getBalance(), false);
                return $this->priceCurrency->format($amount - $this->getUseGiftCreditAmount(), false);
            }
            if ($baseCurrency->convert(100, $cardCurrency)) {
                $amount = $credit->getBalance() * $baseCurrency->convert(100, $currentCurrency)
                    / $baseCurrency->convert(100, $cardCurrency);
                return $this->priceCurrency->format($amount - $this->getUseGiftCreditAmount(), false);
            }
            return $cardCurrency->format($credit->getBalance(), array(), false);
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
        return $this->_helperData->getCheckoutSession()->getUseGiftCardCredit();
    }

    public function getUsingAmount()
    {
        return $this->priceCurrency->format(
            $this->_helperData->getCheckoutSession()->getUseGiftCreditAmount(),
            false
        );
    }

    public function getUseGiftCreditAmount()
    {
        return round($this->_helperData->getCheckoutSession()->getUseGiftCreditAmount(), 2);
    }
    
    public function getAllGiftvoucherData()
    {
        $this->_helperData->getCheckoutSession()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
        $result = array();
        $result['showGiftForm'] = $this->_helperData->getGeneralConfig('active')
            && $this->_helperData->getStoreConfig('giftvoucher/interface_payment/show_gift_card');
        $result['purchaseGiftcard'] = $this->_hasGiftcard();
        $result['enableCredit'] = $this->_helperData->getGeneralConfig('enablecredit');
        $result['customerCredit'] = $this->getCustomerCredit();
        if ($result['customerCredit']) {
            $result['customerCreditFormated'] = $this->formatBalance($result['customerCredit'], true);
        }
        $result['useGiftcredit'] = $this->getUseGiftCredit();
        $result['useGiftcreditAmount'] = $this->getUseGiftCreditAmount();
        $result['allImages'] = $this->_getAllImages();
        $result['usingAmount'] = $this->getUsingAmount();
        $result['useGiftVoucher'] = $this->getUseGiftVoucher();
        $result['giftVoucherDiscount'] = $this->getGiftVoucherDiscount(true);
        $result['isPassed'] = $this->isPassed();
        $result['isAvailableToAddCode'] = $this->_helperData->isAvailableToAddCode();
        $result['maximumTimeAddCode'] = $this->_helperData->getGeneralConfig('maximum');
        $result['existedGiftcards'] = $this->getExistedGiftCard();
        $result['customerIsLogin'] = $this->checkCustomerIsLoggedIn();
        $result['manageGiftcard'] = $this->getUrl('giftvoucher/index/index');
        $result['checkGiftcard'] = $this->getUrl('giftvoucher/index/check');
        $result['addGiftVoucherUrl'] = $this->getAddGiftVoucherUrl();
        
        return $result;
    }
    
    private function _hasGiftcard()
    {
        $items = $this->_helperData->getCheckoutSession()->getQuote()->getAllItems();
        $count = 0;
        foreach ($items as $item) {
            $data = $item->getData();
            if ($data['product_type'] == 'giftvoucher') {
                $count++;
            }
        }
        if ($count == count($items)) {
            return array('hasGiftcard'=>true, 'allGiftcard'=>true, 'countGiftcard'=>$count);
        } elseif ($count != count($items) && $count > 0) {
            return array('hasGiftcard'=>true, 'allGiftcard'=>false, 'countGiftcard'=>$count);
        } else {
            return array('hasGiftcard'=>false, 'allGiftcard'=>false, 'countGiftcard'=>$count);
        }
    }
    
    private function _getAllImages()
    {
        $result = array();
        $result['opc-ajax-loader'] = $this->getViewFileUrl('Magestore_Giftvoucher::images/opc-ajax-loader.gif');
        $result['btn_edit'] = $this->getViewFileUrl('Magestore_Giftvoucher::images/btn_edit.gif');
        $result['i_msg-success'] = $this->getViewFileUrl('Magestore_Giftvoucher::images/i_msg-success.gif');
        $result['btn_remove'] = $this->getViewFileUrl('Magestore_Giftvoucher::images/btn_remove.gif');
        return $result;
    }

    public function getItems()
    {
        $cart = $this->getHelperData()->getCheckoutSession();

        return $cart->getQuote()->getAllItems();
    }

    /**
     * @return int
     */
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
