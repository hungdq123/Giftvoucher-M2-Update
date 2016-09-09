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
namespace Magestore\Giftvoucher\Block;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftvoucher block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftvoucher extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherHelper;

    /**
     * @var \Magestore\Giftvoucher\Model\Session
     */
    protected $_giftvoucherSession;

    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magestore\Giftvoucher\Model\Status
     */
    protected $_giftvoucherStatus;

    /**
     * Giftvoucher constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magestore\Giftvoucher\Model\Session $giftvoucherSession
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magestore\Giftvoucher\Model\Status $giftvoucherStatus
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magestore\Giftvoucher\Model\Session $giftvoucherSession,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\Currency $currency,
        \Magestore\Giftvoucher\Model\Status $giftvoucherStatus
    ) {
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_giftvoucherSession = $giftvoucherSession;
        $this->_giftvoucher = $giftvoucher;
        $this->_currency = $currency;
        $this->_giftvoucherStatus = $giftvoucherStatus;
        parent::__construct($context);
    }
    public function getGiftvoucherHelper()
    {
        return $this->_giftvoucherHelper;
    }
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('giftvoucher/index/check');
    }

    public function getCode()
    {
        return $this->_request->getParam('code', null);
    }

    public function getCodeTxt()
    {
        return $this->_giftvoucherHelper->getHiddenCode($this->getCode());
    }

    public function getGiftVoucher()
    {
        if ($code = $this->getCode()) {
            $codes = $this->_giftvoucherSession->getCodesInvalid();
            $codes[] = $code;
            $codes = array_unique($codes);
            if ($max = $this->_giftvoucherHelper->getGeneralConfig('maximum')) {
                if (count($codes) > $max) {
                    return null;
                }
            }

            $this->_giftvoucherSession->setCodes($codes);
            $giftVoucher = $this->_giftvoucher->loadByCode($code);
            if ($giftVoucher->getId()) {
                return $giftVoucher;
            }
        }
        return null;
    }

    /**
     * Returns the formatted balance
     *
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftVoucher
     * @return string
     */
    public function getBalanceFormat($giftVoucher)
    {
        $currency = $this->_currency->load($giftVoucher->getCurrency());
        return $currency->format($giftVoucher->getBalance());
    }

    /**
     * Get status of gift code
     *
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftVoucher
     * @return string
     */
    public function getStatus($giftVoucher)
    {
        $status = $giftVoucher->getStatus();
        $statusArray = $this->_giftvoucherStatus->getOptionArray();
        return $statusArray[$status];
    }
}
