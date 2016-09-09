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
namespace Magestore\Giftvoucher\Model\Total\Quote;

use Magento\Framework\App\Area;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher Total Quote Giftcardcreditaftertax Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftcardcreditaftertax extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    protected $_hiddentBaseDiscount = 0;
    protected $_hiddentDiscount = 0;

    /**
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\State $appState
     * @param \Magestore\Giftvoucher\Model\Credit $creditModel
     * @param \Magento\Tax\Helper\Data $helperTax
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\State $appState,
        \Magestore\Giftvoucher\Model\Credit $creditModel,
        \Magento\Tax\Helper\Data $helperTax,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionQuote = $sessionQuote;
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        $this->_creditModel = $creditModel;
        $this->_helperTax = $helperTax;
        $this->priceCurrency = $priceCurrency;
        $this->setCode('giftcardcreditaftertax');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        $applyGiftAfterTax =
            (bool) $this->_giftvoucherHelper->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        if (!$applyGiftAfterTax) {
            return $this;
        }
        
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        
        $session = $this->_checkoutSession;
        if (!is_object($session)) {
            return $this;
        }

        if (!$this->_giftvoucherHelper->getGeneralConfig('enablecredit', $quote->getStoreId())) {
            $session->setBaseUseGiftCreditAmount(0);
            $session->setUseGiftCreditAmount(0);
            return $this;
        }
        if ($this->_appState->getAreaCode() != Area::AREA_FRONTEND) {
            $customerId = $this->_sessionQuote->getCustomerId();
            $credit = $this->_creditModel->load(
                $customerId,
                'customer_id'
            );
            $baseBalance =  $credit->getBalance();
        }
       
        if (!$session->getUseGiftCardCredit()) {
            return $this;
        }

        $store = $quote->getStore();
        
        if (!isset($baseBalance)) {
            $baseBalance = $session->getGiftcreditBalance();
        }
        if ($baseBalance < 0.0001) {
            $session->setBaseUseGiftCreditAmount(0);
            $session->setUseGiftCreditAmount(0);
            return $this;
        }

        if ($session->getMaxCreditUsed() > 0) {
            $baseBalance = min($baseBalance, $session->getMaxCreditUsed());
        }
        $baseTotalDiscount = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                        $itemDiscount = $child->getBaseRowTotal() - $child->getMagestoreBaseDiscount()
                            - $child->getBaseDiscountAmount() + $child->getBaseTaxAmount();
                        $baseTotalDiscount += $itemDiscount;
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                    $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                        - $item->getBaseDiscountAmount() + $item->getBaseTaxAmount();
                    $baseTotalDiscount += $itemDiscount;
                }
            }
        }
        if ($this->_giftvoucherHelper->getGeneralConfig('use_for_ship')) {
            $shipDiscount = $address->getBaseShippingAmount() - $address->getMagestoreBaseDiscountForShipping()
                - $address->getBaseShippingDiscountAmount() + $address->getBaseShippingTaxAmount();
            $baseTotalDiscount += $shipDiscount;
        }
        
        $baseDiscount = min($baseTotalDiscount, $baseBalance);
        $discount = $this->priceCurrency->convert($baseDiscount);
        if ($baseTotalDiscount != 0) {
            $this->prepareGiftDiscountForItem(
                $total,
                $address,
                $baseDiscount / $baseTotalDiscount,
                $store,
                $baseDiscount
            );
        }
        if ($baseDiscount && $discount) {
            $session->setBaseUseGiftCreditAmount($baseDiscount);
            $session->setUseGiftCreditAmount($discount);
            $session->setGiftcardCreditAmount($baseDiscount);
            
            $total->setGiftcardCreditAmount($baseDiscount);
            $total->setBaseUseGiftCreditAmount($baseDiscount);
            $total->setUseGiftCreditAmount($discount);
            $total->setMagestoreBaseDiscount($total->getMagestoreBaseDiscount() + $baseDiscount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
            $total->setGrandTotal($total->getGrandTotal()- $discount);
  
            $total->setGiftcardcreditaftertax(-$discount);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $applyGiftAfterTax =
            (bool) $this->_giftvoucherHelper->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        if (!$applyGiftAfterTax) {
            return $result;
        }
        $amount = $total->getUseGiftCreditAmount();
        if ($amount > 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Gift Card credit'),
                'value' => -$amount
            ];
        }
        return $result;
    }

    /**
     * Prepare Gift Discount For Item
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param $rateDiscount
     * @param $store
     * @param $baseDiscount
     * @return $this
     */
    public function prepareGiftDiscountForItem(
        \Magento\Quote\Model\Quote\Address\Total $total,
        \Magento\Quote\Model\Quote\Address $address,
        $rateDiscount,
        $store,
        $baseDiscount
    ) {
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                        $itemDiscount = $child->getBaseRowTotal() - $child->getMagestoreBaseDiscount()
                            - $child->getBaseDiscountAmount() + $child->getBaseTaxAmount();
                        $child->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount()
                            + $itemDiscount * $rateDiscount);
                        $child->setBaseUseGiftCreditAmount($child->getBaseUseGiftCreditAmount()
                            + $itemDiscount * $rateDiscount);
                        $child->setUseGiftCreditAmount($child->getUseGiftCreditAmount()
                            + $this->priceCurrency->convert($itemDiscount * $rateDiscount));
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                    $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                        - $item->getBaseDiscountAmount() + $item->getBaseTaxAmount();
                    $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemDiscount * $rateDiscount);
                    $item->setBaseUseGiftCreditAmount($item->getBaseUseGiftCreditAmount()
                        + $itemDiscount * $rateDiscount);
                    $item->setUseGiftCreditAmount($item->getUseGiftCreditAmount()
                        + $this->priceCurrency->convert($itemDiscount * $rateDiscount));
                }
            }
        }
        if ($this->_giftvoucherHelper->getGeneralConfig('use_for_ship', $address->getQuote()->getStoreId())) {
            $shipDiscount = $total->getBaseShippingAmount() - $total->getMagestoreBaseDiscountForShipping()
                - $total->getBaseShippingDiscountAmount() + $total->getBaseShippingTaxAmount();
            $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            
            $total->setBaseGiftcreditDiscountForShipping($total->getBaseGiftcreditDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            $total->setGiftcreditDiscountForShipping($total->getGiftcreditDiscountForShipping()
                + $this->priceCurrency->convert($shipDiscount * $rateDiscount));
            
            if (!$this->_checkoutSession->getBaseGiftcreditDiscountForShipping()) {
                $this->_checkoutSession
                    ->setBaseGiftcreditDiscountForShipping($total->getBaseGiftcreditDiscountForShipping());
                $this->_checkoutSession
                    ->setGiftcreditDiscountForShipping($total->getGiftcreditDiscountForShipping());
            }
            
        }
        return $this;
    }

    /**
     * Clear Gift Card seassion
     */
    public function clearGiftcardSession($session)
    {
        if ($session->getUseGiftCard()) {
            $session->setUseGiftCard(null)
                    ->setGiftCodes(null)
                    ->setBaseAmountUsed(null)
                    ->setBaseGiftVoucherDiscount(null)
                    ->setGiftVoucherDiscount(null)
                    ->setCodesBaseDiscount(null)
                    ->setCodesDiscount(null)
                    ->setGiftMaxUseAmount(null);
        }
        if ($session->getUseGiftCardCredit()) {
            $session->setUseGiftCardCredit(null)
                    ->setMaxCreditUsed(null)
                    ->setBaseUseGiftCreditAmount(null)
                    ->setUseGiftCreditAmount(null);
        }
    }
}
