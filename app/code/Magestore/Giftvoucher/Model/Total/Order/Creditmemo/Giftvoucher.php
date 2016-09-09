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

namespace Magestore\Giftvoucher\Model\Total\Order\Creditmemo;

/**
 * Giftvoucher Total Order Creditmemo Giftvoucher Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftvoucher extends \Magento\Sales\Model\Order\Total\AbstractTotal
{

    /**
     * Collect creditmemo giftvoucher
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if (!$order->getGiftVoucherDiscount() && !$order->getUseGiftCreditAmount()) {
            return $this;
        }

        $creditmemo->setUseGiftCreditAmount(0);
        $creditmemo->setBaseUseGiftCreditAmount(0);
        $creditmemo->setBaseGiftVoucherDiscount(0);
        $creditmemo->setGiftVoucherDiscount(0);

        $totalDiscountAmountGiftvoucher = 0;
        $baseTotalDiscountAmountGiftvoucher = 0;
        $totalDiscountAmountCredit = 0;
        $baseTotalDiscountAmountCredit = 0;

        $totalGiftvoucherDiscountRefunded = 0;
        $baseGiftvoucherTotalDiscountRefunded = 0;
        $totalGiftcreditDiscountRefunded = 0;
        $baseGiftcreditTotalDiscountRefunded = 0;

        $hiddenGiftvoucherTaxRefunded = 0;
        $baseGiftvoucherHiddenTaxRefunded = 0;
        $hiddenGiftcreditTaxRefunded = 0;
        $baseGiftcreditHiddenTaxRefunded = 0;

        $totalGiftvoucherHiddenTax = 0;
        $baseTotalGiftvoucherHiddenTax = 0;
        $baseTotalGiftcreditHiddenTax = 0;
        $totalGiftcreditHiddenTax = 0;

        foreach ($order->getCreditmemosCollection() as $existedCreditmemo) {
            if ($existedCreditmemo->getGiftVoucherDiscount() || $existedCreditmemo->getUseGiftCreditAmount()) {
                $totalGiftvoucherDiscountRefunded += $existedCreditmemo->getGiftVoucherDiscount();
                $baseGiftvoucherTotalDiscountRefunded += $existedCreditmemo->getBaseGiftvoucherDiscount();
                $totalGiftcreditDiscountRefunded += $existedCreditmemo->getUseGiftCreditAmount();
                $baseGiftcreditTotalDiscountRefunded += $existedCreditmemo->getBaseUseGiftCreditAmount();

                $hiddenGiftvoucherTaxRefunded += $existedCreditmemo->getGiftvoucherHiddenTaxAmount();
                $baseGiftvoucherHiddenTaxRefunded += $existedCreditmemo->getGiftvoucherBaseHiddenTaxAmount();
                $hiddenGiftcreditTaxRefunded += $existedCreditmemo->getGiftcreditHiddenTaxAmount();
                $baseGiftcreditHiddenTaxRefunded += $existedCreditmemo->getGiftcreditBaseHiddenTaxAmount();
            }
        }

        $baseShippingAmount = $creditmemo->getBaseShippingAmount();
        if ($baseShippingAmount) {
            $baseTotalDiscountAmountGiftvoucher = $baseTotalDiscountAmountGiftvoucher + ($baseShippingAmount *
                $order->getBaseGiftvoucherDiscountForShipping() / $order->getBaseShippingAmount());
            $totalDiscountAmountGiftvoucher = $totalDiscountAmountGiftvoucher + ($order->getShippingAmount() *
                $baseTotalDiscountAmountGiftvoucher / $order->getBaseShippingAmount() );
            $baseTotalDiscountAmountCredit = $baseTotalDiscountAmountCredit + ($baseShippingAmount *
                $order->getBaseGiftcreditDiscountForShipping() / $order->getBaseShippingAmount());
            $totalDiscountAmountCredit = $totalDiscountAmountCredit + ($order->getShippingAmount() *
                $baseTotalDiscountAmountCredit / $order->getBaseShippingAmount());

            $baseTotalGiftvoucherHiddenTax = $baseShippingAmount
                * $order->getGiftvoucherBaseShippingHiddenTaxAmount() / $order->getBaseShippingAmount();
            $totalGiftvoucherHiddenTax = $order->getGiftvoucherShippingHiddenTaxAmount()
                * $baseTotalGiftvoucherHiddenTax / $order->getBaseShippingAmount();
            $baseTotalGiftcreditHiddenTax = $baseShippingAmount
                * $order->getGiftcreditBaseShippingHiddenTaxAmount() / $order->getBaseShippingAmount();
            $totalGiftcreditHiddenTax = $order->getGiftcreditShippingHiddenTaxAmount()
                * $baseTotalGiftcreditHiddenTax / $order->getBaseShippingAmount();
        }

        foreach ($creditmemo->getAllItems() as $item) {
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
            $creditmemoItemQty = $item->getQty();

            if ($orderItemDiscountGiftvoucher && $orderItemQty) {
                $discount = $creditmemo->roundPrice(
                    $orderItemDiscountGiftvoucher / $orderItemQty * $creditmemoItemQty,
                    'regular',
                    false
                );
                $baseDiscount = $creditmemo->roundPrice(
                    $baseOrderItemDiscountGiftvoucher / $orderItemQty * $creditmemoItemQty,
                    'base',
                    false
                );

                $totalDiscountAmountGiftvoucher += $discount;
                $baseTotalDiscountAmountGiftvoucher += $baseDiscount;

                $totalGiftvoucherHiddenTax += $creditmemo->roundPrice(
                    $orderItemGiftvoucherHiddenTax / $orderItemQty * $creditmemoItemQty,
                    'regular',
                    false
                );
                $baseTotalGiftvoucherHiddenTax += $creditmemo->roundPrice(
                    $baseOrderItemGiftvoucherHiddenTax / $orderItemQty * $creditmemoItemQty,
                    'base',
                    false
                );

            }
            if ($orderItemDiscountCredit && $orderItemQty) {
                $discount = $creditmemo->roundPrice(
                    $orderItemDiscountCredit / $orderItemQty * $creditmemoItemQty,
                    'regular',
                    false
                );
                $baseDiscount = $creditmemo->roundPrice(
                    $baseOrderItemDiscountCredit / $orderItemQty * $creditmemoItemQty,
                    'base',
                    false
                );

                $totalDiscountAmountCredit += $discount;
                $baseTotalDiscountAmountCredit += $baseDiscount;

                $totalGiftcreditHiddenTax += $creditmemo->roundPrice(
                    $orderItemGiftcreditHiddenTax / $orderItemQty * $creditmemoItemQty,
                    'regular',
                    false
                );
                $baseTotalGiftcreditHiddenTax += $creditmemo->roundPrice(
                    $baseOrderItemGiftcreditHiddenTax / $orderItemQty * $creditmemoItemQty,
                    'base',
                    false
                );

            }
        }
        $allowedGiftvoucherBaseHiddenTax = $order->getGiftvoucherHiddenTaxAmount() - $hiddenGiftvoucherTaxRefunded;
        $allowedGiftvoucherHiddenTax = $order->getGiftvoucherBaseHiddenTaxAmount()
            - $baseGiftvoucherHiddenTaxRefunded;
        $allowedGiftcreditBaseHiddenTax = $order->getGiftcreditHiddenTaxAmount() - $hiddenGiftcreditTaxRefunded;
        $allowedGiftcreditHiddenTax = $order->getGiftcreditBaseHiddenTaxAmount()
            - $baseGiftcreditHiddenTaxRefunded;

        $totalGiftvoucherHiddenTax = min($allowedGiftvoucherBaseHiddenTax, $totalGiftvoucherHiddenTax);
        $baseTotalGiftvoucherHiddenTax = min($allowedGiftvoucherHiddenTax, $baseTotalGiftvoucherHiddenTax);
        $totalGiftcreditHiddenTax = min($allowedGiftcreditBaseHiddenTax, $totalGiftcreditHiddenTax);
        $baseTotalGiftcreditHiddenTax = min($allowedGiftcreditHiddenTax, $baseTotalGiftcreditHiddenTax);


        $creditmemo->setBaseGiftVoucherDiscount($baseTotalDiscountAmountGiftvoucher);
        $creditmemo->setGiftVoucherDiscount($totalDiscountAmountGiftvoucher);

        $creditmemo->setBaseUseGiftCreditAmount($baseTotalDiscountAmountCredit);
        $creditmemo->setUseGiftCreditAmount($totalDiscountAmountCredit);

        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmountCredit
            - $baseTotalDiscountAmountGiftvoucher + $totalGiftvoucherHiddenTax + $totalGiftcreditHiddenTax);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmountCredit
            - $totalDiscountAmountGiftvoucher + $baseTotalGiftvoucherHiddenTax + $baseTotalGiftcreditHiddenTax);
    }

    /**
     * Check credit memo is last or not
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }
}
