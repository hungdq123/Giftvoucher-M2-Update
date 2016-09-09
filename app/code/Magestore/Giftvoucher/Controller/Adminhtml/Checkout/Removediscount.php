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
namespace Magestore\Giftvoucher\Controller\Adminhtml\Checkout;

/**
 * Adminhtml Checkout Removediscount Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Removediscount extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magento\Framework\Json\Helper\Data $helperJson
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magento\Framework\Json\Helper\Data $helperJson
    ) {
        parent::__construct($context);
        $this->_sessionQuote = $sessionQuote;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_giftvoucher = $giftvoucher;
        $this->_helperJson = $helperJson;
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $session = $this->_checkoutSession;
        $quote = $this->_sessionQuote->getQuote();

        if ($quote->getCouponCode() && !$this->_giftvoucherHelper->getGeneralConfig('use_with_coupon')) {
            $this->clearGiftcardSession($session);
            $this->messageManager->addNotice(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
        } else {
            if ($request->isPost()) {
                if ($this->_giftvoucherHelper->getGeneralConfig('enablecredit', $quote->getStoreId())
                    && $request->getParam('giftvoucher_credit')== "false") {
                    $session->setUseGiftCardCredit(0);
                    $session->setMaxCreditUsed(null);
                }
                if ($request->getParam('giftvoucher') == "false") {
                    $session->setUseGiftCard(null);
                    $this->messageManager->addSuccess(__('Your Gift Card has been removed successfully.'));
                }
            }
        }
        $this->getResponse()->setBody($this->_helperJson->jsonEncode(array()));
    }
    
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
