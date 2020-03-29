<?php
		// moneybird post data security
		add_action('init','wcmb_moneybird_moneybird_data_security');
		function wcmb_moneybird_moneybird_data_security(){
			if(isset($_POST['wcmb_save_general_setting'])){
				$wcmb_nonce = $_POST['wcmb_post_security'];
			    if ( ! wp_verify_nonce( $wcmb_nonce, 'wcmb-moneybird-data' ) ) {
				    die( __( 'Security check', 'wcmb' ) ); 
				} else {
					$invoice_layout_id = sanitize_text_field($_POST['wcmb_invoice_layout']);
					$workflow_id = sanitize_text_field($_POST['wcmb_workflow']);
					update_option('wcmb_moneybird_document_style_id',$invoice_layout_id);
					update_option('wcmb_moneybird_workflow_id',$workflow_id);
				}
			}
		}
		function wcmb_moneybird_api_general_callback(){
		    ?>
		    <div class="wrap">
		    	<h1><?php echo __('MoneyBird General Settings', 'wcmb' ); ?></h1>
		    	<?php
            		if(get_option('wcmb_moneybird_access_token') == ""){
            			$MoneybirdApiSetting = admin_url('admin.php?page=moneybird-settings-page');
            			?>
            			<div class="error">
			                <p><strong>ERROR :</strong><?php echo __('Please Get Access token Process complite.<br />Go to Moneybird API setting', 'wcmb' );?> :  <a href="<?php echo $MoneybirdApiSetting;?>">Moneybird API Setting</a></p>
			            </div>
            			<?php
            		} else {
        			?>	
        				<form method="post" id="wcmb_mb_General_setting_form">
				            <table class="form-table">
				                <tbody>
				                    <tr valign="top">
				                        <th scope="row">
				                            <label><?php echo __('Moneybird Document Style', 'wcmb' ); ?></label>
				                        </th>
				                        <td>
				                        	<?php
				                        		$access_token = get_option('wcmb_moneybird_access_token'); 
				                        		$headers = array(
								                    'Content-Type' => 'application/json',
								                    'Authorization' => 'Bearer ' . $access_token,
								                );
										        // administration_id Get using 
										        $wcmb_moneybird_selected_administration = get_option( 'wcmb_moneybird_selected_administration' );
										        $administratorId = $wcmb_moneybird_selected_administration[0]->id;
										       	// invoice latout get
										       	$DocumentstyleUrl = "https://moneybird.com/api/v2/".$administratorId."/document_styles.json?";
								                $getDocumentstyleData = wp_remote_get( $DocumentstyleUrl, array(
								                    'headers'     => $headers,
								                    )
								                );
								                $documentStyleData = json_decode(wp_remote_retrieve_body($getDocumentstyleData));
								                $get_invoice_id = get_option('wcmb_moneybird_document_style_id');
				                            	echo '<select name="wcmb_invoice_layout" class="wcmb_invoiceLayoutWidth" id="wcmb_invoice_layout" >';
										        foreach ($documentStyleData as $key => $value) {
				                            		$selected = ($get_invoice_id === $value->id)? "selected" : "";
													echo '<option  value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
										        }
												echo '</select>';
												?>
				                        </td>
				                    </tr>
				                    <tr valign="top">
				                        <th scope="row">
				                            <label><?php echo __('Moneybird Workflow', 'wcmb' ); ?></label>
				                        </th>
				                        <td>
				                        	<?php
										        // workflow id get
				                        		$workflowIdURL = "https://moneybird.com/api/v2/".$administratorId."/workflows.json?";
								                $getworkflowIdData = wp_remote_get( $workflowIdURL, array(
								                    'headers'     => $headers,
								                    )
								                );
								                $workflowIdData = json_decode(wp_remote_retrieve_body($getworkflowIdData));
								                $get_workflow_id = get_option('wcmb_moneybird_workflow_id');
								                
				                            	echo '<select name="wcmb_workflow" class="wcmb_workflowWidth" id="wcmb_workflow">';
										        foreach ($workflowIdData as $key => $value) {
									        	   	if($value->type === 'InvoiceWorkflow'){
					                            		$selected=($get_workflow_id === $value->id)? "selected" : "";
														echo '<option  value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
									        	   	}
										        }
												echo '</select>';
												?>
				                        </td>
				                    </tr>
				                    <tr valign="top">
				                        <th scope="row">
				                        </th>
				                        <td>
				                        	<input type="hidden" name="wcmb_post_security" value="<?php echo wp_create_nonce( 'wcmb-moneybird-data' );?>">
				                            <p class="submit"><input type="submit" name="wcmb_save_general_setting" id="wcmb_save_invoice_layout" class="button button-primary" value="Save Changes"></p>                        
				                        </td>
			                    	</tr>
				                </tbody>
				            </table>
				        </form>
        			<?php
            		}
            	?>
		    </div>
		    <?php
		}
?>