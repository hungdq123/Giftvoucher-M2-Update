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

/**
 * Adminhtml Generategiftcard Generate Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Generate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Generategiftcard
     */
    protected $_generategiftcardModel;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_filterDate;
    
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_giftvoucherHelper;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcardModel
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate
     * @param \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcardModel,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $filterDate,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
    ) {
        parent::__construct($context);
        $this->_generategiftcardModel = $generategiftcardModel;
        $this->_filterDate = $filterDate;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_giftvoucher = $giftvoucher;
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_generategiftcardModel;
            $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
            if ($model->getIsGenerated()) {
                $this->messageManager->addError(__('Each template only generate a time'));
                return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
            }
             $data['expired_at'] = $this->_filterDate->filter($data['expired_at']);
            if (!$data['expired_at']) {
                $data['expired_at'] = null;
            }

            if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $condition = $rules['conditions'];
                }
                $data['conditions'] = $condition;
                unset($data['rule']);
            }
            
            if (!$this->_giftvoucherHelper->isExpression($data['pattern'])) {
                $this->messageManager->addError(__('Invalid pattern'));
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $model->loadPost($data);
                $model->setIsGenerated(1)->save();
                $this->_getSession()->setFormData(false);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }

            try {
                $data = $model->getData();
                $data['conditions'] = $condition;
                $data['gift_code'] = $data['pattern'];
                $data['template_id'] = $model->getId();
                $data['amount'] = $data['balance'];
                $data['status'] = \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE;
                $data['extra_content'] = __('Created by %s', $authSession->getUser()->getUsername());
                $amount = $model->getAmount();
                
                for ($i = 1; $i <= $amount; $i++) {
                    $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                        ->setData($data)
                        ->loadPost($data)
                        ->setIncludeHistory(true)
                        ->setGenerateGiftcode(true)
                        ->save();
                }
                $this->messageManager->addSuccess(__('The pattern has been saved and generated successfully.'));
                return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
            } catch (\Exception $e) {
                $model->setIsGenerated(0)->save();
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

            }
        }
        $this->messageManager->addError(__('Unable to find Template to save'));
        return $resultRedirect->setPath('*/*/');
    }
}
