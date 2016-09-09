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
 * Giftvoucher Gifttype Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Gifttype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const GIFT_TYPE_FIX = 1;
    const GIFT_TYPE_RANGE = 2;
    const GIFT_TYPE_DROPDOWN = 3;

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('Fixed value'),
                    'value' => self::GIFT_TYPE_FIX
                ),
                array(
                    'label' => __('Range of values'),
                    'value' => self::GIFT_TYPE_RANGE
                ),
                array(
                    'label' => __('Dropdown values'),
                    'value' => self::GIFT_TYPE_DROPDOWN
                ),
            );
        }
        return $this->_options;
    }
}
