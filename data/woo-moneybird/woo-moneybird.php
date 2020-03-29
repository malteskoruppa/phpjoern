<?php
/**
 * Plugin Name: Integration of Moneybird for WooCommerce
 * Plugin URI: https://Woocommerce-moneybird.techastha.com
 * Description: Generate invoice using Moneybird API form WooCommerce Order.
 * Author: techastha
 * Author URI: https://techastha.com
 * Text Domain: wcmb
 * Version: 1.0
 * Requires at least: 5.2.0
 * Requires PHP: 5.6.20
 *
 * WC requires at least: 3.6.0
 * WC tested up to: 3.8.0
 *
 * 
 */
include( plugin_dir_path( __FILE__ ) . 'admin/wcmb-moneybird-api-settings.php');
include( plugin_dir_path( __FILE__ ) . 'admin/wcmb-moneybird-api-general-settings.php');

add_action('woocommerce_thankyou', 'wcmb_generate_invoice_from_new_order', 10, 1);
function wcmb_generate_invoice_from_new_order( $order_id ) {
    if ( ! $order_id ) return;
    // access tocken get
    $access_token = get_option('wcmb_moneybird_access_token');

    if ( ! $access_token ) return;
        
    if( ! get_post_meta( $order_id, 'wcmb_moneybird_invoice_generated', true ) ) {
        // Get an instance of the WC_Order object
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );
        // administration_id Get
        $administrationsUrl = "https://moneybird.com/api/v2/administrations.json";
        $getAdministraterData = wp_remote_get( $administrationsUrl, array('headers' => $headers,));
        $administrater = json_decode(wp_remote_retrieve_body($getAdministraterData));
        
        $addministrater_id = $administrater[0]->id;

        // User Data Get
        $order = wc_get_order( $order_id );
        $userEmail = $order->get_billing_email();
        $userFirstName = $order->get_billing_first_name();
        $userLastName = $order->get_billing_last_name();
        $userCompanyName = $order->get_billing_company();
        $userAddress = $order->get_billing_address_1();
        $email = $order->get_billing_email();
        // email verify
        $verifyEmailUrl = "https://moneybird.com/api/v2/".$addministrater_id."/contacts.json?query=".$userEmail."";
        $verifyEmailData = wp_remote_get( $verifyEmailUrl, array('headers' => $headers,));
        $verifyEmail = json_decode(wp_remote_retrieve_body($verifyEmailData));
        if(!empty($verifyEmail)){
            $contactId = $verifyEmail[0]->id;
        } else {
            $contactData = json_encode([
                'contact'=>[
                    'company_name'        => $userCompanyName,
                    'firstname'           => $userFirstName, 
                    'lastname'            => $userLastName,
                    'address1'            => $userAddress,
                    'email'               => $userEmail
                ],
            ]);
            
            $createcontactUrl = "https://moneybird.com/api/v2/".$addministrater_id."/contacts.json";
            $createContactData = wp_remote_post( $createcontactUrl, array(
                'method'      => 'POST',
                'timeout'     => 120,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking'    => true,
                'headers'     => $headers,
                'body'        => $contactData,
                'cookies'     => array()
                )
            );
            if ( is_wp_error( $createContactData ) ) {
                $error_message = $createContactData->get_error_message();
                echo "Something went wrong: $error_message";
            } else {
                $contactDetail = json_decode(wp_remote_retrieve_body($createContactData));
                $contactId = $contactDetail->id;
            }
        }
        // create array of user post data in invoice
        $postData = array();
        $postData['sales_invoice']['contact_id'] = $contactId;
        if(get_option('wcmb_moneybird_document_style_id')){
            $postData['sales_invoice']['document_style_id'] = get_option('wcmb_moneybird_document_style_id');
        }
        if(get_option('wcmb_moneybird_workflow_id')){
            $postData['sales_invoice']['workflow_id'] = get_option('wcmb_moneybird_workflow_id');
        }
        // Loop through order items
        $oProducts = array();
        $i = 0;
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $oProducts[$i]['description'] = $product->get_name();
            $oProducts[$i]['price'] = $product->get_price();
            $oProducts[$i]['amount'] = $item->get_quantity();
            $i++;
        }
        $postData['sales_invoice']['details_attributes'] = $oProducts;  
        $postDataJson = json_encode($postData);
        
        // sales invoice create
        $createInvoiceUrl = "https://moneybird.com/api/v2/".$addministrater_id."/sales_invoices";
        $createInvoiceData = wp_remote_post( $createInvoiceUrl, array(
            'method'      => 'POST',
            'timeout'     => 120,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'headers'     => $headers,
            'body'        => $postDataJson,
            'cookies'     => array()
            )
        );
        
        if ( is_wp_error( $createInvoiceData ) ) {
            $error_message = $createInvoiceData->get_error_message();
            echo "Something went wrong:" . $error_message;
        } else {
            $invoiceData = json_decode(wp_remote_retrieve_body($createInvoiceData));
            if($invoiceData){
                $invoice_id = $invoiceData->id;
                $invoice_url = "https://moneybird.com/".$addministrater_id."/sales_invoices/".$invoice_id.".pdf";
                echo '<a href="'.$invoice_url.'">'.__( 'Download Invoice from MoneyBird', 'wcmb' ).'</a>';
            }
        } 
        // Flag the action as done (to avoid repetitions on reload for example)
        $order->update_meta_data( 'wcmb_moneybird_invoice_generated', true );
        $order->update_meta_data( 'wcmb_moneybird_invoice_url', $invoice_url );
        $order->save();
    } else {
        $invoice_url = get_post_meta( $order_id, 'wcmb_moneybird_invoice_url', true );
        echo '<a href="'.$invoice_url.'">'.__( 'Download Invoice from MoneyBird', 'wcmb' ).'</a>';
    }
}

