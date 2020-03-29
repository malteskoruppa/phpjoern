<?php
add_action( 'admin_menu', 'wcmb_moneybird_api_setting_menu_page' );
function wcmb_moneybird_api_setting_menu_page() {
    add_menu_page( __( 'Moneybird API Setting', 'wcmb' ), 'Moneybird API', 'manage_options','moneybird-settings-page','wcmb_moneybird_api_settings_callback', 'dashicons-admin-generic', 80); 
    add_submenu_page( 'moneybird-settings-page', __( 'Moneybird API Setting', 'wcmb' ), 'General Setting','manage_options', 'moneybird-general-settings-page','wcmb_moneybird_api_general_callback');
}

/*custom js add*/
add_action('admin_enqueue_scripts', 'wcmb_contact_directory_submit_form_ajax');
function wcmb_contact_directory_submit_form_ajax() {
    // load our jquery file that sends the $.post request
    wp_enqueue_style('wcmb-moneybird-custom', plugin_dir_url( __FILE__ ).'css/wcmb-moneybird-layout.css' );
    wp_enqueue_script( "wcmb-moneybird-custom", plugin_dir_url( __FILE__ ) . 'js/wcmb-admin-script.js', array( 'jquery' ) );
    // make the ajaxurl var available to the above script
    wp_localize_script( 'wcmb-moneybird-custom', 'wcmb_moneybird_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );  
}

// custom column add invoice -> view to invoice
add_filter( 'manage_edit-shop_order_columns', 'wcmb_moneybird_new_order_column' );
function wcmb_moneybird_new_order_column( $columns ) {
    $columns['wcmb_invoice'] = __( 'Invoice','wcmb' );
    return $columns;
}
// new order custome column content
add_action( 'manage_shop_order_posts_custom_column', 'wcmb_moneybird_new_order_column_content', 2 );
function wcmb_moneybird_new_order_column_content($column_name ) {
    global $post;
    if ( $column_name === 'wcmb_invoice' ) {
        $invoice_url = get_post_meta( $post->ID, 'wcmb_moneybird_invoice_url', true );
        if($invoice_url){
            echo '<a href="'.$invoice_url.'" id="wcmb_invoice_url" class="button button-primary" target="_blank" >'.__('View', 'wcmb' ).'</a>';
        }
    }
}
add_action('wp_ajax_get_wcmb_clientid_secretid_data', 'wcmb_get_clientid_secretid_data_callBack');
add_action('wp_ajax_nopriv_get_wcmb_clientid_secretid_data', 'wcmb_get_clientid_secretid_data_callBack');
function wcmb_get_clientid_secretid_data_callBack(){
    
    $wcmb_nonce   = sanitize_text_field($_POST['wcmb_nonce']);

    if ( ! wp_verify_nonce( $wcmb_nonce, 'wcmb-moneybird-data' ) ) {
        die( __( 'Security check', 'wcmb' ) ); 
    } else {

        $clientId   = sanitize_text_field($_POST['clientId']);
        $secretId   = sanitize_text_field($_POST['secretId']);

        update_option('wcmb_moneybird_client_id', $clientId);
        update_option('wcmb_moneybird_secret_id', $secretId);

        $result = array();
        // access url generate.....
        $client_id = $clientId;
        $callback = admin_url('admin.php');
        $scopes = array("sales_invoices", "documents");
        $getAuthorizeUrl = wcmb_authorize_url_create($client_id, $callback, $scopes);
        
        $result['accesssUrl'] = $getAuthorizeUrl;
        $resultJson = json_encode($result);
        echo $resultJson;
        exit();
    }
}

add_action('wp_ajax_wcmb_reset_moneybird_api_data', 'wcmb_reset_moneybird_api_data_callback');
add_action('wp_ajax_nopriv_wcmb_reset_moneybird_api_data', 'wcmb_reset_moneybird_api_data_callback');
function wcmb_reset_moneybird_api_data_callback(){
    
    delete_option('wcmb_moneybird_client_id');
    delete_option('wcmb_moneybird_secret_id');
    delete_option('wcmb_moneybird_access_token');
    delete_option('wcmb_moneybird_selected_administration');
    delete_option('wcmb_moneybird_document_style_id');
    delete_option('wcmb_moneybird_workflow_id');
    
    $redirectresult = array();
    $redirekUrl = admin_url('admin.php?page=moneybird-settings-page');
    $redirectresult['suuccess'] = 1;
    $redirectresult['resetUrl'] = $redirekUrl;
    $resultJson = json_encode($redirectresult);
    echo $resultJson;
    exit();
}
    
function wcmb_moneybird_api_settings_callback(){
    
?>  
    <div class="wrap">
        <h1><?php echo __('MoneyBird API Settings', 'wcmb' ); ?></h1>
        <?php
            if(isset($_GET['error_description'])){
        ?>
            <div class="error">
                <p><strong><?php echo $_GET['error_description'];?></strong></p></div>
            </div>
        <?php
            }
            // disable column variable
            $x = "";
            if(get_option('wcmb_moneybird_client_id')){
                $x = 'disabled = "disabled"';
            }
            $y = "";
            if(get_option('wcmb_moneybird_secret_id')){
                $y = 'disabled = "disabled"';
            }
        ?>

        <form method="post" id="wcmb_mbform">
            <table class="form-table">
                <tbody>
                    <tr valign="top">

                        <th scope="row">
                            <label><?php echo __('Client ID', 'wcmb' ); ?></label>
                        </th>
                        <td>
                            <input name="wcmb_clientid" id="wcmb_clientid" class="wcmb_clientIdWidth"  type="text"  placeholder="<?php echo __('Enter the MoneyBird Client ID', 'wcmb' ); ?>" value="<?php echo get_option('wcmb_moneybird_client_id');?>" <?php echo $x;?>>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label><?php echo __('Client Secret ID', 'wcmb' ); ?></label>
                        </th>
                        <td>
                            <input name="wcmb_clientSecret" id="wcmb_clientSecret" class="wcmb_clientSecretWidth" type="text"  placeholder="<?php echo __('Enter the MoneyBird Client secret ID', 'wcmb' ); ?>" value="<?php echo get_option('wcmb_moneybird_secret_id');?>" <?php echo $y;?>>
                        </td>
                    </tr>
                    <?php 
                        if(get_option('wcmb_moneybird_access_token')){
                    ?>
                    <tr valign="top" class="access_tocken_row">
                        <th scope="row">
                            <label><?php echo __('Access Token Key', 'wcmb' ); ?></label>
                        </th>
                        <td>
                            
                            <input name="wcmb_token" id="wcmb_token" class="wcmb_reset wcmb_resetWidth" type="text" value="<?php echo get_option( 'wcmb_moneybird_access_token' ); ?>" disabled="disabled">
                            <p class="description" id=""><?php echo __('<b>How to Create API token : </b> You can be done by logging in to your Moneybird account and visit the page  ', 'wcmb' ); ?> <a href="https://moneybird.com/user/applications/new" target="_blank"><?php echo __('https://moneybird.com/user/applications/new ', 'wcmb' ); ?></a>.</p>
                            <p class="description" id=""><?php echo __('<b>Note: </b> Go to Currency options and Please select your Euro (€) Currency. ', 'wcmb' ); echo  admin_url('admin.php')?></p>
                        </td>
                    </tr>
                    <?php $wcmb_moneybird_selected_administration = get_option( 'wcmb_moneybird_selected_administration' ); ?>
                    <?php if($wcmb_moneybird_selected_administration){ ?>
                    <tr valign="top" class="access_tocken_row">
                        <th scope="row">
                            <label><?php echo __('Selected Administrator', 'wcmb' ); ?></label>
                        </th>
                        <td>
                            <p class="description" id="wcmb_administrator_id">
                                <b><?php echo __('Administrator Name: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->name; ?>
                            </p>
                            <p class="description" id="wcmb_administrator_id">
                                 <b><?php echo __('Administrator Id: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->id; ?>
                            </p>
                            <p class="description" id="wcmb_administrator_language"><b><?php echo __('Administrator Language: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->language; ?></p>
                            <p class="description" id="wcmb_administrator_currency"><b><?php echo __('Administrator Currency: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->currency; ?></p>
                            <p class="description" id="wcmb_administrator_country"><b><?php echo __('Administrator Country: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->country; ?></p>
                            <p class="description" id="wcmb_administrator_time_zone"><b><?php echo __('Administrator Time-Zone: ', 'wcmb' ); ?></b><?php echo $wcmb_moneybird_selected_administration[0]->time_zone; ?></p>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php
                        }
                    ?>
                    <tr valign="top">
                        <th scope="row">
                        </th>
                        <td>
                            <?php
                                echo '<p class="submit">';
                                if(get_option('wcmb_moneybird_client_id')){

                                    $client_id = get_option('wcmb_moneybird_client_id');
                                    $callback = admin_url('admin.php');
                                    $scopes = array("sales_invoices", "documents");
                                    $getAuthorizeUrl = wcmb_authorize_url_create($client_id, $callback, $scopes);

                                    echo '<input type="submit" name="submit" id="wcmb_access_tocken" class="button button-primary" value="'.__('Get Access Tocken', 'wcmb' ).'" disabled="disabled">';
                                    echo '<button id="wcmb_reset" class="button button-primary">'.__( 'Reset', 'wcmb' ).'</button>';

                                } else {
                                    $create_nonce = wp_create_nonce( "wcmb-moneybird-data" );
                                    echo '<input type="hidden" name="wcmb_post_security" id="wcmb_post_security" value="'.$create_nonce.'">';
                                    echo '<input type="submit" name="submit" id="wcmb_access_tocken" class="button button-primary" value="'.__('Get Access Tocken', 'wcmb' ).'">';
                                    echo '<button id="wcmb_reset" class="button button-primary wcmb_resetButtonDisable">'.__( 'Reset', 'wcmb' ).'</button>';
                                }
                                echo '</p>';
                            ?>
                        </td>
                    </tr>

                </tbody>
            </table>
        </form>
    </div>
<?php
}
// authorize URL generate function
function wcmb_authorize_url_create($client_id, $callback, $scopes = array()){
    $pattern = "https://moneybird.com/oauth/authorize?client_id=%s&redirect_uri=%s&scope=%s&response_type=code";
    return sprintf($pattern, $client_id,urlencode($callback),implode("+", $scopes));
}
// access token get function
function wcmb_get_access_code($client_id, $callback, $client_secret, $request_code) {
    $AccesstokenUrl = "https://moneybird.com/oauth/token";
    $getAccessrokenData = wp_remote_post( $AccesstokenUrl, array(
        'method'      => 'POST',
        'timeout'     => 120,
        'redirection' => 5,
        'httpversion' => '1.1',
        'blocking'    => true,
        'headers'     => array(),
        'body'        => [
            'client_id'     => $client_id,
            'redirect_uri'  => $callback,
            'client_secret' => $client_secret,
            'code'          => $request_code,
            'grant_type'    => 'authorization_code'
        ],
        'cookies'     => array()
        )
    );
    if ( is_wp_error( $getAccessrokenData ) ) {
        $error_message = urlencode($getAccessrokenData->get_error_message());
        wp_redirect( admin_url('admin.php?page=moneybird-settings-page') .'&error_description='. $error_message );
        die();
    } else {
        $access_request = json_decode(wp_remote_retrieve_body($getAccessrokenData));
        return $access_request; 
    }
}
//  callback2 URL for  access tocken  
add_action( 'admin_init', 'wcmb_save_data_moneybird_data' );
function wcmb_save_data_moneybird_data() {
    $wcmb_plugin_callback_url = admin_url('admin.php?page=moneybird-settings-page');
    // user click deny in moneybird
    if(isset($_GET['error']) == 'access_denied'){
        $error_message = urlencode('Something rong. Please reset and try again.');
        wp_redirect( $wcmb_plugin_callback_url .'&error_description='. $error_message );
        die();
    }
    if (isset($_GET['code'])) {
        $access_code = $_GET['code'];
        $getClientId = get_option('wcmb_moneybird_client_id');
        $getSecreteId = get_option('wcmb_moneybird_secret_id');
        $access_request = wcmb_get_access_code($getClientId, $wcmb_plugin_callback_url, $getSecreteId, $access_code);
        if($access_request){
            if($access_request->access_token){
                $accessToken = $access_request->access_token;   
                $headers = array(
                    'Content-Type' => 'application/json',
                    'Authorization'=> 'Bearer '.$accessToken
                );
                $administrationsUrl = "https://moneybird.com/api/v2/administrations.json";
                $getAdministraterData = wp_remote_get( $administrationsUrl, array(
                    'timeout'     => 120,
                    'httpversion' => '1.1',
                    'headers'     => $headers,
                    )
                );
                if ( is_wp_error( $getAdministraterData ) ) {
                    $error_message = urlencode($getAdministraterData->get_error_message());
                    wp_redirect( $wcmb_plugin_callback_url .'&error_description='. $error_message );
                    die();
                } else {
                    $administrater = json_decode(wp_remote_retrieve_body($getAdministraterData));
                    if($administrater->error){
                        $error_message = urlencode($administrater->error.". Please Reset And try again");
                        wp_redirect( $wcmb_plugin_callback_url .'&error_description='. $error_message);
                        die();
                    }
                    update_option( 'wcmb_moneybird_selected_administration', $administrater);
                    // save document style id
                    $wcmb_moneybird_selected_administration = get_option( 'wcmb_moneybird_selected_administration' );
                    $administratorId = $wcmb_moneybird_selected_administration[0]->id;
                    $DocumentstyleIdUrl = "https://moneybird.com/api/v2/".$administratorId."/document_styles.json?";
                    $getDocumentstyleIdData = wp_remote_get( $DocumentstyleIdUrl, array(
                        'headers'     => $headers,
                        )
                    );
                    $documentStyleIdData = json_decode(wp_remote_retrieve_body($getDocumentstyleIdData));
                    $getDocumentStyleId = $documentStyleIdData[0]->id;
                    update_option('wcmb_moneybird_document_style_id',$getDocumentStyleId);
                    
                    // save workflow Id
                    $workflowIdURL = "https://moneybird.com/api/v2/".$administratorId."/workflows.json?";
                    $getworkflowIdData = wp_remote_get( $workflowIdURL, array(
                        'headers'     => $headers,
                        )
                    );
                    $workflowIdData = json_decode(wp_remote_retrieve_body($getworkflowIdData));
                    $getworkflowId = $workflowIdData[1]->id;
                    update_option('wcmb_moneybird_workflow_id',$getworkflowId);
                    update_option( 'wcmb_moneybird_access_token', $access_request->access_token);
                    wp_redirect( $wcmb_plugin_callback_url );
                }
                
            } else if( $access_request->error === 'invalid_client'){
                wp_redirect( $wcmb_plugin_callback_url .'&error_description='. urlencode($access_request->error_description) );
                die();
            } else {
                wp_redirect( $wcmb_plugin_callback_url .'&error_description='. urlencode($access_request->error_description) );
                die();
            }
        } else {
            wp_redirect( $wcmb_plugin_callback_url .'&error_description='.urlencode('some issues') );
            die();
        }
    }
}



