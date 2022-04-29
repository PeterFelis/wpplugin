<?php

/** 
 * 
 * Plugin Name: contact form
 * Description: lalalala
 * 
 * 
 * */

if (!defined('ABSPATH')) {
    echo 'go away and stay away';
    exit();
}

class contactForm
{
    public function __construct()
    {

        add_action('init', array($this, 'create_custom_post_type'));

        // add assets dus enqueuescripts
        add_action('wp_enqueue_scripts', array($this, 'loadAssets'));

        // add shortcode
        add_shortcode('contact-form', array($this, 'load_shortcode'));

        // js voor verzenden form
        add_action('wp_footer', array($this, 'load_scripts'));

        // hook voor rest endpoint api
        add_action('rest_api_init', array($this, 'register_rest_api'));
    }


    public function create_custom_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'contact form',
                'singular_name' => 'contact form entry'
            ),
            'public' => true,
            'description' => 'eenvoudig contact formulier',
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'public_queryable' => false,
            'capability' => 'manage_options',
            'menu_icon' => 'dashicons-id'
        );
        register_post_type('contact_form', $args);
    }


    public function loadAssets()
    {
        wp_register_style('css', plugin_dir_url(__FILE__) . 'contactform.css', false, 'all');
        wp_enqueue_style('css');
    }

    public function load_shortcode()
    {
        echo file_get_contents(plugin_dir_url(__FILE__) . 'contactform.html');
    }

    public function load_scripts()
    {
?><script>
            const nonce = "<?php echo wp_create_nonce('wp_rest'); ?>";

            jQuery('#simple_contact_form').submit(function(e) {
                e.preventDefault();
                const form = jQuery(this).serialize();
                console.log(form);

                jQuery.ajax({
                    method: 'post',
                    url: "<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email'); ?>",
                    headers: {
                        'X-WP-Nonce': nonce
                    },
                    data: form
                })
            });
        </script>
<?php







    }

    public function register_rest_api()
    {
        register_rest_route('simple-contact-form/v1', 'send-email', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form')
        ));
    }

    public function handle_contact_form($data)
    {
        $headers = $data->get_headers();
        $params = $data->get_params();

        $nonce = $headers['x_wp_nonce'][0];
        if (wp_verify_nonce($nonce, 'wp_rest')) {
            $post_id = wp_insert_post([
                'post_type' => 'contact_form',
                'post_title' => 'contact enquiry',
                'post_status' => 'publish'
            ]);
            if ($post_id) {
                return new WP_REST_Response('Thank you for your email', 200);
            }
        } else {
            return new WP_REST_RESPONSE('message nog sent', 422);
        }
    }
}

new contactForm;
