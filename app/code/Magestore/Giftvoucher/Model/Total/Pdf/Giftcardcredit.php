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
 * Giftvoucher Total Pdf Giftcardcredit Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftcardcredit extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    public function getTotalsForDisplay()
    {
        
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
        $totals = array(array(
                'label' => __('Gift Card credit:'),
                'amount' => $amount,
                'font_size' => $fontSize,
            )
        );
        return $totals;
    }

    public function getAmount()
    {
        if ($this->getSource()->getUseGiftCreditAmount()) {
            return -$this->getSource()->getUseGiftCreditAmount();
        }
        return -$this->getOrder()->getUseGiftCreditAmount();
    }
}
