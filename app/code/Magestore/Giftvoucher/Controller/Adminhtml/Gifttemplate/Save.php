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
use Magento\Framework\App\Filesystem\DirectoryList;
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
            //save data to database
            if (isset($data['giftcard_template_id']) && $data['giftcard_template_id']) {
                $id = $data['giftcard_template_id'];
                $model->load($id);
            }
                $images = $data['arrayImages'];
                $arrayImg = array();
                foreach ($images as $image){
                    $imageJson = json_decode($image);
                    $url = $imageJson->url;
                    $file = substr($imageJson->file, 5);
                    $imagePath = parse_url($url, PHP_URL_PATH);
                    $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);

                    if($data['design_pattern'] == 1){
                        $toPath = $mediaDirectory->getAbsolutePath('giftvoucher/template/images/left/'.$file);
                    }elseif ($data['design_pattern'] ==  2) {
                        $toPath = $mediaDirectory->getAbsolutePath('giftvoucher/template/images/top/'.$file);
                    }else{
                        $toPath =$mediaDirectory->getAbsolutePath('giftvoucher/template/images/'.$file);
                    }
                    copy($_SERVER['DOCUMENT_ROOT'].'/'.$imagePath, $toPath);
                    $arrayImg[] = $file;
                }
                if ($model->getImages()) {
                    $currenImg = explode(',', $model->getImages());
                }
                if (isset($currenImg) && count($currenImg)) {
                    $arrayImg = array_merge($arrayImg, $currenImg);
                }                 
                $data['images'] = implode(',', $arrayImg);
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
