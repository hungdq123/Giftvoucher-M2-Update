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
 * Adminhtml Gifttemplate Removeimage Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Removeimage extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_fileSystem = $filesystem;
        parent::__construct($context);
    }

    public function execute()
    {
        $imageName = $this->getRequest()->getParam('value');
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate')->load($id);
        $type = '';
        switch ($model->getDesignPattern()) {
            case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_LEFT:
                $type = 'left/';
                break;
            case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_TOP:
                $type = 'top/';
                break;
            case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_CENTER:
                $type = '';
                break;
        }

        $images = explode(',', $model->getImages());
        foreach ($images as $key => $value) {
            if ($value == $imageName) {
                unset($images[$key]);
            }
        }
        $images = implode(',', $images);
        $model->setImages($images)->setId($id);
        try {
            $model->save();
            echo 'success';
        } catch (\Exception $exc) {
            echo 'failed';
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
