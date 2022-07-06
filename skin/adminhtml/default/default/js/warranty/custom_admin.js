$j(document).ready(function () {
    $j("#warranty_authentication_auth_mode").change(function () {
        if ($j(this).val() == "0") {
            $j("#warranty_authentication_store_id").prop('disabled', true);
            $j("#warranty_authentication_api_key").prop('disabled', true);
            $j("#warranty_authentication_sandbox_store_id").prop('disabled', false);
            $j("#warranty_authentication_sandbox_api_key").prop('disabled', false);
        } else {
            $j("#warranty_authentication_store_id").prop('disabled', false);
            $j("#warranty_authentication_api_key").prop('disabled', false);
            $j("#warranty_authentication_sandbox_store_id").prop('disabled', true);
            $j("#warranty_authentication_sandbox_api_key").prop('disabled', true);
        }
    });
    $j("#warranty_authentication_auth_mode").change();

    $j("#warranty_enableExtend_enable").change(function () {
        if ($j(this).val() == "1") {
            $j("#syncBtn").prop('disabled', false);
        } else {
            $j("#syncBtn").prop('disabled', true);
        }
    });

    $j("#warranty_enableExtend_enable").change();
});

function syncProducts(url) {
    showLoader();
    $j.get(
        url,
        {}
    ).done(function (data) {
        openRefundPopup('Product Synchronization', getWindowContent(data.message, data.status))
    }).fail(function (jqXHR, textStatus, errorThrown) {
        //TODO: add error notification
        let responseData = jqXHR.responseJSON;
        openRefundPopup('Product Synchronization Error', getWindowContent(responseData.message, responseData.status));
    });
}

function orderSyncUpdate(progress) {
    console.log('update', progress);
    window.orderSyncProgress = progress;
    jQuery("#orderSyncProgress").html(progress.text + " — " + Math.ceil(progress.percent) + '%');
}

function orderSyncFinish(argument) {
    console.log('orderSyncProgress', argument);
    let status = 'success';
    let message = window.orderSyncProgress.text;
    if (window.orderSyncProgress && window.orderSyncProgress.max != window.orderSyncProgress.current) {
        status = 'FAIL';
        message = 'Sync was not finished. See logs.';
    }
    openRefundPopup('Order Synchronization', getWindowContent(message, status))
    $j('#orderSyncProgress').remove();
    hideLoader();
}

function syncOrders(url) {
    showLoader();
    var iframeId = "sync_order_iframe";
    var iframeTag = jQuery("<iframe/>",
        {
            src: url,
            id: iframeId
        });
    $j("#contract_window_mask").html(iframeTag);
    $j("#loading_mask_loader").append(
        jQuery('<p/>', {id: 'orderSyncProgress'})
    );

    window.orderSyncProgress = {};
    // $j.get(
    //     ,
    //     {}
    // ).done(function (data) {
    //     openRefundPopup('Order Synchronization', getWindowContent(data.message, data.status))
    // }).fail(function (jqXHR, textStatus, errorThrown) {
    //     let responseData = jqXHR.responseJSON;
    //     openRefundPopup('Orders Synchronization Error', getWindowContent(responseData.message, responseData.status));
    // });
}


function openRefundPopup(
    title = 'Warranty Refund Dialog',
    content = null
) {
    if ($('refund_window') && typeof (Windows) != 'undefined') {
        Windows.focus('refund_window');
        return;
    }
    hideLoader();
    var dialogWindow = Dialog.info(content, {
        closable: true,
        resizable: false,
        draggable: true,
        className: 'magento',
        windowClassName: 'popup-window',
        title: title,
        top: 50,
        width: 300,
        height: 'auto',
        zIndex: 1000,
        recenterAuto: false,
        hideEffect: Element.hide,
        showEffect: Element.show,
        id: 'refund_window',
        buttonClass: "form-button",
    });
}

function showLoader() {
    $j('body').append('<div id="contract_window_mask" class="popup-window-mask" style="display: none;"></div>')
    $j("#contract_window_mask").show();
    $j("#contract_window_mask").height($j(document).height());
    $j("#loading-mask").show();
}

function hideLoader() {
    $j("#contract_window_mask").remove();
    $j("#loading-mask").hide();
}

function getWindowContent(message, status) {
    let color = status === 'FAIL' ? 'red' : 'green';
    let content = '<p style="margin: 10px 0;"><strong>' + message + '</strong></p>';
    content += '<p style="margin: 10px 0;">Current Status - <strong style="color:' + color + '">' + status + '</strong></p>';
    content += '<div class="buttons-set a-right" style="margin-top:20px">';
    content += '<button type="button" class="scalable save" onclick="location.reload();"><span>Ok</span></button>';
    content += '</div>';

    return content;
}
