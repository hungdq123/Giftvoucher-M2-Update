/*Giftvoucher JS*/

// Show edit amount for existed Gift Card
function showGiftCardAmountInput(el) {
    var parent = Element.extend(el.parentNode);
    el.hide();
    parent.down('input').show();
    parent.down('input').disabled = false;
}

// Remove Gift Card from quote
function removeGiftVoucher(url){
	new Ajax.Request(url, {
		method:'post',
		postBody: '',
		onException: '',
		onComplete: function (response){
			if (response.responseText.isJSON()){
                if (order) {
                    order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {reset_shipping: true});
                }
			}
		}
	});
}

// Change use a existed or another Gift Card
function useExistedGiftcard(el) {
    if (el.value) {
        $('giftvoucher-custom-code').hide();
    } else {
        $('giftvoucher-custom-code').show();
    }
}

// Apply Gift Card Form
function applyGiftCardForm(url) {
    var giftcredit_checked = false;
    if($('giftvoucher_credit')) giftcredit_checked = $('giftvoucher_credit').checked;
    if(giftcredit_checked && $$('dd.giftvoucher_credit')[0].down('.input-text').value==0)
    {
        if($('giftcredit_notice')) $('giftcredit_notice').style.display="block";
        if($$('dd.giftvoucher_credit')[0]!=null) $$('dd.giftvoucher_credit')[0].down('.input-text').addClassName('validation-failed');
    }
    if($('giftvoucher').checked)
    {
        if($('giftvoucher_code') && $('giftvoucher_code').value=='')
        {
            if($('giftvoucher_existed_code') && $('giftvoucher_existed_code').value=='')
            {
                $('giftvoucher_code').addClassName('validation-failed');
                $('giftvoucher_existed_code').addClassName('validation-failed');
                $('giftcard_notice_2').style.display="block";
            }
            else
            {
                $('giftvoucher_code').addClassName('validation-failed'); 
                $('giftcard_notice_1').style.display="block";
            }
        }
    }
    if  (
            (giftcredit_checked && $$('dd.giftvoucher_credit')[0].down('.input-text').value!=0)
            ||(     $('giftvoucher').checked 
                && ($$('.giftvoucher-discount-code')[0]!=null||($('giftvoucher_code') && $('giftvoucher_code').value!='')||($('giftvoucher_existed_code') && $('giftvoucher_existed_code').value!=''))
              )
        )
    {
        if($('giftcard_notice_1'))$('giftcard_notice_1').style.display="none";
        if($('giftcard_notice_2'))$('giftcard_notice_2').style.display="none";
        if($('giftvoucher_code'))$('giftvoucher_code').removeClassName('validation-failed');
        if($('giftvoucher_existed_code'))$('giftvoucher_existed_code').removeClassName('validation-failed');
        if($('giftcredit_notice')) $('giftcredit_notice').style.display="none";
        if($$('dd.giftvoucher_credit')[0]!=null)$$('dd.giftvoucher_credit')[0].down('.input-text').removeClassName('validation-failed');
        
        var elements = $('giftvoucher_container').select('input', 'select', 'textarea');
        elements.push($$('[name="form_key"]')[0]);
        var params = Form.serializeElements(elements);
        new Ajax.Request(url, {
            method:'post',
            postBody: params,
            parameters: params,
            onException: '',
            onComplete: function (response) {
                if (response.responseText.isJSON()) {
                    if (order) {
                        order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {reset_shipping: true});
                    }
                }
            }
        });
    }
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
