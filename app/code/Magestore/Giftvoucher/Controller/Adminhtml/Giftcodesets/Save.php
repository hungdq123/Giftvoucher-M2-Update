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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Giftcodesets;

use Magento\Store\Model\Store;

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
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvObject;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherHelper;
    /**
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

        $id = $this->getRequest()->getParam('set_id');
        //var_dump($id);
        $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftcodesets');

        $data = $this->getRequest()->getPostValue();
        if ($data) {


//            if (!$this->_giftvoucherHelper->isExpression($data['pattern'])) {
//                $this->messageManager->addError(__('Invalid pattern'));
//                $this->_getSession()->setFormData($data);
//                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
//            }
            $model->setData($data)
                ->setId($this->getRequest()->getParam('set_id'));

            try {
                $model->loadPost($data);

                if(!(substr($_FILES['import_code']["name"], -4)=='.csv')){
                    $this->messageManager->addError(__('Please import the csv file!.'));
                    return $resultRedirect->setPath('*/*/edit',array('id' => $model->getId()));

                }
                $model->save();
                if( isset($_FILES['import_code']) && substr($_FILES['import_code']["name"], -4)=='.csv') {
                    try {
                        $fileName = $_FILES['import_code']['tmp_name'];
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

                            try {
                                $giftVoucher->setGiftCode($giftVoucherData['gift_code'])
                                    ->setIncludeHistory(true)
                                    ->setUsed($giftVoucherData['used'])
                                    //->setGenerateGiftcode(true)
                                    ->setSetId($model->getId())
                                    ->save();
                                $count[] = $giftVoucher->getId();
                            } catch (\Exception $e) {
                                $this->messageManager->addError($e->getMessage());
                            }
                        }


                       $qtys=$model->load($this->getRequest()->getParam('set_id'))->getSetsQty();
                        //var_dump($qtys);die('xxx');
                        $model->setSetsQty($qtys+count($count));

                        $model->save();

                        if (count($count)) {
                            $successMessage = __('Imported total %1 Gift Code(s)', count($count));
                            $this->messageManager->addSuccess($successMessage);
                            return $resultRedirect->setPath('*/*/');
                        } else {
                            $this->messageManager->addError(__('No gift code imported'));
                            return $resultRedirect->setPath('*/*/edit',array('id' => $model->getId()));
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Please check your import file content again.'));
                        return $resultRedirect->setPath('*/*/edit',array('id' => $model->getId()));
                    }

                }
                $this->messageManager->addSuccess(__('The Gift Code Set have been saved.'));
                $this->_getSession()->setFormData(false);

                //return $resultRedirect->setPath('*/*/');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }


        }
        //$this->messageManager->addError(__('Unable to find Template to save'));
        return $resultRedirect->setPath('*/*/edit',array('id' => $model->getId()));
    }


}
