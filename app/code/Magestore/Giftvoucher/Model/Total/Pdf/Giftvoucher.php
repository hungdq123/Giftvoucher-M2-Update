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
namespace Magestore\Giftvoucher\Model\Total\Pdf;

/**
 * Giftvoucher Total Pdf Giftvoucher Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftvoucher extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    public function getTotalsForDisplay()
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
        $totals = array(array(
                'label' => __('Gift Card (%1):', $this->getGiftCodes()),
                'amount' => $amount,
                'font_size' => $fontSize,
            )
        );
        return $totals;
    }

    public function getAmount()
    {
        if ($this->getSource()->getGiftVoucherDiscount()) {
            return -$this->getSource()->getGiftVoucherDiscount();
        }
    }

    public function getGiftCodes()
    {
        return $this->getOrder()->getGiftCodes();
    }
}
