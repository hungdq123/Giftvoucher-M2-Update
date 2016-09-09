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

/**
 * Giftvoucher Total Quote Giftvoucher Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftvoucher extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    protected $_hiddentBaseDiscount = 0;
    protected $_hiddentDiscount = 0;
    
    /*
    * @var \Magestore\Giftvoucher\Helper\Data
    */
    protected $_helperData;
    
    /*
    * @var \Magento\Checkout\Model\Session
    */
    protected $_checkoutSession;
    
    /*
    * @var \Magento\Backend\Model\Session\Quote
    */
    protected $_sessionQuote;
    
    /*
    * @var \Magento\Customer\Model\Session
    */
    protected $_customerSession;
    
    
    /*
    * @var  \Magestore\Giftvoucher\Model\Giftvoucher
    */
    protected $_giftvoucher;
    
    /*
    * @var  \Magento\Tax\Model\Calculation
    */
    protected $_taxCalculation;
    
    /*
    * @var  \Magento\Tax\Model\Config
    */
    protected $_taxConfig;
    
    /*
    * @var \Magento\Tax\Helper\Data
    */
    protected $_helperTax;
    
    /*
    * @var \Magento\Framework\Pricing\PriceCurrencyInterface
    */
    protected $_priceCurrency;
    
    /*
    * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;

    /**
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Giftvoucher\Helper\Data $helperData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\State $appState
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $helperTax
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\State $appState,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $helperTax,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_helperData = $helperData;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionQuote = $sessionQuote;
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        $this->_giftvoucher = $giftvoucher;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxConfig = $taxConfig;
        $this->_helperTax = $helperTax;
        $this->_priceCurrency = $priceCurrency;
        $this->_coreRegistry = $coreRegistry;
        $this->setCode('giftvoucher');
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        $session = $this->_checkoutSession;
        $applyGiftAfterTax = (bool) $this->_helperData->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        if ($applyGiftAfterTax) {
            return $this;
        }
        if ($address->getAddressType() == 'billing' && !$quote->isVirtual() || !$session->getUseGiftCard()) {
            return $this;
        }
        
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        
        if ($codes = $session->getGiftCodes()) {
            $codesArray = array_unique(explode(',', $codes));
            $store = $quote->getStore();
            $baseTotalDiscount = 0;
            $totalDiscount = 0;

            $codesBaseDiscount = array();
            $codesDiscount = array();
            $baseAmountUsed = array();
            foreach ($codesArray as $key => $value) {
                $baseAmountUsed[$value] = '';
            }
            $amountUsed = $baseAmountUsed;
            $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
            if (!is_array($giftMaxUseAmount)) {
                $giftMaxUseAmount = array();
            }
            
            foreach ($codesArray as $key => $code) {
                $model = $this->_giftvoucher->loadByCode($code);
                if ($model->getStatus() != \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                    || $model->getBalance() == 0 || $model->getBaseBalance() <= $baseAmountUsed[$code]
                    || !$model->validate($address)
                ) {
                    $codesBaseDiscount[] = 0;
                    $codesDiscount[] = 0;
                } else {
                    if ($this->_helperData->canUseCode($model)) {
                        $baseBalance = $model->getBaseBalance() - $baseAmountUsed[$code];
                        if (array_key_exists($code, $giftMaxUseAmount)) {
                            $maxDiscount = max(floatval($giftMaxUseAmount[$code]), 0)
                                / $this->_priceCurrency->convert(1, false, false);
                            $baseBalance = min($baseBalance, $maxDiscount);
                        }
                        if ($baseBalance > 0) {
                            $baseDiscountTotal = 0;
                            foreach ($address->getAllItems() as $item) {
                                if ($item->getParentItemId()) {
                                    continue;
                                }
                                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                    foreach ($item->getChildren() as $child) {
                                        if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher'
                                            && $model->getActions()->validate($child)
                                        ) {
                                            if ($this->_helperTax->priceIncludesTax()) {
                                                $itemDiscount = $child->getRowTotalInclTax()
                                                    - $child->getMagestoreBaseDiscount() - $child->getDiscountAmount();
                                            } else {
                                                $itemDiscount = $child->getBaseRowTotal()
                                                    - $child->getMagestoreBaseDiscount()
                                                    - $child->getBaseDiscountAmount();
                                            }
                                            $baseDiscountTotal += $itemDiscount;
                                        }
                                    }
                                } elseif ($item->getProduct()) {
                                    if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher'
                                        && $model->getActions()->validate($item)
                                    ) {
                                        if ($this->_helperTax->priceIncludesTax()) {
                                            $itemDiscount = $item->getRowTotalInclTax()
                                                - $item->getMagestoreBaseDiscount() - $item->getDiscountAmount();
                                        } else {
                                            $itemDiscount = $item->getBaseRowTotal()
                                                - $item->getMagestoreBaseDiscount()
                                                - $item->getBaseDiscountAmount();
                                        }
                                        $baseDiscountTotal += $itemDiscount;
                                    }
                                }
                            }
                            if ($this->_helperData->getStoreConfig(
                                'giftvoucher/general/use_for_ship',
                                $address->getQuote()->getStoreId()
                            )) {
                                if ($this->_helperTax->shippingPriceIncludesTax()) {
                                    $shipDiscount = $address->getShippingInclTax()
                                        - $address->getMagestoreBaseDiscountForShipping()
                                        - $address->getShippingDiscountAmount();
                                } else {
                                    $shipDiscount = $address->getBaseShippingAmount()
                                        - $address->getMagestoreBaseDiscountForShipping()
                                        - $address->getBaseShippingDiscountAmount();
                                }
                                $baseDiscountTotal += $shipDiscount;
                            }
                        }
                    }
                    if (!isset($baseDiscountTotal)) {
                        $baseDiscountTotal = 0;
                    }
                    if (!isset($baseBalance)) {
                        $baseBalance = 0;
                    }
                    $baseDiscount = min($baseDiscountTotal, $baseBalance);
                    $discount = $this->_priceCurrency->convert($baseDiscount);
                    if ($baseDiscountTotal > 0) {
                        $calculate = $baseDiscount / $baseDiscountTotal;
                    } else {
                        $calculate = 0;
                    }
                    $this->prepareGiftDiscountForItem($total, $address, $calculate, $store, $model, $baseDiscount);

                    $baseAmountUsed[$code] += $baseDiscount;
                    $amountUsed[$code] = $this->_priceCurrency->convert($baseAmountUsed[$code]);

                    $baseTotalDiscount += $baseDiscount;
                    $totalDiscount += $discount;

                    $codesBaseDiscount[] = $baseDiscount;
                    $codesDiscount[] = $discount;
                }
            }
            
            $codesBaseDiscountString = implode(',', $codesBaseDiscount);
            $codesDiscountString = implode(',', $codesDiscount);

            //update session
            $session->setBaseAmountUsed(implode(',', $baseAmountUsed));

            $session->setBaseGiftVoucherDiscount($session->getBaseGiftVoucherDiscount() + $baseTotalDiscount);
            $session->setGiftVoucherDiscount($session->getGiftVoucherDiscount() + $totalDiscount);

            $session->setCodesBaseDiscount($session->getBaseAmountUsed());
            $session->setCodesDiscount(implode(',', $amountUsed));
            
            $session->setGiftvoucherBaseHiddenTaxAmount($this->_hiddentBaseDiscount);
            $session->setGiftvoucherHiddenTaxAmount($this->_hiddentDiscount);

            //update address

            $total->setBaseGrandTotal($total->getBaseGrandTotal()
                + $this->_hiddentBaseDiscount - $baseTotalDiscount);
            $total->setGrandTotal($total->getGrandTotal() + $this->_hiddentDiscount - $totalDiscount);

            $total->setBaseGiftVoucherDiscount($baseTotalDiscount);
            $total->setGiftVoucherDiscount($totalDiscount);

            $total->setGiftCodes($codes);
            $total->setCodesBaseDiscount($codesBaseDiscountString);
            $total->setCodesDiscount($codesDiscountString);

            $total->setGiftvoucherBaseHiddenTaxAmount($this->_hiddentBaseDiscount);
            $total->setGiftvoucherHiddenTaxAmount($this->_hiddentDiscount);
            
            $total->setMagestoreBaseDiscount($address->getMagestoreBaseDiscount() + $baseTotalDiscount);

            $total->addTotalAmount('giftvoucher', -$totalDiscount);
            $total->addBaseTotalAmount('giftvoucher', -$baseTotalDiscount);
            //update quote
            $quote->setBaseGiftVoucherDiscount($baseTotalDiscount);
            $quote->setGiftVoucherDiscount($totalDiscount);
            $quote->setGiftCodes($codes);
            $quote->setCodesBaseDiscount($codesBaseDiscountString);
            $quote->setCodesDiscount($codesDiscountString);
        }
        $this->recalculateGiftDiscount($address);

        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $applyGiftAfterTax =
            (bool) $this->_helperData->getGeneralConfig('apply_after_tax', $quote->getStoreId());
        if ($applyGiftAfterTax) {
            return $result;
        }
        
        if ($giftVoucherDiscount = $total->getGiftVoucherDiscount()) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Gift Card'),
                'value' => -$giftVoucherDiscount,
                'gift_codes' => $quote->getGiftCodes(),
                'codes_base_discount' => $quote->getCodesBaseDiscount(),
                'codes_discount' => $quote->getCodesDiscount()
            ];
        }
        return $result;
    }

    /**
     * Prepare Gift Discount For Item
     *
     * @param @param \Magento\Quote\Model\Quote\Address $address
     * @param float $rateDiscount
     * @param \Magento\Store\Model\Store $store
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $model
     * @param float $baseDiscount
     * @return Magestore\Giftvoucher\Model\Total\Quote\Giftvoucher
     */
    public function prepareGiftDiscountForItem(
        \Magento\Quote\Model\Quote\Address\Total $total,
        \Magento\Quote\Model\Quote\Address $address,
        $rateDiscount,
        $store,
        $model,
        $baseDiscount
    ) {
        $session = $this->_checkoutSession;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $discountGiftcardCodes = 0;
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher'
                        && $model->getActions()->validate($child)
                    ) {
                        if ($this->_helperTax->priceIncludesTax()) {
                            $itemDiscount = $child->getRowTotalInclTax() - $child->getMagestoreBaseDiscount()
                                - $child->getDiscountAmount();
                        } else {
                            $itemDiscount = $child->getBaseRowTotal() - $child->getMagestoreBaseDiscount()
                                - $child->getBaseDiscountAmount();
                        }

                        $child->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount()
                            + $itemDiscount * $rateDiscount);
                        $child->setBaseGiftVoucherDiscount($child->getBaseGiftVoucherDiscount()
                            + $itemDiscount * $rateDiscount);
                        $child->setGiftVoucherDiscount($child->getGiftVoucherDiscount()
                            + $this->_priceCurrency->convert($itemDiscount * $rateDiscount));

                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                        $taxableAmount = $child->getTaxableAmount();

                        $child->setBaseTaxableAmount($child->getBaseTaxableAmount()
                            - $child->getBaseGiftVoucherDiscount());
                        $child->setTaxableAmount($child->getTaxableAmount() - $child->getGiftVoucherDiscount());

                        if ($this->_helperTax->priceIncludesTax()) {
                            $rate = $this->_helperData->getItemRateOnQuote($child->getProduct(), $store);
                            $hiddenBaseTaxBeforeDiscount = $this->_taxCalculation
                                ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                            $hiddenTaxBeforeDiscount = $this->_taxCalculation
                                ->calcTaxAmount($taxableAmount, $rate, true, false);

                            $hiddenBaseTaxAfterDiscount = $this->_taxCalculation
                                ->calcTaxAmount($child->getBaseTaxableAmount(), $rate, true, false);
                            $hiddenTaxAfterDiscount = $this->_taxCalculation
                                ->calcTaxAmount($child->getTaxableAmount(), $rate, true, false);

                            $hiddentBaseDiscount = $this->_taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                                - $this->_taxCalculation->round($hiddenBaseTaxAfterDiscount);
                            $hiddentDiscount = $this->_taxCalculation->round($hiddenTaxBeforeDiscount)
                                - $this->_taxCalculation->round($hiddenTaxAfterDiscount);

                            $child->setGiftvoucherBaseHiddenTaxAmount($hiddentBaseDiscount);
                            $child->setGiftvoucherHiddenTaxAmount($hiddentDiscount);

                            $this->_hiddentBaseDiscount += $hiddentBaseDiscount;
                            $this->_hiddentDiscount += $hiddentDiscount;
                        }
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher'
                    && $model->getActions()->validate($item)
                ) {
                    if ($this->_helperTax->priceIncludesTax()) {
                        $itemDiscount = $item->getRowTotalInclTax() - $item->getMagestoreBaseDiscount()
                            - $item->getDiscountAmount();
                    } else {
                        $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                            - $item->getBaseDiscountAmount();
                    }
                    
                    $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemDiscount * $rateDiscount);
                    $item->setBaseGiftVoucherDiscount($item->getBaseGiftVoucherDiscount()
                        + $itemDiscount * $rateDiscount);
                    $item->setGiftVoucherDiscount($item->getGiftVoucherDiscount()
                        + $this->_priceCurrency->convert($itemDiscount * $rateDiscount));

                    $baseTaxableAmount = $item->getBaseTaxableAmount();
                    $taxableAmount = $item->getTaxableAmount();
                    $item->setBaseTaxableAmount($item->getBaseTaxableAmount() - $item->getBaseGiftVoucherDiscount());
                    $item->setTaxableAmount($item->getTaxableAmount() - $item->getGiftVoucherDiscount());

                    if ($this->_helperTax->priceIncludesTax()) {
                        $rate = $this->_helperData->getItemRateOnQuote($item->getProduct(), $store);
                        $hiddenBaseTaxBeforeDiscount = $this->_taxCalculation
                            ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                        $hiddenTaxBeforeDiscount = $this->_taxCalculation
                            ->calcTaxAmount($taxableAmount, $rate, true, false);

                        $hiddenBaseTaxAfterDiscount = $this->_taxCalculation
                            ->calcTaxAmount($item->getBaseTaxableAmount(), $rate, true, false);
                        $hiddenTaxAfterDiscount = $this->_taxCalculation
                            ->calcTaxAmount($item->getTaxableAmount(), $rate, true, false);

                        $hiddentBaseDiscount = $this->_taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                            - $this->_taxCalculation->round($hiddenBaseTaxAfterDiscount);
                        $hiddentDiscount = $this->_taxCalculation->round($hiddenTaxBeforeDiscount)
                            - $this->_taxCalculation->round($hiddenTaxAfterDiscount);

                        $item->setGiftvoucherBaseHiddenTaxAmount($hiddentBaseDiscount);
                        $item->setGiftvoucherHiddenTaxAmount($hiddentDiscount);

                        $this->_hiddentBaseDiscount += $hiddentBaseDiscount;
                        $this->_hiddentDiscount += $hiddentDiscount;
                    }
                }
            }
       
        }
        if ($this->_helperData->getStoreConfig(
            'giftvoucher/general/use_for_ship',
            $address->getQuote()->getStoreId()
        )) {
            if ($this->_helperTax->shippingPriceIncludesTax()) {
                $shipDiscount = $total->getShippingInclTax() - $total->getMagestoreBaseDiscountForShipping()
                    - $total->getShippingDiscountAmount();
            } else {
                $shipDiscount = $total->getBaseShippingAmount() - $total->getMagestoreBaseDiscountForShipping()
                    - $total->getBaseShippingDiscountAmount();
            }

            $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            $total->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping()
                + $shipDiscount * $rateDiscount);
            $total->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping()
                + $this->_priceCurrency->convert($shipDiscount * $rateDiscount));
            
            $session->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping());
            $session->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping());
            
            $baseTaxableAmount = $total->getBaseShippingTaxable();
            $taxableAmount = $total->getShippingTaxable();

            $total->setBaseShippingTaxable($total->getBaseShippingTaxable()
                - $total->getBaseGiftvoucherDiscountForShipping());
            $total->setShippingTaxable($total->getShippingTaxable()
                - $total->getGiftvoucherDiscountForShipping());

            if ($this->_helperTax->shippingPriceIncludesTax() && $shipDiscount) {
                $rate = $this->getShipingTaxRate($address, $store);
                $hiddenBaseTaxBeforeDiscount = $this->_taxCalculation
                    ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
                $hiddenTaxBeforeDiscount = $this->_taxCalculation
                    ->calcTaxAmount($taxableAmount, $rate, true, false);

                $hiddenBaseTaxAfterDiscount = $this->_taxCalculation
                    ->calcTaxAmount($total->getBaseShippingTaxable(), $rate, true, false);
                $hiddenTaxAfterDiscount = $this->_taxCalculation
                    ->calcTaxAmount($total->getShippingTaxable(), $rate, true, false);

                $hiddentBaseShippingDiscount = $this->_taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                    - $this->_taxCalculation->round($hiddenBaseTaxAfterDiscount);
                $hiddentShippingDiscount = $this->_taxCalculation->round($hiddenTaxBeforeDiscount)
                    - $this->_taxCalculation->round($hiddenTaxAfterDiscount);

                $total->setGiftvoucherBaseShippingHiddenTaxAmount($hiddentBaseShippingDiscount);
                $total->setGiftvoucherShippingHiddenTaxAmount($hiddentShippingDiscount);
                
                $session->setGiftvoucherBaseShippingHiddenTaxAmount($hiddentBaseShippingDiscount);
                $session->setGiftvoucherShippingHiddenTaxAmount($hiddentShippingDiscount);

                $this->_hiddentBaseDiscount += $hiddentBaseShippingDiscount;
                $this->_hiddentDiscount += $hiddentShippingDiscount;
            }
        }
        return $this;
    }

    /**
     * Get the tax rate of shipping
     *
     * @param @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Store\Model\Store $store
     * @return float
     */
    public function getShipingTaxRate($address, $store)
    {
        $request = $this->_taxCalculation->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );
        $request->setProductClassId($this->_taxConfig->getShippingTaxClass($store));
        $rate = $this->_taxCalculation->getRate($request);
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

    public function recalculateGiftDiscount($address)
    {
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher') {
                        $child->setDiscountAmount($child->getDiscountAmount()+$child->getGiftVoucherDiscount()
                            +$child->getUseGiftCreditAmount());
                        $child->setBaseDiscountAmount($child->getBaseDiscountAmount()
                            +$child->getBaseGiftVoucherDiscount()+$child->getBaseUseGiftCreditAmount());
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher') {
                    $item->setDiscountAmount($item->getDiscountAmount()+$item->getGiftVoucherDiscount()
                        +$item->getUseGiftCreditAmount());
                    $item->setBaseDiscountAmount($item->getBaseDiscountAmount()+$item->getBaseGiftVoucherDiscount()
                        +$item->getBaseUseGiftCreditAmount());
                }
            }
        }
    }
}
