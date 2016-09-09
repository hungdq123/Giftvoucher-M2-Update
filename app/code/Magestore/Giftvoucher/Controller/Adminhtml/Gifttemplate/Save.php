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
namespace Magestore\Giftvoucher\Controller\Adminhtml\Gifttemplate;

use Magento\Store\Model\Store;

/**
 * Adminhtml Gifttemplate Save Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
    ) {
        $this->_setColFactory = $setColFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate');
            $filterDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Filter\Date');
            $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
            $helper = $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data');
            //upload giftcard template image
            $numberImage = $data['number_image'];
            $imgUploaded = $helper->uploadTemplateImages($numberImage);
            //upload or delete backgroud image
            if (isset($data['background_img']['delete']) && $data['background_img']['delete'] == 1) {
                $helper->deleteImageFile($data['background_img']['value']);
            }
            $background = $helper->uploadTemplateBackground();
            if ($background || (isset($data['background_img']['delete']) && $data['background_img']['delete'])) {
                $data['background_img'] = $background;
            } else {
                unset($data['background_img']);
            }
            //save data to database
            if (isset($data['giftcard_template_id']) && $data['giftcard_template_id']) {
                $id = $data['giftcard_template_id'];
                $model->load($id);
            }
            if (isset($imgUploaded) && count($imgUploaded)) {
                if ($model->getImages()) {
                    $currenImg = explode(',', $model->getImages());
                }
                if (isset($currenImg) && count($currenImg)) {
                    $arrayImg = array_merge($imgUploaded, $currenImg);
                } else {
                    $arrayImg = $imgUploaded;
                }
                $data['images'] = implode(',', $arrayImg);
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Gift Card Template was successfully saved'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
                }
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());
            }
        }
        if ($this->getRequest()->getParam('back') == 'edit') {
            return $resultRedirect->setPath('*/*/edit', array('id' => $data['package_id']));
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
