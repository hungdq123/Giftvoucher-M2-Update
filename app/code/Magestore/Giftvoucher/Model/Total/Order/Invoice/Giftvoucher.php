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
namespace Magestore\Giftvoucher\Model\Total\Order\Invoice;

/**
 * Giftvoucher Total Order Invoice Giftvoucher Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftvoucher extends \Magento\Sales\Model\Order\Total\AbstractTotal
{

    /**
     * Collect invoice giftvoucher
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if (!$order->getGiftVoucherDiscount() && !$order->getUseGiftCreditAmount()) {
            return $this;
        }

        $invoice->setUseGiftCreditAmount(0);
        $invoice->setBaseUseGiftCreditAmount(0);
        $invoice->setBaseGiftVoucherDiscount(0);
        $invoice->setGiftVoucherDiscount(0);

        $totalDiscountAmountGiftvoucher = 0;
        $baseTotalDiscountAmountGiftvoucher = 0;
        $totalDiscountAmountCredit = 0;
        $baseTotalDiscountAmountCredit = 0;

        $totalGiftvoucherDiscountInvoiced = 0;
        $baseTotalGiftvoucherDiscountInvoiced = 0;
        $totalGiftcreditDiscountInvoiced = 0;
        $baseTotalGiftcreditDiscountInvoiced = 0;

        $hiddenGiftvoucherTaxInvoiced = 0;
        $baseHiddenGiftvoucherTaxInvoiced = 0;
        $hiddenGiftcreditTaxInvoiced = 0;
        $baseHiddenGiftcreditTaxInvoiced = 0;

        $totalGiftvoucherHiddenTax = 0;
        $baseTotalGiftvoucherHiddenTax = 0;
        $totalGiftcreditHiddenTax = 0;
        $baseTotalGiftcreditHiddenTax = 0;

        $addShippingDicount = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getGiftVoucherDiscount() || $previusInvoice->getUseGiftCreditAmount()) {
                $addShippingDicount = false;

                $totalGiftvoucherDiscountInvoiced += $previusInvoice->getGiftVoucherDiscount();
                $baseTotalGiftvoucherDiscountInvoiced += $previusInvoice->getBaseGiftvoucherDiscount();
                $totalGiftcreditDiscountInvoiced += $previusInvoice->getUseGiftCreditAmount();
                $baseTotalGiftcreditDiscountInvoiced += $previusInvoice->getBaseUseGiftCreditAmount();

                $hiddenGiftvoucherTaxInvoiced += $previusInvoice->getGiftvoucherHiddenTaxAmount();
                $baseHiddenGiftvoucherTaxInvoiced += $previusInvoice->getGiftvoucherBaseHiddenTaxAmount();
                $hiddenGiftcreditTaxInvoiced += $previusInvoice->getGiftcreditHiddenTaxAmount();
                $baseHiddenGiftcreditTaxInvoiced += $previusInvoice->getGiftcreditBaseHiddenTaxAmount();
            }
        }

        if ($addShippingDicount) {
            $totalDiscountAmountGiftvoucher = $totalDiscountAmountGiftvoucher
                + $order->getGiftvoucherDiscountForShipping();
            $baseTotalDiscountAmountGiftvoucher = $baseTotalDiscountAmountGiftvoucher
                + $order->getBaseGiftvoucherDiscountForShipping();
            $totalDiscountAmountCredit = $totalDiscountAmountCredit + $order->getGiftcreditDiscountForShipping();
            $baseTotalDiscountAmountCredit = $baseTotalDiscountAmountCredit
                + $order->getBaseGiftcreditDiscountForShipping();

            $totalGiftvoucherHiddenTax += $order->getGiftvoucherShippingHiddenTaxAmount();
            $baseTotalGiftvoucherHiddenTax += $order->getGiftvoucherBaseShippingHiddenTaxAmount();
            $totalGiftcreditHiddenTax += $order->getGiftcreditShippingHiddenTaxAmount();
            $baseTotalGiftcreditHiddenTax += $order->getGiftcreditBaseShippingHiddenTaxAmount();
        }

        

        if ($invoice->isLast()) {
            $totalDiscountAmountGiftvoucher = $order->getGiftVoucherDiscount() - $totalGiftvoucherDiscountInvoiced;
            $baseTotalDiscountAmountGiftvoucher = $order->getBaseGiftVoucherDiscount()
                - $baseTotalGiftvoucherDiscountInvoiced;
            $totalDiscountAmountCredit = $order->getUseGiftCreditAmount() - $totalGiftcreditDiscountInvoiced;
            $baseTotalDiscountAmountCredit = $order->getBaseUseGiftCreditAmount()
                - $baseTotalGiftcreditDiscountInvoiced;

            $totalGiftvoucherHiddenTax = $order->getGiftvoucherHiddenTaxAmount() - $hiddenGiftvoucherTaxInvoiced;
            $baseTotalGiftvoucherHiddenTax = $order->getGiftvoucherBaseHiddenTaxAmount()
                - $baseHiddenGiftvoucherTaxInvoiced;
            $totalGiftcreditHiddenTax = $order->getGiftcreditHiddenTaxAmount() - $hiddenGiftcreditTaxInvoiced;
            $baseTotalGiftcreditHiddenTax = $order->getGiftcreditBaseHiddenTaxAmount()
                - $baseHiddenGiftcreditTaxInvoiced;
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $orderItemDiscountGiftvoucher = (float) $orderItem->getGiftVoucherDiscount();
                $baseOrderItemDiscountGiftvoucher = (float) $orderItem->getBaseGiftVoucherDiscount();
                $orderItemDiscountCredit = (float) $orderItem->getUseGiftCreditAmount();
                $baseOrderItemDiscountCredit = (float) $orderItem->getBaseUseGiftCreditAmount();

                $orderItemGiftvoucherHiddenTax = (float) $orderItem->getGiftvoucherHiddenTaxAmount();
                $baseOrderItemGiftvoucherHiddenTax = (float) $orderItem->getGiftvoucherBaseHiddenTaxAmount();
                $orderItemGiftcreditHiddenTax = (float) $orderItem->getGiftcreditHiddenTaxAmount();
                $baseOrderItemGiftcreditHiddenTax = (float) $orderItem->getGiftcreditBaseHiddenTaxAmount();

                $orderItemQty = $orderItem->getQtyOrdered();
                $invoiceItemQty = $item->getQty();

                if ($orderItemDiscountGiftvoucher && $orderItemQty) {
                    $discount = $invoice->roundPrice(
                        $orderItemDiscountGiftvoucher / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                    $baseDiscount = $invoice->roundPrice(
                        $baseOrderItemDiscountGiftvoucher / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    $totalDiscountAmountGiftvoucher += $discount;
                    $baseTotalDiscountAmountGiftvoucher += $baseDiscount;

                    $totalGiftvoucherHiddenTax += $invoice->roundPrice(
                        $orderItemGiftvoucherHiddenTax / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                    $baseTotalGiftvoucherHiddenTax += $invoice->roundPrice(
                        $baseOrderItemGiftvoucherHiddenTax / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    
                }

                if ($orderItemDiscountCredit && $orderItemQty) {
                    $discount = $invoice->roundPrice(
                        $orderItemDiscountCredit / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                    $baseDiscount = $invoice->roundPrice(
                        $baseOrderItemDiscountCredit / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    
                    $totalDiscountAmountCredit += $discount;
                    $baseTotalDiscountAmountCredit += $baseDiscount;

                    $totalGiftcreditHiddenTax += $invoice->roundPrice(
                        $orderItemGiftcreditHiddenTax / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                    $baseTotalGiftcreditHiddenTax += $invoice->roundPrice(
                        $baseOrderItemGiftcreditHiddenTax / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    
                }
            }

            $allowedGiftvoucherBaseHiddenTax = $order->getGiftvoucherHiddenTaxAmount() - $hiddenGiftvoucherTaxInvoiced;
            $allowedGiftvoucherHiddenTax = $order->getGiftvoucherBaseHiddenTaxAmount()
                - $baseHiddenGiftvoucherTaxInvoiced;
            $allowedGiftcreditBaseHiddenTax = $order->getGiftcreditHiddenTaxAmount() - $hiddenGiftcreditTaxInvoiced;
            $allowedGiftcreditHiddenTax = $order->getGiftcreditBaseHiddenTaxAmount() - $baseHiddenGiftcreditTaxInvoiced;

            $totalGiftvoucherHiddenTax = min($allowedGiftvoucherBaseHiddenTax, $totalGiftvoucherHiddenTax);
            $baseTotalGiftvoucherHiddenTax = min($allowedGiftvoucherHiddenTax, $baseTotalGiftvoucherHiddenTax);
            $totalGiftcreditHiddenTax = min($allowedGiftcreditBaseHiddenTax, $totalGiftcreditHiddenTax);
            $baseTotalGiftcreditHiddenTax = min($allowedGiftcreditHiddenTax, $baseTotalGiftcreditHiddenTax);
        }

        $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() + $totalGiftvoucherHiddenTax
            + $totalGiftcreditHiddenTax - $order->getGiftvoucherShippingHiddenTaxAmount()
            - $order->getGiftcreditDiscountForShipping());
        $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() + $totalGiftvoucherHiddenTax
            + $totalGiftcreditHiddenTax - $order->getBaseGiftvoucherDiscountForShipping()
            - $order->getBaseGiftcreditDiscountForShipping());

        $invoice->setBaseGiftVoucherDiscount($baseTotalDiscountAmountGiftvoucher);
        $invoice->setGiftVoucherDiscount($totalDiscountAmountGiftvoucher);

        $invoice->setBaseUseGiftCreditAmount($baseTotalDiscountAmountCredit);
        $invoice->setUseGiftCreditAmount($totalDiscountAmountCredit);

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmountCredit
            - $baseTotalDiscountAmountGiftvoucher + $totalGiftvoucherHiddenTax + $totalGiftcreditHiddenTax);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmountCredit
            - $totalDiscountAmountGiftvoucher + $baseTotalGiftvoucherHiddenTax + $baseTotalGiftcreditHiddenTax);

        return $this;
    }
}
