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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Generategiftcard;

use Magento\Store\Model\Store;
use Magento\Backend\App\Action;

/**
 * Adminhtml Generategiftcard Save Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_filterDate;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvObject;

    /**

    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherHelper;
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate,
        \Magento\Framework\File\Csv $csvObject,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper

    ) {
        $this->_filterDate = $filterDate;
        $this->_objectManager = $objectManager;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_csvObject = $csvObject;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        //--------
        if( isset($_FILES['import_code']) && substr($_FILES['import_code']["name"], -4)=='.csv'){
        if (isset($_FILES['import_code'])) {
            if (substr($_FILES['import_code']["name"], -4) != '.csv') {
                $this->messageManager->addError(__('Please choose a CSV file'));
                //return $resultRedirect->setPath('*/*/import');

            }
            try {

                $fileName = $_FILES['import_code']['tmp_name'];
                $data = $this->_csvObject->getData($fileName);
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
                            ->setGenerateGiftcode(true)
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
                    return $resultRedirect->setPath('*/*/');
                } else {
                    $this->messageManager->addError(__('No gift code imported'));
                    return $resultRedirect->setPath('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Please check your import file content again.'));
                return $resultRedirect->setPath('*/*/');
            }
        }
    }else{
        //---------


        $id = $this->getRequest()->getParam('template_id');
        $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Generategiftcard');
        if ($this->getRequest()->getParam('id') && $this->getRequest()->getParam('duplicate')) {
            if ($this->_duplicatePattern()) {
                $this->messageManager->addSuccess(__('The pattern has been duplicated successfully.'));
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->_duplicatePattern()));
            } else {
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data['expired_at'] = $this->_filterDate->filter($data['expired_at']);
            if (!$data['expired_at']) {
                $data['expired_at'] = null;
            }
            if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                $conditions = $data['conditions'];
                unset($data['rule']);
            }

//            if (!$this->_giftvoucherHelper->isExpression($data['pattern'])) {
//                $this->messageManager->addError(__('Invalid pattern'));
//                $this->_getSession()->setFormData($data);
//                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
//            }
            $model->setData($data)
                ->setId($this->getRequest()->getParam('template_id'));
            try {
                $model->loadPost($data);
                if ($this->getRequest()->getParam('generate')) {
                    $model->setIsGenerated(1);
                }
                $model->save();
                if ($this->getRequest()->getParam('generate')) {
                    $data = $model->getData();
                    $data['conditions'] = $conditions;
                    $data['gift_code'] = $data['pattern'];
                    $data['template_id'] = $model->getId();
                    $data['amount'] = $data['balance'];
                    $data['status'] = \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE;
                    $data['extra_content'] = __('Created by %1', $authSession->getUser()->getUsername());
                    $amount = $model->getAmount();
                    for ($i = 1; $i <= $amount; $i++) {
                        $this->_giftvoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
                        $this->_giftvoucher->setData($data)->loadPost($data)
                            ->setIncludeHistory(true)
                            ->setGenerateGiftcode(true)
                            ->save();
                    }
                    $this->messageManager->addSuccess(__('The pattern has been generated successfully.'));
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
                }
                $this->messageManager->addSuccess(__('The pattern has been saved successfully.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
    }
        //lucas


        $this->messageManager->addError(__('Unable to find Template to save'));
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _duplicatePattern()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Generategiftcard');
        $data = $model->load($this->getRequest()->getParam('id'))->getData();
        $data['is_generated'] = 0;
        unset($data['template_id']);
        $model->setData($data);
        try {
            $model->save();
            $this->_getSession()->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            return false;
        }
    }
}
