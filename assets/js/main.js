/* Frontend JS */

/** 
 * Copy codes (copy to clipboard) 
*/
if(document.getElementById("copy-recovery-codes")){

    document.addEventListener("DOMContentLoaded", function() {
        const copyBtn = document.getElementById("copy-recovery-codes");
        const codesList = document.getElementById("recovery-codes-list");

        copyBtn.addEventListener("click", function() {
            const codes = Array.from(codesList.querySelectorAll("li"))
                .map(li => li.textContent.trim())
                .join("\n");

            navigator.clipboard.writeText(codes).then(() => {
                copyBtn.textContent = "✅ Codes copied!";
                setTimeout(() => copyBtn.textContent = "📋 Copy all codes", 2000);
            });
        });
    });

}

/** 
 * Show and hide recovery code field
*/
jQuery(document).ready(function($){
    $('#recovery-code').on('change', function(){
        if($(this).is(':checked')) {
            $('.totp-login-check-code').hide(); 
            $('.totp-login-check-code').val(''); 
            $('.totp-login-check-code').removeAttr('required');
            $('.totp-login-bk-code').show(); 
            $('.totp-login-bk-code').prop('required', true); 
        } else {
            $('.totp-login-check-code').show(); 
            $('.totp-login-check-code').prop('required', true); 
            $('.totp-login-bk-code').hide();
            $('.totp-login-bk-code').val('');
            $('.totp-login-bk-code').removeAttr('required');
        }
    });
});