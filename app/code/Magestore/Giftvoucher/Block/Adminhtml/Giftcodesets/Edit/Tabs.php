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

namespace Magestore\Giftvoucher\Block\Adminhtml\Giftcodesets\Edit;

/**
 * Adminhtml Giftvoucher Generategiftcard Edit Tabs Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        \Magestore\Giftvoucher\Model\Giftcodesets $generategiftcard,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_generategiftcard = $generategiftcard;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftcodesets_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Sets Information'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addTab(
            'form_section',
            [
                'label' => __('General Information'),
                'content' => $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Giftcodesets\Edit\Tab\Form'
                )->toHtml()
            ]
        );

        $isGenerated = $this->getTemplateGenerate()->getIsGenerated();
        if ($isGenerated) {
            $this->addTab('form_giftcode', array(
                'label' => __('Gift Codes Information'),
                'title' => __('Gift Codes Information'),
                'content' => $this->getLayout()
                    ->createBlock('Magestore\Giftvoucher\Block\Adminhtml\Giftcodesets\Edit\Tab\Giftcodelist')->toHtml(),
            ));
        }
    }

//    public function getTemplateGenerate()
//    {
//        if ($this->_coreRegistry->registry('generategiftcard_data')) {
//            return $this->_coreRegistry->registry('generategiftcard_data');
//        }
//        return $this->_generategiftcard;
//    }
}
