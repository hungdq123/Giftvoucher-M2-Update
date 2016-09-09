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

/**
 * Giftvoucher View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class View extends \Magestore\Giftvoucher\Block\Account
{

    public function getCustomerGift()
    {
        if (!$this->hasData('customer_gift')) {
            $this->setData(
                'customer_gift',
                $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')->load(
                    $this->getRequest()->getParam('id')
                )
            );
        }
        return $this->getData('customer_gift');
    }

    public function getGiftVoucher()
    {
        if (!$this->hasData('gift_voucher')) {
            $customerGift = $this->getCustomerGift();
            $obj = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($customerGift->getVoucherId());
            if (!$obj->getGiftcardTemplateImage()) {
                $obj->setGiftcardTemplateImage('default.png');
            }

            $this->setData('gift_voucher', $obj);
        }
        return $this->getData('gift_voucher');
    }

    public function getGiftVoucherEmail()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$this->hasData('gift_voucher')) {
            $obj = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($id);
            if (!$obj->getGiftcardTemplateImage()) {
                $obj->setGiftcardTemplateImage('default.png');
            }
            $this->setData('gift_voucher', $obj);
        }
        return $this->getData('gift_voucher');
    }

    /**
     * Returns the formatted gift code
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @return string
     */
    public function getCodeTxt($giftVoucher)
    {
        return $this->getHelper()->getHiddenCode($giftVoucher->getGiftCode());
    }

    /**
     * Returns the formatted balance
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @return string
     */
    public function getBalanceFormat($giftVoucher)
    {
        //$currency = $this->getModel('Magento\Directory\Model\Currency')->load($giftVoucher->getCurrency());
        return $this->formatCurrency($giftVoucher->getBalance(), $giftVoucher->getCurrency());
    }

    /**
     * Get status of gift code
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @return string
     */
    public function getStatus($gifVoucher)
    {
        $status = $gifVoucher->getStatus();
        $statusArray = $this->getSingleton('Magestore\Giftvoucher\Model\Status')->getOptionArray();
        return $statusArray[$status];
    }

    /**
     * Check a gift code is sent to the recipient or not
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftCard
     * @return boolean
     */
    public function checkSendFriendGiftCard($giftCard)
    {
        return ($giftCard->getRecipientName() && $giftCard->getRecipientEmail()
            && $giftCard->getCustomerId() == $this->getSingleton('Magento\Customer\Model\Session')->getCustomerId()
        );
    }

    /**
     * get shipment for gift card
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftCard
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipmentForGiftCard($giftCard)
    {
        $history = $this->getModel('Magestore\Giftvoucher\Model\History')->getCollection()
            ->addFieldToFilter('giftvoucher_id', $giftCard->getId())
            ->addFieldToFilter('action', \Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE)
            ->getFirstItem();
        if (!$history->getOrderIncrementId() || !$history->getOrderItemId()) {
            return false;
        }
        $shipmentItem = $this->getModel('Magento\Sales\Model\Order\Shipment\Item')->getCollection()
            ->addFieldToFilter('order_item_id', $history->getOrderItemId())
            ->getFirstItem();
        if (!$shipmentItem || !$shipmentItem->getId()) {
            return false;
        }
        $shipment = $this->getModel('Magento\Sales\Model\Order\Shipment')->load($shipmentItem->getParentId());
        if (!$shipment->getId()) {
            return false;
        }
        return $shipment;
    }

    /**
     * get History for Gift Card
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftCard
     * @return Magestore_Giftvoucher_Model_Mysql4_History_Collection
     */
    public function getGiftCardHistory($giftCard)
    {
        $collection = $this->getModel('Magestore\Giftvoucher\Model\History')->getCollection()
            ->addFieldToFilter('main_table.giftvoucher_id', $giftCard->getId());
        if ($giftCard->getCustomerId() != $this->getCustomer()->getId()) {
            $collection->addFieldToFilter('main_table.customer_id', $this->getCustomer()->getId());
        }
        $collection->getHistory();
        return $collection;
    }

    /**
     * Get action name of Gift card history
     *
     * @param Magestore_Giftvoucher_Model_History $history
     * @return string
     */
    public function getActionName($history)
    {
        $actions = $this->getSingleton('Magestore\Giftvoucher\Model\Actions')->getOptionArray();
        if (isset($actions[$history->getAction()])) {
            return $actions[$history->getAction()];
        }
        reset($actions);
        return current($actions);
    }

    /**
     * Returns the formatted amount
     *
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @return string
     */
    public function getAmountFormat($giftVoucher)
    {
        //$currency =$this->getModel('Magento\Directory\Model\Currency')->load($giftVoucher->getCurrency());
        return $this->converCurrency($giftVoucher->getAmount());
    }

    /**
     * Returns a Gift Card template object
     *
     * @param int $templateId
     * @return Magestore_Giftvoucher_Model_Gifttemplate
     */
    public function getGiftcardTemplate($templateId)
    {
        $template = $this->getModel('Magestore\Giftvoucher\Model\Gifttemplate')->load($templateId);
        if (!$template->getBackgroundImg()) {
            $template->setBackgroundImg('default.png');
        }
        if (!$template->getStyleColor()) {
            $template->setStyleColor('orange');
        }
        if (!$template->getTextColor()) {
            $template->setTextColor('#2f2f2f');
        }

        return $template;
    }

    public function messageMaxLen()
    {
        return $this->getHelper()->getInterfaceConfig('max');
    }

    public function getBarcodeInformation($giftCard)
    {
        $helper = $this->getHelper();
        $store = $this->getStore();
        $result = [];
        $barcode = $helper->getGeneralConfig('barcode_enable');
        $barcode_type = $helper->getGeneralConfig('barcode_type');
        $urlBarcode = '';
        $resizeImage = false;
        $qr = new \Magestore_Giftvoucher_QRCode($giftCard->getGiftCode());
        if ($barcode) {
            if ($barcode_type == 'code128') {
                $urlBarcode = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'giftvoucher/template/barcode/' . $giftCard->getGiftCode() . '.png';
                $imageUrl = $this->getMediaDirPath('giftvoucher/template/barcode/' . $giftCard->getGiftCode() . '.png');
                $imageObj = $this->getImage();
                $imageObj->open($imageUrl);
                $imageObj->getImage();
                if ($imageObj->getOriginalWidth() > 200) {
                    $resizeImage = true;
                }
            } else {
                $urlBarcode = $qr->getResult();
            }
        }
        $result['resize_image'] = $resizeImage;
        $result['url_barcode'] = $urlBarcode;

        return $result;
    }

    public function getNotes($template, $giftCard)
    {
        if ($template->getNotes()) {
            $notes = $template->getNotes();
        } else {
            $notes = $giftCard->getPrintNotes();
        }

        return $notes;
    }
}
