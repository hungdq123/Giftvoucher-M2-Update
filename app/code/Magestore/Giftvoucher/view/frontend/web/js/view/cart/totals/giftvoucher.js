/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magestore_Giftvoucher/js/view/summary/giftvoucher'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Giftvoucher/cart/totals/giftvoucher'
            },
            /**
             * @override
             *
             * @returns {boolean}
             */
            isDisplayed: function () {
                return this.getPureValue() != 0;
            },
            
            /**
             * @override
             *
             * @returns {boolean}
             */
            isDisplayedCard: function() {
                return this.getPureValueCard() > 0;
            }
        });
    }
);
