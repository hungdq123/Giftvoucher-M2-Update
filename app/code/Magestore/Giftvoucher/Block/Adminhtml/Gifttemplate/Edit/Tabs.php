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

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit;

/**
 * Adminhtml GiftCard Template Grid Edit Tabs Block
 *
 * @category Magestore
 * @package  Magestore_Gifttemplate
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('gifttemplate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Gift Card Template Information'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addTab(
            'general_gifttemplate_section',
            [
                'label' => __('General Information'),
                'content' =>  $this->getLayout()->createBlock(
                    'Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit\Tab\Form'
                )->toHtml()
            ]
        );
//        $this->addTab(
//            'images_gifttemplate_section',
//            [
//                'label' => __('Images'),
//                'content' =>  $this->getLayout()->createBlock(
//                    'Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit\Tab\Images'
//                )->toHtml()
//            ]
//        );

        $this->addTab('image_section', 'giftvocuher_edit_tab_image');
    }
}
