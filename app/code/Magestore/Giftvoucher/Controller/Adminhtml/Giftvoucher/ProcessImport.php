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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher ProcessImport Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class ProcessImport extends \Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher
{
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (isset($_FILES['filecsv'])) {
            if (substr($_FILES['filecsv']["name"], -4)!='.csv') {
                $this->messageManager->addError(__('Please choose a CSV file'));
                return $resultRedirect->setPath('*/*/import');
            }
            try {
                $fileName = $_FILES['filecsv']['tmp_name'];
                $data= $this->_csvObject->getData($fileName);
                $count = array();
                $fields = array();
                $giftVoucherImport = array();
                foreach ($data as $row => $cols) {
                    if ($row == 0) {
                        $fields = $cols;
                    } else {
                        $giftVoucherImport[] = array_combine($fields, $cols);
                    }
                }

                $statuses = array(
                    '1' => 1, 'pending' => 1,
                    '2' => 2, 'active' => 2,
                    '3' => 3, 'disabled' => 3,
                    '4' => 4, 'used' => 4,
                    '5' => 5, 'expired' => 5,
                );
                $extraContent = __('Imported by %1', $this->_objectManager->create('Magento\Backend\Model\Auth\Session')
                    ->getUser()->getUsername());
                $template = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')
                    ->getCollection()
                    ->getFirstItem();
                foreach ($giftVoucherImport as $giftVoucherData) {
                    $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
                    if (isset($giftVoucherData['gift_code']) && $giftVoucherData['gift_code']) {
                        $giftVoucher->loadByCode($giftVoucherData['gift_code']);
                        if ($giftVoucher->getId()) {
                            $this->messageManager->addError(
                                __('Gift code %1 already existed', $giftVoucher->getGiftCode())
                            );
                            continue;
                        } else {
                            //Mage::helper('giftvoucher')->createBarcode($giftVoucherData['gift_code']);
                        }
                    }
                    if (isset($giftVoucherData['status']) && $giftVoucherData['status']) {
                        $giftVoucherData['status'] = $statuses[$giftVoucherData['status']];
                    }

                    if (isset($giftVoucherData['history_amount']) && $giftVoucherData['history_amount']) {
                        $giftVoucherData['amount'] = $giftVoucherData['history_amount'];
                    }
                    if (isset($giftVoucherData['extra_content']) && $giftVoucherData['extra_content']) {
                        $giftVoucherData['extra_content'] = str_replace(
                            '\n',
                            chr(10),
                            $giftVoucherData['extra_content']
                        );
                    } else {
                        $giftVoucherData['extra_content'] = $extraContent;
                    }
                    $giftVoucherData['recipient_address'] = str_replace(
                        '\n',
                        chr(10),
                        $giftVoucherData['recipient_address']
                    );
                    $giftVoucherData['message'] = str_replace('\n', chr(10), $giftVoucherData['message']);
                    if (!isset($giftVoucherData['currency'])) {
                        $giftVoucherData['currency'] = $this->_storeManager->getStore($giftVoucherData['store_id'])
                            ->getBaseCurrencyCode();
                    }
                    if (!isset($giftVoucherData['giftcard_template_id'])) {
                        $images = explode(',', $template->getImages());
                        $giftVoucherData['giftcard_template_image'] = $images[0];
                        $giftVoucherData['giftcard_template_id'] = $template->getId();
                    }
                    try {
                        $giftVoucher->setData($giftVoucherData)
                                ->setIncludeHistory(true)
                                ->setId(null)
                                ->save();
                        $count[] = $giftVoucher->getId();
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }

                if (count($count)) {
                    $successMessage = __('Imported total %1 Gift Code(s)', count($count));
                    if ($this->getRequest()->getParam('print')) {
                        $url = $this->getUrl('*/*/massPrint', array(
                            'ids' => implode(',', $count)
                        ));
                        $successMessage .= "<script type='text/javascript'>window.onload = function(){
                        var bob=window.open('','_blank');bob.location='" . $url . "';    
                        };</script>";
                    }
                    $this->messageManager->addSuccess($successMessage);
                    return $resultRedirect->setPath('*/*/index');
                } else {
                    $this->messageManager->addError(__('No gift code imported'));
                    return $resultRedirect->setPath('*/*/import');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Please check your import file content again.'));
                return $resultRedirect->setPath('*/*/import');
            }
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
