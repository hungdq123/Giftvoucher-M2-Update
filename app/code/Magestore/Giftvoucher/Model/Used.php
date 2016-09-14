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
 * Giftvoucher Status Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Used extends \Magento\Framework\DataObject
{

    const STATUS_YES = 1;
    const STATUS_NO = 2;


    /**
     * Get the gift code's status options as array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::STATUS_YES => __('Yes'),
            self::STATUS_NO => __('No'),

        );
    }


    /**
     * Get the gift code's used options
     *
     * @return array
     */
    public static function getOptions()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    public function toOptionArray()
    {
        return self::getOptions();
    }
}
