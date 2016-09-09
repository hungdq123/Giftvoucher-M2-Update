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
 * Giftvoucher Displayincart Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Displayincart implements \Magento\Framework\Option\ArrayInterface
{
    
    /**
     * Get model option as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $positions = array(
            'amount' => __('Gift Card value'),
            'giftcard_template_id' => __('Gift Card template'),
            'customer_name' => __('Sender name'),
            'recipient_name' => __('Recipient name'),
            'recipient_email' => __('Recipient email address'),
            'recipient_ship' => __('Ship to recipient'),
            'recipient_address' => __('Recipient address'),
            'message' => __('Custom message'),
            'day_to_send' => __('Day to send'),
            'timezone_to_send' => __('Time zone'),
        );
        $options = array();

        foreach ($positions as $code => $label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
