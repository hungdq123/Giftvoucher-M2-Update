/*Giftvoucher JS*/

function toggleTotalCode() {
    $$('.giftvoucher-total-code').each(function(el) {
        el.toggle();
    });
}

function addGiftVoucher(url_reload) {
    var giftvoucher_code = $('giftvoucher_code').value;
    var add_code = '';
    if ($('giftvoucher_existed_code')) {
        add_code = $('giftvoucher_existed_code').value;
    }
    if (giftvoucher_code != '' || add_code != '') {
        $('giftvoucher_add').hide();
        $('giftvoucher_wait').show();
        var url = $('giftvoucher_cache_url').innerHTML;
        if (giftvoucher_code != '')
            url += '/code/' + giftvoucher_code;
        if (add_code != '')
            url += '/addcode/' + add_code;
        new Ajax.Request(url, {
            method: 'post',
            postBody: '',
            onException: function(response, e) {
                $('giftvoucher_message').update('<ul class="error-msg"><li>' + e + '</li></ul>');
            },
            onComplete: function(response) {
                if (response.responseText.isJSON()) {
                    var res = response.responseText.evalJSON();
                    var needUpdate = true;
                    if (res.ajaxExpired && res.ajaxRedirect) {
                        setLocation(res.ajaxRedirect);
                        needUpdate = false;
                    }
                    if (needUpdate && res.updatepayment) {
                        var eladd = getElement();
                        eladd.hide();
                        $('giftvoucher_container').down('dd.giftvoucher').hide();
                        $('giftvoucher_ajaxload').show();
                        // if (typeof shippingMethod != 'undefined') {
                        // shippingMethod.save();
                        // } else if (typeof billing != 'undefined'){
                        // billing.save();
                        // } else {
                        // save_address_information(save_address_url);
                        // }
                    } else if (needUpdate) {
                        if (res.html) {
                            var container = $('giftvoucher_container');
                            container.innerHTML = res.html;
                            $('payment_form_giftvoucher').show();
                        } else {
                            $('giftvoucher_wait').hide();
                            $('giftvoucher_add').show();
                        }
                        var message = '';
                        if (res.success) {
                            message = '<ul class="success-msg"><li>' + res.success + '</li></ul>';
                            if (typeof (save_shipping_method) != 'undefined')
                                save_shipping_method(shipping_method_url);
                        }
                        if (res.notice)
                            message += '<ul class="notice-msg"><li>' + res.notice + '</li></ul>';
                        if (res.error)
                            message += '<ul class="error-msg"><li>' + res.error + '</li></ul>';
                        if (message != '')
                            $('giftvoucher_message').update(message);
                    }
                } else {
                    $('giftvoucher_wait').hide();
                    $('giftvoucher_add').show();
                    alert(response.responseText);
                }
                // if (typeof(save_shipping_method) != 'undefined')
                // save_shipping_method(shipping_method_url);

                /*Customize by Vick*/
                var summary = $$('div.onestepcheckout-summary').first();
                summary.update('<div class="loading-ajax">&nbsp;</div>');
                var payment_methods = $$('div.payment-methods')[0];
                payment_methods.update('<div class="loading-ajax">&nbsp;</div>');

                new Ajax.Request(url_reload, {
                    method: 'post',
                    onComplete: function(transport) {
                        if (transport.responseText.isJSON()) {
                            var response = transport.responseText.evalJSON();
                            payment_methods.hide();
                            payment_methods.update(response.payment_method);
                            payment_methods.show();
                            summary.hide();
                            summary.update(response.summary);
                            summary.show();
                        }
                    }
                });
            }
        });
    } else {
        payment.save();
    }
}

function removeGiftVoucher(e, url_reload) {
    var el = $(e.id);
    var url = el.children[0].value;
    var idPrefix = el.id;
    var title = $(idPrefix + '_remove');
    var ajaxAlt = $(idPrefix + '_ajax');
    title.hide();
    el.hide();
    ajaxAlt.show();
    var request = new Ajax.Request(url, {
        method: 'post',
        postBody: '',
        onException: function(response, e) {
            $('giftvoucher_message').update('<ul class="error-msg"><li>' + e + '</li></ul>');
        },
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                var needUpdate = true;
                if (res.ajaxExpired && res.ajaxRedirect) {
                    setLocation(res.ajaxRedirect);
                    needUpdate = false;
                }
                if (needUpdate && res.updatepayment) {
                    var eladd = getElement();
                    eladd.hide();
                    $('giftvoucher_container').down('dd.giftvoucher').hide();
                    $('giftvoucher_ajaxload').show();
                    // if (typeof shippingMethod != 'undefined') {
                    // shippingMethod.save();
                    // } else if (typeof billing != 'undefined'){
                    // billing.save();
                    // } else {
                    // save_address_information(save_address_url);
                    // }
                } else if (needUpdate) {
                    if (res.html) {
                        var container = $('giftvoucher_container');
                        container.innerHTML = res.html;
                        $('payment_form_giftvoucher').show();
                    } else {
                        ajaxAlt.hide();
                        el.show();
                        title.show();
                    }
                    var message = '';
                    if (res.success) {
                        message = '<ul class="success-msg"><li>' + res.success + '</li></ul>';
                        if (typeof (save_shipping_method) != 'undefined')
                            save_shipping_method(shipping_method_url);
                    }
                    if (res.error)
                        message += '<ul class="error-msg"><li>' + res.error + '</li></ul>';
                    if (message != '')
                        $('giftvoucher_message').update(message);
                }
            } else {
                ajaxAlt.hide();
                el.show();
                title.show();
                alert(response.responseText);
            }
            // if (typeof(save_shipping_method) != 'undefined')
            // save_shipping_method(shipping_method_url);

            /*Customize by Vick*/
            var summary = $$('div.onestepcheckout-summary').first();
            summary.update('<div class="loading-ajax">&nbsp;</div>');
            var payment_methods = $$('div.payment-methods')[0];
            payment_methods.update('<div class="loading-ajax">&nbsp;</div>');

            new Ajax.Request(url_reload, {
                method: 'post',
                onComplete: function(transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        payment_methods.hide();
                        payment_methods.update(response.payment_method);
                        payment_methods.show();
                        summary.hide();
                        summary.update(response.summary);
                        summary.show();
                    }
                }
            });
        }
    });
}

// change or udpate gift card used to spend
function changeUseGiftvoucher(el) {
    var url = $('giftvoucher_cache_url').innerHTML.replace('/addgift', '/giftcard');
    if (el.checked) {
        $('payment_form_giftvoucher').show();
        changeGiftcardVisible('giftvoucher', false);
        url += '/giftvoucher/1';
    } else {
        $('payment_form_giftvoucher').hide();
        changeGiftcardVisible('giftvoucher', true);
    }

    var eladd = getElement();
    eladd.hide();
    $('giftvoucher_container').down('dd.giftvoucher').hide();
    $('giftvoucher_ajaxload').show();
    new Ajax.Request(url, {
        method: 'post',
        postBody: '',
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                if (res.updatepayment) {
                    // if (typeof shippingMethod != 'undefined') {
                    // shippingMethod.save();
                    // } else if (typeof billing != 'undefined'){
                    // billing.save();
                    // } else {
                    // save_address_information(save_address_url);
                    // }
                } else {
                    var eladd = getElement();
                    eladd.show();
                    $('giftvoucher_container').down('dd.giftvoucher').show();
                    $('giftvoucher_ajaxload').hide();
                }
            } else {
                var eladd = getElement();
                eladd.show();
                $('giftvoucher_container').down('dd.giftvoucher').show();
                $('giftvoucher_ajaxload').hide();
            }
        }
    });
}

function onLoadGiftvoucherForm() {
    if ($('giftvoucher')) {
        $('giftvoucher').disabled = false;
        if ($('giftvoucher').checked) {
            $('payment_form_giftvoucher').show();
            changeGiftcardVisible('giftvoucher', false);
        } else {
            $('payment_form_giftvoucher').hide();
            changeGiftcardVisible('giftvoucher', true);
        }
    }
    if ($('giftvoucher_credit')) {
        $('giftvoucher_credit').disabled = false;
        if ($('giftvoucher_credit').checked) {
            $('payment_form_giftvoucher_credit').show();
            changeGiftcardVisible('giftvoucher_credit', false);
        } else {
            $('payment_form_giftvoucher_credit').hide();
            changeGiftcardVisible('giftvoucher_credit', true);
        }
    }
}

function useExistedGiftcard(el) {
    if (el.value) {
        $('giftvoucher-custom-code').hide();
    } else {
        $('giftvoucher-custom-code').show();
    }
}

function changeUseExistedGiftcard(el) {
    if (el.value) {
        $('giftvoucher-custom-code').hide();
        changeGiftcardVisible('giftvoucher-custom-code', true);
    } else {
        $('giftvoucher-custom-code').show();
        changeGiftcardVisible('giftvoucher-custom-code', false);
    }
}

function showGiftCardInput(el) {
    var parent = Element.extend(el.parentNode);
    el.hide();
    parent.down('.giftcard_input').show();
    parent.down('.giftcard_input input').focus();
}

function showGiftCardAmountInput(el) {
    var parent = Element.extend(el.parentNode);
    el.hide();
    parent.down('input').show();
    parent.down('input').disabled = false;
}

function enterUpdateGiftCardInput(el, e) {
    if (e.keyCode == 13) {
        updateGiftCardInput(el);
    }
}

function updateGiftCardInput(el, url_reload) {
    var parent = Element.extend(el.parentNode);
    parent.down('.apply').hide();
    parent.down('.ajax-loader').show();
    var url = $('giftvoucher_cache_url').innerHTML.replace('/addgift', '/updateAmount');
    var code = Element.extend(el.parentNode.parentNode.parentNode.parentNode).down('button').id;
    url += '/code/' + code;
    url += '/amount/' + parent.down('input').value;
    new Ajax.Request(url, {
        method: 'post',
        postBody: '',
        parameters: '',
        onException: function(response, e) {
            $('giftvoucher_message').update('<ul class="error-msg"><li>' + e + '</li></ul>');
        },
        onComplete: function(response) {
            el.show();
            parent.down('.ajax-loader').hide();
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                var needUpdate = true;
                if (res.ajaxExpired && res.ajaxRedirect) {
                    setLocation(res.ajaxRedirect);
                    needUpdate = false;
                }
                if (needUpdate && res.updatepayment) {
                    var eladd = getElement();
                    eladd.hide();
                    $('giftvoucher_container').down('dd.giftvoucher').hide();
                    $('giftvoucher_ajaxload').show();
                    // if (typeof shippingMethod != 'undefined') {
                    // shippingMethod.save();
                    // } else if (typeof billing != 'undefined'){
                    // billing.save();
                    // } else {
                    // save_address_information(save_address_url);
                    // }
                } else if (needUpdate) {
                    if (res.amount) {
                        $(code + '_remove').down('.giftcard-amount').update(res.amount);
                        parent.hide();
                        parent = Element.extend(parent.parentNode);
                        parent.down('.giftcard_change').show();
                    }
                    if (res.html) {
                        var container = $('giftvoucher_container');
                        container.innerHTML = res.html;
                        $('payment_form_giftvoucher').show();
                    }
                    var message = '';
                    if (res.success) {
                        message = '<ul class="success-msg"><li>' + res.success + '</li></ul>';
                        if (typeof (save_shipping_method) != 'undefined')
                            save_shipping_method(shipping_method_url);
                    }
                    if (res.error)
                        message += '<ul class="error-msg"><li>' + res.error + '</li></ul>';
                    if (message != '')
                        $('giftvoucher_message').update(message);
                }
            }
            /*Customize by Vick*/
            var summary = $$('div.onestepcheckout-summary').first();
            summary.update('<div class="loading-ajax">&nbsp;</div>');
            var payment_methods = $$('div.payment-methods')[0];
            payment_methods.update('<div class="loading-ajax">&nbsp;</div>');

            new Ajax.Request(url_reload, {
                method: 'post',
                onComplete: function(transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        payment_methods.hide();
                        payment_methods.update(response.payment_method);
                        payment_methods.show();
                        summary.hide();
                        summary.update(response.summary);
                        summary.show();
                    }
                }
            });
        }
    });
}

// change or update gift card credit to spend
function changeUseGiftCredit(el, url_reload) {
    var url = $('giftvoucher_cache_url').innerHTML.replace('/addgift', '/giftcardcredit');
    if (el.checked) {
        $('payment_form_giftvoucher_credit').show();
        changeGiftcardVisible('giftvoucher_credit', false);
        url += '/giftcredit/1';
    } else {
        $('payment_form_giftvoucher_credit').hide();
        changeGiftcardVisible('giftvoucher_credit', true);
    }
    var eladd = getElement();
    eladd.hide();
    $('giftvoucher_container').down('dt.giftvoucher').hide();
    $('giftvoucher_container').down('dd.giftvoucher').hide();
    $('giftvoucher_container').down('dd.giftvoucher_credit').hide();
    $('giftvoucher_credit_ajaxload').show();
    new Ajax.Request(url, {
        method: 'post',
        postBody: '',
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                if (res.updatepayment) {
                    // if (typeof shippingMethod != 'undefined') {
                    // shippingMethod.save();
                    // } else if (typeof billing != 'undefined'){
                    // billing.save();
                    // } else {
                    // save_address_information(save_address_url);
                    // }
                } else if (res.html) {
                    var container = $('giftvoucher_container');
                    container.innerHTML = res.html;
                    onLoadGiftvoucherForm();
                    var eladd = getElement();
                    eladd.show();
                } else {
                    var eladd = getElement();
                    eladd.show();
                    $('giftvoucher_container').down('dt.giftvoucher').show();
                    $('giftvoucher_container').down('dd.giftvoucher').show();
                    $('giftvoucher_container').down('dd.giftvoucher_credit').show();
                    $('giftvoucher_credit_ajaxload').hide();
                }
            } else {
                var eladd = getElement();
                eladd.show();
                $('giftvoucher_container').down('dt.giftvoucher').show();
                $('giftvoucher_container').down('dd.giftvoucher').show();
                $('giftvoucher_container').down('dd.giftvoucher_credit').show();
                $('giftvoucher_credit_ajaxload').hide();
            }
            /*Customize by Vick*/
            var summary = $$('div.onestepcheckout-summary').first();
            summary.update('<div class="loading-ajax">&nbsp;</div>');
            var payment_methods = $$('div.payment-methods')[0];
            payment_methods.update('<div class="loading-ajax">&nbsp;</div>');

            new Ajax.Request(url_reload, {
                method: 'post',
                onComplete: function(transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        payment_methods.hide();
                        payment_methods.update(response.payment_method);
                        payment_methods.show();
                        summary.hide();
                        summary.update(response.summary);
                        summary.show();
                    }
                }
            });
        }
    });
}

function showCartCreditInput(el) {
    var parent = Element.extend(el.parentNode.parentNode);
    if (el.checked) {
        parent.down('dd.giftvoucher_credit').show();
    } else {
        parent.down('dd.giftvoucher_credit').hide();
    }
}

function showCartGiftCardInput(el) {
    var parent = Element.extend(el.parentNode.parentNode);
    if (el.checked) {
        parent.down('dd.giftvoucher').show();
    } else {
        parent.down('dd.giftvoucher').hide();
    }
}

function showCreditInput(el) {
    var parent = Element.extend(el.parentNode);
    el.hide();
    parent.down('.credit_input').show();
    parent.down('.credit_input input').focus();
}

function enterUpdateCreditInput(el, e) {
    if (e.keyCode == 13) {
        updateCreditInput(el);
    }
}

function updateCreditInput(el, url_reload) {
    var parent = Element.extend(el.parentNode);

    var url = $('giftvoucher_cache_url').innerHTML.replace('/addgift', '/creditamount');
    url += '/amount/' + parent.down('input').value;

    // $('checkout-payment-method-load').down('#checkout-payment-method-load').hide();
    // $('giftvoucher_container').down('dt.giftvoucher').hide();
    // $('giftvoucher_container').down('dd.giftvoucher').hide();
    $('giftvoucher_container').down('dd.giftvoucher_credit').hide();
    $('giftvoucher_credit_ajaxload').show();
    new Ajax.Request(url, {
        method: 'post',
        postBody: '',
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                if (res.updatepayment) {
                    // if (typeof shippingMethod != 'undefined') {
                    // shippingMethod.save();
                    // } else if (typeof billing != 'undefined'){
                    // billing.save();
                    // } else {
                    // save_address_information(save_address_url);
                    // }
                } else if (res.html) {
                    var container = $('giftvoucher_container');
                    container.innerHTML = res.html;
                    onLoadGiftvoucherForm();
                    // $('checkout-payment-method-load').down('#checkout-payment-method-load').show();
                } else {
                    // $('checkout-payment-method-load').down('#checkout-payment-method-load').show();
                    // $('giftvoucher_container').down('dt.giftvoucher').show();
                    // $('giftvoucher_container').down('dd.giftvoucher').show();
                    $('giftvoucher_container').down('dd.giftvoucher_credit').show();
                    $('giftvoucher_credit_ajaxload').hide();
                    parent.hide();
                    parent = Element.extend(parent.parentNode);
                    parent.down('.using_credit').show();
                }
            } else {
                // $('checkout-payment-method-load').down('#checkout-payment-method-load').show();
                // $('giftvoucher_container').down('dt.giftvoucher').show();
                // $('giftvoucher_container').down('dd.giftvoucher').show();
                $('giftvoucher_container').down('dd.giftvoucher_credit').show();
                $('giftvoucher_credit_ajaxload').hide();
            }
            /*Customize by Vick*/
            var summary = $$('div.onestepcheckout-summary').first();
            summary.update('<div class="loading-ajax">&nbsp;</div>');
            var payment_methods = $$('div.payment-methods')[0];
            payment_methods.update('<div class="loading-ajax">&nbsp;</div>');

            new Ajax.Request(url_reload, {
                method: 'post',
                onComplete: function(transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        payment_methods.hide();
                        payment_methods.update(response.payment_method);
                        payment_methods.show();
                        summary.hide();
                        summary.update(response.summary);
                        summary.show();
                    }
                }
            });
        }
    });
}

function changeGiftcardVisible(method, mode) {
    var block = 'payment_form_' + method;
    [block + '_before', block, block + '_after'].each(function(el) {
        element = $(el);
        if (element) {
            element.style.display = (mode) ? 'none' : '';
            element.select('input', 'select', 'textarea', 'button').each(function(field) {
                field.disabled = mode;
            });
        }
    });
}
function checkOutLoadGiftCard(json) {
    if (!$('giftvoucher_container')) {
        var eladd = getElement();
        eladd.insert({
            before: json.html
        });
    }
}

function getElement() {
    if ($('checkout-payment-method-load').down('#checkout-payment-method-load') == undefined) {
        return  $('checkout-payment-method-load');
    } else {
        return $('checkout-payment-method-load').down('#checkout-payment-method-load');
    }
}