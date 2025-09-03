jQuery(document).ready(function($){

    var site_url        = WPURLS.siteurl;
    var admin_ajaxurl   = site_url+'/wp-admin/admin-ajax.php';

    var $form = $('#woo-totp-verification-form');
    var $messageBox = $('.woo-totp-notifications');

    if ($form.length === 0) return; // safety check

    $form.on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: admin_ajaxurl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: { withCredentials: true },
            success: function(response) {

                if(response.success){

                    window.location.href = response.redirect;

                }else if(response.expired){

                    $('#woo-totp-verification-form').remove();

                    $messageBox.html(
                        '<div class="notification failure"><strong>' + response.message + '</strong></div>'
                    );

                }else{

                    $messageBox.html(
                        '<div class="notification failure"><strong>' + response.message + '</strong></div>'
                    );
                }
                
            },
            error: function(xhr, status, error) {
                console.error(error);
                $messageBox.html(
                    '<div class="notification failure"><strong>Unexpected error, please try again.</strong></div>'
                );
            }
        });
    });
});