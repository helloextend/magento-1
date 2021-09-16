$j(document).ready( function() {
    $j("#warranty_authentication_auth_mode").change(function(){
        if($j(this).val() == "0"){
            $j("#warranty_authentication_store_id").prop('disabled', true);
            $j("#warranty_authentication_api_key").prop('disabled', true);
            $j("#warranty_authentication_sandbox_store_id").prop('disabled', false);
            $j("#warranty_authentication_sandbox_api_key").prop('disabled', false);
        }else{
            $j("#warranty_authentication_store_id").prop('disabled', false);
            $j("#warranty_authentication_api_key").prop('disabled', false);
            $j("#warranty_authentication_sandbox_store_id").prop('disabled', true);
            $j("#warranty_authentication_sandbox_api_key").prop('disabled', true);
        }
    });
    $j("#warranty_authentication_auth_mode").change();

    $j("#warranty_enableExtend_enable").change(function () {
        if($j(this).val() == "1"){
            $j("#syncBtn").prop('disabled', false);
        } else {
            $j("#syncBtn").prop('disabled', true);
        }
    });

    $j("#warranty_enableExtend_enable").change();
});