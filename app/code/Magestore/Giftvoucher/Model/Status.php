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
class Status extends \Magento\Framework\DataObject
{

    const STATUS_PENDING = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_DISABLED = 3;
    const STATUS_USED = 4;
    const STATUS_EXPIRED = 5;
    const STATUS_DELETED = 6;
    const STATUS_NOT_SEND = 0;
    const STATUS_SENT_EMAIL = 1;
    const STATUS_SENT_OFFICE = 2;

    /**
     * Get the gift code's status options as array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_DISABLED => __('Disabled'),
            self::STATUS_USED => __('Used'),
            self::STATUS_EXPIRED => __('Expired'),
        );
    }

    /**
     * Get the email's status options as array
     *
     * @return array
     */
    public static function getOptionEmail()
    {
        return array(
            self::STATUS_NOT_SEND => __('Not Send'),
            self::STATUS_SENT_EMAIL => __('Sent via Email'),
            self::STATUS_SENT_OFFICE => __('Send via Post Office'),
        );
    }

    /**
     * Get the gift code's status options
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
