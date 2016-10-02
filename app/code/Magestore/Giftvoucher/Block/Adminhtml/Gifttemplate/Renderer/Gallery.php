<?php

/**
 * Magestore.
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
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Renderer;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Gallery extends \Magento\Backend\Block\Template implements RendererInterface
{
    protected $_template = 'Magestore_Giftvoucher::gifttemplate/renderer/gallery.phtml';

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    public function setElement($element)
    {
        $this->_element = $element;
    }

    /**
     * Render form element as HTML.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Getter for static view file URL.
     *
     * @param $fileId
     *
     * @return string
     */
    public function getAssetRepoUrl($fileId)
    {
        return $this->_assetRepo->getUrl($fileId);
    }

    /**
     * get html id.
     *
     * @return array|string
     */
    public function getHtmlId()
    {
        return $this->_escaper->escapeHtml($this->getElement()->getHtmlId());
    }

    /**
     * Get url to upload files.
     *
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->_escaper->escapeHtml($this->getElement()->getUploadUrl());
    }

    /**
     * Get maximum file size to upload in bytes.
     *
     * @return int
     */
    public function getFileMaxSize()
    {
        return $this->getElement()->getFileMaxSize();
    }

    /**
     * @return mixed
     */
    public function getImageJsonData()
    {
        return $this->getElement()->getImageJsonData();
    }

    /**
     * @return mixed
     */
    public function getMaximumImageCount()
    {
        return $this->getElement()->getMaximumImageCount();
    }
}
