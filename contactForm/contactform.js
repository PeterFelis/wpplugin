
<script>

    const nonce = "<?php echo wp_create_nonce('wp_rest');?>";

    jQuery('#simple_contact_form').submit(function(e){
        e.preventDefault();
    const form = jQuery(this).serialize();
    console.log(form);

    jQuery.ajax({
        method:'post',
    url: "<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>",
    headers: {'X-WP-Nonce': nonce},
    data: form
    })
    });

</script>