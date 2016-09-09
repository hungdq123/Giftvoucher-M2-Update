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
 * Adminhtml Checkout giftcardPost Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class GiftcardPost extends \Magento\Backend\App\Action
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
                    && $request->getParam('giftvoucher_credit')) {
                    $session->setUseGiftCardCredit(1);
                    $session->setMaxCreditUsed(floatval($request->getParam('credit_amount')));
                    $this->messageManager->addSuccess(__('Gift Card credit has been applied successfully.'));
                } else {
                    $session->setUseGiftCardCredit(0);
                    $session->setMaxCreditUsed(null);
                }
                if ($request->getParam('giftvoucher')) {
                    $session->setUseGiftCard(1);
                    $giftcodesAmount = $request->getParam('giftcodes');
                    if (count($giftcodesAmount)) {
                        $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                        if (!is_array($giftMaxUseAmount)) {
                            $giftMaxUseAmount = array();
                        }
                        $giftMaxUseAmount = array_merge($giftMaxUseAmount, $giftcodesAmount);
                        $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                    }
                    $addcodes = array();
                    if ($request->getParam('existed_giftvoucher_code')) {
                        $addcodes[] = trim($request->getParam('existed_giftvoucher_code'));
                    }
                    if ($request->getParam('giftvoucher_code')) {
                        $addcodes[] = trim($request->getParam('giftvoucher_code'));
                    }
                    if (count($addcodes)) {
                        foreach ($addcodes as $code) {
                            $giftVoucher = $this->_giftvoucher->loadByCode($code);
                            if (!$giftVoucher->getGiftCode()) {
                                $this->messageManager->addError(__('Gift Card "%1" does not exist.', $code));
                                continue;
                            }
                            if (!$this->_giftvoucherHelper->canUseCode($giftVoucher)) {
                                $this->messageManager->addError(__('This gift code limits the number of users'));
                                continue;
                            }
                            $quote = $this->_sessionQuote->getQuote();
                            if ($giftVoucher->getBaseBalance() > 0
                                && $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                                && $giftVoucher->validate($quote->setQuote($quote))
                            ) {
                                $giftVoucher->addToSession($session);
                                if ($giftVoucher->getCustomerId() == $this->_sessionQuote->getCustomerId()
                                    && $giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail()
                                    && $giftVoucher->getCustomerId()
                                ) {
                                    $this->messageManager->addNotice(__('Gift Card "%1" has been sent to the customer\'s friend.', $code));
                                }
                                $this->messageManager->addSuccess(__('Gift Card "%1" has been applied successfully.', $code));
                            } elseif ($giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE) {
                                $giftVoucher->addToSession($session);
                                $this->messageManager->addNotice(__('You can’t use this gift code since its conditions haven’t been met.'));
                            } else {
                                $this->messageManager->addError(__('Gift Card "%1" is no longer available to use.', $code));
                            }
                        }
                    } else {
                        $this->messageManager->addSuccess(__('Gift Card has been updated successfully.'));
                    }
                } elseif ($session->getUseGiftCard()) {
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
