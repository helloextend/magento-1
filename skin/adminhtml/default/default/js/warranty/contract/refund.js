const refundConfig = [];
$j(document).ready(function () {

    function refund(url, contractId, itemId) {
        event.preventDefault();
        showLoader();
        $j.get(url, {
            contractId: contractId,
            itemId: itemId
        }).done(function (data) {
            openRefundPopup('Refund Successful', getWindowContent('success'))
        }).fail(function (jqXHR, textStatus, errorThrown) {
            //TODO: add error notification
            openRefundPopup('Refund validation error', getWindowContent('zero-amount'));
        });
    }

    function validate(url, contractId, itemId) {
        showLoader()
        $j.get(url, {
            contractId: contractId,
            itemId: itemId,
            validation: true
        }).done(function (data) {
            if (data.amountValidated > 0) {
                let popupModalHtml = getWindowContent('confirm', itemId);
                if (
                    typeof refundConfig[itemId].contractId === 'object'
                    && Object.keys(refundConfig[itemId].contractId).length > 1
                ) {
                    popupModalHtml += "<input type='hidden' id='contract_id' value='" + JSON.stringify(contractId) + "' />";
                }
                openRefundPopup('Refund confirmation', popupModalHtml);
                $j('#refund-amount-validation-text').text('$' + data.amountValidated);
            } else {
                openRefundPopup('Refund validation failed', getWindowContent('zero-amount'));
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            openRefundPopup('Refund validation error', getWindowContent('zero-amount'));
        });
    }

    $j('.action-extend-refund').bind("click", function (event) {
        let currentItemId = $j(this).data('current_item_id')
        let url = refundConfig[currentItemId].url;
        let contractId = refundConfig[currentItemId].contractId;
        let itemId = refundConfig[currentItemId].itemId;
        let isPartial = String(refundConfig[currentItemId].isPartial);
        if (isPartial) {
            let contractItem = '';
            $j.each(contractId, function (index, value) {
                contractItem += '<input type="checkbox" id="pl-contract' + index + '" name="pl-contract' + index + '" value="' + value + '">' +
                    '<label for="pl-contract' + index + '">' + value + '</label><br>';
            });
            openRefundPopup('Refund confirmation', getWindowContent('validation', itemId));
            $j("div#partial-contracts-list").append(contractItem);
        } else {
            validate(url, contractId, itemId);
        }
    })

    $j('body').on('click', '#ok-button', function (e) {
        let currentItemId = $j(this).data('current_item_id')
        let url = refundConfig[currentItemId].url;
        let contractId = refundConfig[currentItemId].contractId;
        if (
            typeof refundConfig[currentItemId].contractId === 'object'
            && Object.keys(refundConfig[currentItemId].contractId).length > 1
        ) {
            contractId = JSON.parse($j('#contract_id').val());
        }
        let itemId = refundConfig[currentItemId].itemId;
        refund(url, contractId, itemId);
        Windows.close('refund_window');
    });

    $j('body').on('click', '#validate-button', function (e) {
        let currentItemId = $j(this).data('current_item_id')
        let url = refundConfig[currentItemId].url;
        let contractId;
        let itemId = refundConfig[currentItemId].itemId;
        let selectedRefundsArr = [];
        $j.each($j("#refund_window input[name^='pl-contract']:checked"), function () {
            selectedRefundsArr.push($j(this).val());
        });
        contractId = Object.assign({}, selectedRefundsArr);
        validate(url, contractId, itemId);
        Windows.close('refund_window');
    });

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

    $j('body').on('click', '#cancel-button', function (e) {
        Windows.close('refund_window');
    });

    $j('body').on('click', '#success-button', function (e) {
        location.reload();
    });

    $j('body').on('click', '#partial-select-all', function (e) {
        if ($j('#partial-select-all').hasClass("selected")) {
            $j("#partial-contracts-list input").prop("checked", false);
        } else {
            $j("#partial-contracts-list input").prop("checked", true);
        }
        $j(this).toggleClass("selected")
    });

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

    function getWindowContent(type, itemId = null) {
        let content = [];
        content['confirm'] = '<p style="margin: 10px 0;"><strong>Are you sure you want to refund <span id="refund-amount-validation-text"></span>?</strong></p>';
        content['confirm'] += '<div class="buttons-set a-right" style="margin-top:30px">';
        content['confirm'] += '<button type="button" class="scalable cancel" id="cancel-button"><span>Cancel</span></button>';
        content['confirm'] += '<button type="button" class="scalable save" data-current_item_id="' + itemId + '" id="ok-button"><span>Send Refund Request</span></button>'
        content['confirm'] += '</div>';

        content['validation'] = '<p style="margin: 10px 0;"><strong>Select the Contract IDs to process</strong></p>';
        content['validation'] += '<div id="partial-contracts-list" style="margin: 10px 0;"></div>';
        content['validation'] += '<button type="button" class="scalable add" id="partial-select-all"><span>Select/Unselect All</span></button>';
        content['validation'] += '<div class="buttons-set a-right" style="margin-top:20px">';
        content['validation'] += '<button type="button" class="scalable cancel" id="cancel-button"><span>Cancel</span></button>';
        content['validation'] += '<button type="button" class="scalable save" data-current_item_id="' + itemId + '" id="validate-button"><span>Validate Request</span></button>';
        content['validation'] += '</div>';

        content['zero-amount'] = '<p style="margin: 10px 0;">';
        content['zero-amount'] += '<strong>Sorry, unexpected error, currently you can\'t refund selected item(s). Please try again later.</strong></p>';
        content['zero-amount'] += '<div class="buttons-set a-right" style="margin-top:30px">';
        content['zero-amount'] += '<button type="button" class="scalable cancel" id="cancel-button"><span>Close</span></button>';
        content['zero-amount'] += '</div>';

        content['success'] = '<p style="margin: 10px 0;"><strong>The request was successfully completed.</strong></p>';
        content['success'] += '<div class="buttons-set a-right" style="margin-top:20px">';
        content['success'] += '<button type="button" class="scalable save" id="success-button"><span>Ok</span></button>';
        content['success'] += '</div>';

        return content[type]
    }
});
