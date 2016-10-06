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

use Magento\Store\Model\Store;

/**
 * Adminhtml Giftvoucher Save Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucherModel;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_filterDate;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucherModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucherModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate
    ) {
        $this->_objectManager = $objectManager;
        $this->_setColFactory = $setColFactory;
        $this->_giftvoucherModel = $giftvoucherModel;
        $this->_filterDate = $filterDate;
        parent::__construct($context);
    }
            
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_giftvoucherModel;
            $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
            
            if (isset($data['expired_at']) && !$data['expired_at']) {
                $data['expired_at'] = null;
            }else{
                $data['expired_at'] = $this->_filterDate->filter($data['expired_at']);
            }
            if (isset($data['order_increment_id'])) {
                unset($data['order_increment_id']);
            }
            $data['status'] = $data['giftvoucher_status'];
            $data['comments'] = $data['giftvoucher_comments'];
            $data['amount'] = $data['balance'];
            $data['used'] = $data['giftvoucher_used'];
            if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                if (isset($rules['actions'])) {
                    $data['actions'] = $rules['actions'];
                }
                unset($data['rule']);
            }
            
            $validator = new \Zend_Validate_EmailAddress();
            if (isset($data['customer_email']) && $data['customer_email'] != '') {
                if (!$validator->isValid($data['customer_email'])) {
                    $this->messageManager->addError(__('Customer email is not valid'));
                }
            }
            
            if (isset($data['recipient_email']) && $data['recipient_email'] != '') {
                if (!$validator->isValid($data['recipient_email'])) {
                    $this->messageManager->addError(__('Recipient email is not valid'));
                }
            }
            if ($this->getRequest()->getParam('giftvoucher_id')) {
                $data['action'] = \Magestore\Giftvoucher\Model\Actions::ACTIONS_UPDATE;
                $data['extra_content'] = __('Updated by %1', $authSession->getUser()->getUsername());
            } else {
                $data['extra_content'] = __('Created by %1', $authSession->getUser()->getUsername());
            }
            
            $incrementId = $this->_giftvoucherModel->getCollection()->joinHistory()
                            ->addFieldToFilter('history.giftvoucher_id', $this->getRequest()->getParam('id'))
                            ->getFirstItem()
                            ->getOrderIncrementId();

            if (isset($data['giftcard_template_id']) && !$data['giftcard_template_id']) {
                $template = $this->_giftvoucherModel->getCollection()->getFirstItem();
                $templateImages = explode(',', $template->getImages());

                $data['giftcard_template_id'] = $template->getId();
                $data['giftcard_template_image'] = $templateImages[0];
            }
            
            $model->setData($data);
            $model->setIncludeHistory(true)->setId($this->getRequest()->getParam('giftvoucher_id'));
            
            try {
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('sendemail')) {
                        $data['is_sent'] = 1;
                    }
                }
                $model->loadPost($data);
                $model->save();

                $this->messageManager->addSuccess(__('Gift Code was successfully saved'));
                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('sendemail')) {
                        $emailSent = (int) $model->setNotResave(true)->sendEmail()->getEmailSent();
                        if ($emailSent) {
                            $this->messageManager->addSuccess(__('and (%1) email(s) were sent.', $emailSent));
                        } else {
                            $allowStatus = explode(',', $this->_objectManager->get('Magestore\Giftvoucher\Helper\Data')
                                ->getEmailConfig('only_complete', $model->getStoreId()));
                            if (!$model->getRecipientEmail()) {
                                $this->messageManager->addError(__('There is no email address to send.'));
                            } else {
                                $options = \Magestore\Giftvoucher\Model\Status::getOptionArray();
                                $this->messageManager->addError(__('gift card is %1 should not send an email, %2', $options[$model->getStatus()], '<a href="' . $this->getUrl('admin/system_config/edit/section/giftvoucher') . '">' . __(' view config select status of gift card when sending e-mail to friend') . '</a>'));
                            }
                        }
                    }
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('giftvoucher_id')
                ));
            }
        }
        
        if ($this->getRequest()->getParam('back')=='edit') {
            return $resultRedirect->setPath('*/*/edit', array('id' => $data['giftvoucher_id']));
        }
        
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
