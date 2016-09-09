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
 * Giftvoucher Total Quote Giftcardcredit Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftcardcredit extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\ObjectManagerInterface $objectManager
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
        $this->_objectManager = $objectManager;
        $this->setCode('giftcardcredit');
    }
    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return this
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
        if ($applyGiftAfterTax) {
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
        } else {
            $customerId = $this->_customerSession->getCustomerId();
            
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
                        if ($this->_helperTax->priceIncludesTax()) {
                            $itemDiscount = $child->getRowTotalInclTax() - $child->getMagestoreBaseDiscount()
                                - $child->getDiscountAmount();
                        } else {
                            $itemDiscount = $child->getBaseRowTotal() - $child->getMagestoreBaseDiscount()
                                - $child->getBaseDiscountAmount();
                        }
                        $baseTotalDiscount += $itemDiscount;
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                    if ($this->_helperTax->priceIncludesTax()) {
                        $itemDiscount = $item->getRowTotalInclTax() - $item->getMagestoreBaseDiscount()
                            - $item->getDiscountAmount();
                    } else {
                        $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                            - $item->getBaseDiscountAmount();
                    }
                    $baseTotalDiscount += $itemDiscount;
                }
            }
        }
        if ($this->_giftvoucherHelper->getGeneralConfig('use_for_ship')) {
            if ($this->_helperTax->shippingPriceIncludesTax()) {
                $shipDiscount = $address->getShippingInclTax() - $address->getMagestoreBaseDiscountForShipping()
                - $address->getShippingDiscountAmount();
            } else {
                $shipDiscount = $address->getBaseShippingAmount() - $address->getMagestoreBaseDiscountForShipping()
                - $address->getBaseShippingDiscountAmount();
            }
            $baseTotalDiscount += $shipDiscount;
        }
       
        $baseDiscount =  min($baseTotalDiscount, $baseBalance);
        $discount = $this->priceCurrency->convert($baseDiscount);
        if ($baseTotalDiscount != 0) {
            $this->prepareGiftDiscountForItem($total, $address, $baseDiscount / $baseTotalDiscount, $store, $baseDiscount);
        }
        if ($baseDiscount && $discount) {
            $session->setBaseUseGiftCreditAmount($baseDiscount);
        }
        if (!$this->_giftvoucherHelper->getCheckoutSession()->getUseGiftCard()) {
            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                            $child->setDiscountAmount($child->getDiscountAmount()+$child->getUseGiftCreditAmount());
                            $child->setBaseDiscountAmount($child->getBaseDiscountAmount()
                                +$child->getBaseUseGiftCreditAmount());
                        }
                    }
                } elseif ($item->getProduct()) {
                    if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                        $item->setDiscountAmount($item->getDiscountAmount()+$item->getUseGiftCreditAmount());
                        $item->setBaseDiscountAmount($item->getBaseDiscountAmount()
                            +$item->getBaseUseGiftCreditAmount());
                    }
                }
            }
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
            $total->setGrandTotal($total->getGrandTotal() - $discount);
            $total->setTotalAmount('giftcardcredit', -$discount);
            $total->setBaseTotalAmount('giftcardcredit', -$baseDiscount);
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
        $session = $this->_checkoutSession;
        $applyGiftAfterTax =
            (bool) $this->_giftvoucherHelper->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        if ($applyGiftAfterTax) {
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
        $taxCalculation = $this->_objectManager->create('Magento\Tax\Model\Calculation');
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                        if ($this->_helperTax->priceIncludesTax()) {
                            $itemDiscount = $child->getRowTotalInclTax() - $child->getMagestoreBaseDiscount()
                                - $child->getDiscountAmount();
                        } else {
                            $itemDiscount = $child->getBaseRowTotal() - $child->getMagestoreBaseDiscount()
                                - $child->getBaseDiscountAmount();
                        }
                        $child->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount()
                            + $itemDiscount * $rateDiscount);
                        $child->setBaseUseGiftCreditAmount($child->getBaseUseGiftCreditAmount()
                            + $itemDiscount * $rateDiscount);
                        $child->setUseGiftCreditAmount($child->getUseGiftCreditAmount()
                            + $this->priceCurrency->convert($itemDiscount * $rateDiscount));
                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                        $taxableAmount = $child->getTaxableAmount();

                        $child->setBaseTaxableAmount($child->getBaseTaxableAmount()
                            - $child->getBaseUseGiftCreditAmount());
                        $child->setTaxableAmount($child->getTaxableAmount() - $child->getUseGiftCreditAmount());

                        if ($this->_helperTax->priceIncludesTax()) {
                            $rate = $this->_giftvoucherHelper->getItemRateOnQuote($item->getProduct(), $store);
                            $hiddenBaseTaxBeforeDiscount = $taxCalculation
                                ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                            $hiddenTaxBeforeDiscount = $taxCalculation
                                ->calcTaxAmount($taxableAmount, $rate, true, false);

                            $hiddenBaseTaxAfterDiscount = $taxCalculation
                                ->calcTaxAmount($child->getBaseTaxableAmount(), $rate, true, false);
                            $hiddenTaxAfterDiscount = $taxCalculation
                                ->calcTaxAmount($child->getTaxableAmount(), $rate, true, false);


                            $hiddentBaseDiscount = $taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                                - $taxCalculation->round($hiddenBaseTaxAfterDiscount);
                            $hiddentDiscount = $taxCalculation->round($hiddenTaxBeforeDiscount)
                                - $taxCalculation->round($hiddenTaxAfterDiscount);

                            $this->_hiddentBaseDiscount += $hiddentBaseDiscount;
                            $this->_hiddentDiscount += $hiddentDiscount;
                        }
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                    if ($this->_helperTax->priceIncludesTax()) {
                        $itemDiscount = $item->getRowTotalInclTax() - $item->getMagestoreBaseDiscount()
                            - $item->getDiscountAmount();
                    } else {
                        $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                            - $item->getBaseDiscountAmount();
                    }
                    $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemDiscount * $rateDiscount);
                    $item->setBaseUseGiftCreditAmount($item->getBaseUseGiftCreditAmount()
                        + $itemDiscount * $rateDiscount);
                    $item->setUseGiftCreditAmount($item->getUseGiftCreditAmount()
                        + $this->priceCurrency->convert($itemDiscount * $rateDiscount));

                    $baseTaxableAmount = $item->getBaseTaxableAmount();
                    $taxableAmount = $item->getTaxableAmount();
                    $item->setBaseTaxBeforeDiscount($item->getBaseTaxBeforeDiscount()
                        - $item->getBaseUseGiftCreditAmount());
                    $item->setTaxBeforeDiscount($item->getTaxBeforeDiscount() - $item->getUseGiftCreditAmount());

                    if ($this->_helperTax->priceIncludesTax()) {
                        $rate = $this->_giftvoucherHelper->getItemRateOnQuote($item->getProduct(), $store);
                        $hiddenBaseTaxBeforeDiscount = $taxCalculation
                            ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                        $hiddenTaxBeforeDiscount = $taxCalculation->calcTaxAmount($taxableAmount, $rate, true, false);

                        $hiddenBaseTaxAfterDiscount = $taxCalculation
                            ->calcTaxAmount($item->getBaseTaxableAmount(), $rate, true, false);
                        $hiddenTaxAfterDiscount = $taxCalculation
                            ->calcTaxAmount($item->getTaxableAmount(), $rate, true, false);


                        $hiddentBaseDiscount = $taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                            - $taxCalculation->round($hiddenBaseTaxAfterDiscount);
                        $hiddentDiscount = $taxCalculation->round($hiddenTaxBeforeDiscount)
                            - $taxCalculation->round($hiddenTaxAfterDiscount);

                        $this->_hiddentBaseDiscount += $hiddentBaseDiscount;
                        $this->_hiddentDiscount += $hiddentDiscount;
                    }
                }
                
            }
        }
        if ($this->_giftvoucherHelper->getGeneralConfig('use_for_ship', $address->getQuote()->getStoreId())) {
            if ($this->_helperTax->shippingPriceIncludesTax()) {
                $shipDiscount = $total->getShippingInclTax() - $total->getMagestoreBaseDiscountForShipping()
                    - $total->getShippingDiscountAmount();
            } else {
                $shipDiscount = $total->getBaseShippingAmount() - $total->getMagestoreBaseDiscountForShipping()
                    - $total->getBaseShippingDiscountAmount();
            }
            $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            $total->setBaseGiftcreditDiscountForShipping($total->getBaseGiftcreditDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            $total->setGiftcreditDiscountForShipping($total->getGiftcreditDiscountForShipping()
                + $this->priceCurrency->convert($shipDiscount * $rateDiscount));
            
            if (!$this->_checkoutSession->setBaseGiftcreditDiscountForShipping()) {
                $this->_checkoutSession
                    ->setBaseGiftcreditDiscountForShipping($total->getBaseGiftcreditDiscountForShipping());
                $this->_checkoutSession
                    ->setGiftcreditDiscountForShipping($total->getGiftcreditDiscountForShipping());
            }
        
            $baseTaxableAmount = $total->getBaseShippingTaxable();
            $taxableAmount = $total->getShippingTaxable();

            $total->setBaseShippingTaxable($total->getBaseShippingTaxable()
                - $total->getBaseGiftcreditDiscountForShipping());
            $total->setShippingTaxable($total->getShippingTaxable() - $total->getGiftcreditDiscountForShipping());

            if ($this->_helperTax->shippingPriceIncludesTax() && $shipDiscount) {
                $rate = $this->getShipingTaxRate($address, $store);
                $hiddenBaseTaxBeforeDiscount = $taxCalculation->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                $hiddenTaxBeforeDiscount = $taxCalculation->calcTaxAmount($taxableAmount, $rate, true, false);

                $hiddenBaseTaxAfterDiscount = $taxCalculation
                    ->calcTaxAmount($total->getBaseShippingTaxable(), $rate, true, false);
                $hiddenTaxAfterDiscount = $taxCalculation
                    ->calcTaxAmount($total->getShippingTaxable(), $rate, true, false);

                $this->_hiddentBaseDiscount += $taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                    - $taxCalculation->round($hiddenBaseTaxAfterDiscount);
                $this->_hiddentDiscount += $taxCalculation->round($hiddenTaxBeforeDiscount)
                    - $taxCalculation->round($hiddenTaxAfterDiscount);
            }
        }
        return $this;
    }

    /**
     * Get the tax rate of shipping
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Store\Model\Store $store
     * @return float
     */
    public function getShipingTaxRate($address, $store)
    {
        $taxCalculation = $this->_objectManager->create('Magento\Tax\Model\Calculation');
        $taxConfig = $this->_objectManager->create('Magento\Tax\Model\Config');
        $request = $taxCalculation->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );
        $request->setProductClassId($taxConfig->getShippingTaxClass($store));
        $rate = $taxCalculation->getRate($request);
        return $rate;
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
