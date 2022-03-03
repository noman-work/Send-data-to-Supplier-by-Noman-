<?php
/**
 * Plugin Name: Send data to Supplier by Noman 🚚
 * Plugin URI: https://nomanwc.com/
 * Description: Ideal for improving store management or for dropshippers.
 * Version: 1.1.1
 * Author: Adboullah Al Noman
 * Author URI: https://nomanwc.com/
 * 
 */


add_action('add_meta_boxes', 'send_order_edit_email');

function send_order_edit_email()
{
    add_meta_box('noman_meta_box', 'Send data to Supplier', 'noman_meta_box_callback', 'shop_order', 'side', 'high');
}

function noman_meta_box_callback()
{   
    //Send data by clicking this button
    $post_id = isset($_GET['post']) ? $_GET['post'] : false;
    
    if(! $post_id ) return; // Exit
    /**
     * This Data is Coming from ACF Field 
     * @supplier_message
     * @supplier_email_address
     */
    $supply_msg = get_field('supplier_message', $post_id);
    $supply_email =  get_field('supplier_email_address', $post_id);

    $value="send_order_data";
    echo "Add Both Supplier Message and Supplier E-mail. Hit Update you will see send data Option.";
    
    $send_message = get_post_meta( $post_id, 'escaped_json', true );
    
    if( 1 == $send_message ){
        echo "<div style='
        background: #2f3640;
        color: #fff;
        font-weight: 600;
        padding: 6px 10px;
        border-radius: 3px;
        margin: 9px 0;
    '>E-mail Already Sent!!</div>";
    }  

    
    if( !empty($supply_email) )
    {
        ?>   <p><a style="" href="?post=<?php echo $post_id; ?>&action=edit&send=<?php echo $value; ?>" class="button"><?php _e('Send Order!'); ?></a></p> <?php

        if ( isset( $_GET['send'] ) && ! empty( $_GET['send'] ) ) {
            send_order_data_to_dropship($post_id);
            $escaped_json = 1;
            update_post_meta( $post_id, 'escaped_json', $escaped_json );
            
            wp_redirect(get_home_url().'/wp-admin/post.php?post='.$post_id.'&action=edit'); 
        }
    }
}

/**
 * sends an email through WooCommerce with the correct headers
 */
function send( $id, $object, $to, $subject, $message, $notification ) {
    $email = new WC_Email();

    $email->id = "advanced_notification_{$notification->notification_id}";
    $email->object = $object;

    $email->send( $to, $subject, $message, $email->get_headers(), $email->get_attachments() );
}

/**
 * Sends an email to the dropship
 */
function send_order_data_to_dropship( $order_id){
    //Global Variables
    global $woocommerce, $wpdb;

    //Get the order
    $order = new WC_Order( $order_id );
    $customer_name = $order->billing_first_name . ' ' . $order->billing_last_name;
    $plugin_path = plugin_dir_path( dirname( __FILE__ ) );
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $email_heading = '🚀 New customer order from ' . $customer_name;
    $postid = get_the_id();
    $supply_email =  get_field('supplier_email_address', $order_id);

    $order_date_c 	 = $order->get_date_created()->format( 'c' );
    $order_date_text = wc_format_datetime( $order->get_date_created() );

    $subject = apply_filters( 'woocommerce_email_subject_new_order', sprintf( __( '✨ New Order from %s', 'woocommerce-advanced-notifications' ) , $customer_name ), $order, null );

    
    // load the mailer class
    $mailer = WC()->mailer();
    ob_start();
        wc_get_template('order-edit-email/email.php', array(
            'order' => $order
        ), '', $plugin_path . '/');
    $message = ob_get_clean();
    $wc_email = new WC_Email();
    $formatted_message = $wc_email->style_inline( $mailer->wrap_message( $email_heading, $message ) );

    // Send the email
    send( 'new_order', $order, $supply_email, $subject, $formatted_message, $notification );
}

/**
 * Generating the email fields
 */
if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array(
        'key' => 'group_621f7e6bb2c23',
        'title' => 'Supply Data',
        'fields' => array(
            array(
                'key' => 'field_621f7e754f289',
                'label' => 'Supplier Message',
                'name' => 'supplier_message',
                'type' => 'textarea',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'maxlength' => '',
                'rows' => '',
                'new_lines' => 'wpautop',
            ),
            array(
                'key' => 'field_621f8ff98b865',
                'label' => 'Supplier Email Address',
                'name' => 'supplier_email_address',
                'type' => 'email',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'shop_order',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'side',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));    
endif;		
