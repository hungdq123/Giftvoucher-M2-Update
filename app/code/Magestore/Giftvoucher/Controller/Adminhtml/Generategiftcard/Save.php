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
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
    ) {
        $this->_filterDate = $filterDate;
        $this->_objectManager = $objectManager;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

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
            
            if (!$this->_giftvoucherHelper->isExpression($data['pattern'])) {
                $this->messageManager->addError(__('Invalid pattern'));
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
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
