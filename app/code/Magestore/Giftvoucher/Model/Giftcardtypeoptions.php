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
namespace Magestore\Giftvoucher\Model;

/**
 * Giftvoucher View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftcardtypeoptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const TYPE_PHYSICAL = 1;
    const TYPE_VIRTUAL = 2;
    const TYPE_COMBINE= 3;


    /**
     * Get the gift card's type
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::TYPE_PHYSICAL => __('Physical'),
            self::TYPE_VIRTUAL => __('Virtual'),
            self::TYPE_COMBINE=> __('Combine'),

        );
    }


    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('Physical'),
                    'value' => self::TYPE_PHYSICAL
                ),
                array(
                    'label' => __('Virtual'),
                    'value' => self::TYPE_VIRTUAL
                ),
                array(
                    'label' => __('Combine'),
                    'value' => self::TYPE_COMBINE
                ),
            );
        }
        if($withEmpty){
            array_unshift($this->_options, array(
                'value' => '',
                'label' => __('-- Please Select --'),
            ));
        }
        return $this->_options;
    }
}
