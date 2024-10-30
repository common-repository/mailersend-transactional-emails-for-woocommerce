<?php

/**
 * Admin settings page of the plugin
 *
 * @author MailerSend <support@mailersend.com>
 */

$variablesDocsLinks = "https://mailersend.com/help/how-to-integrate-mailersend-with-woocommerce";


$orderEmailsVariables = wc_help_tip("<b>You can use the following variables:<br/>
                                            <p class='unset-fontsize'>
                                                {{customer.name}}<br/>
    
                                                {{order.order_number}}<br/>
                                                {{order.date}}<br/>
                                                {{order.shipping_method}}<br/>
    
                                                {{invoice.discount_total}}<br />
                                                {{invoice.total}}<br/>
    
                                                {{store.url}}<br/>
                                            </p>                                                                        
                                            <a href='" . esc_url( $variablesDocsLinks ) . "' target='_blank'>Click for More</a>"
);

$newAccountEmailVariables = wc_help_tip("<b>You can use the following variables:<br/>
                                                <p class='unset-fontsize'>
                                                    {{user.username}}<br/>
                                                    {{user.name}}<br/>
                                                    {{user.email}}<br/>
    
                                                    {{store.url}}<br/>
                                                </p>                                                                        
                                                <a href='" . esc_url( $variablesDocsLinks ) . "' target='_blank'>Click for More</a>"
);

$resetPasswordEmailVariables = wc_help_tip("<b>You can use the following variables:<br/>
                                                <p class='unset-fontsize'>
                                                    {{user.username}}<br/>
                                                    {{user.name}}<br/>
                                                    {{user.email}}<br/>
                                                    {{user.reset_key}}<br/>
    
                                                    {{store.url}}<br/>
                                                </p>                                                                        
                                                <a href='" . esc_url( $variablesDocsLinks ) . "' target='_blank'>Click for More</a>"
);

if ($successMessage != null) {
    ?>
    <div class='updated'>
        <p><?php echo esc_html( $successMessage ); ?></p>
    </div>
    <?php
}

if ($errorMessage != null) {
    ?>
    <div class='notice notice-error' style="color: red; font-weight: bold;">
        <p><?php echo esc_html ( $errorMessage ); ?></p>
    </div>
    <?php
}
?>
<div id="mailersender-section">
	<h1>MailerSend</h1>
	<div class="mailersender-wrapper">
		<div class="ms-left_content">
            <form method="POST" id="mailersend_data_form">
                <input type="hidden" name="updated" value="true" />
                <?php wp_nonce_field( 'ms-nonce', 'ms-nonce' ); ?>

                <div class="ms-apikey-content">
                    <span class="spinner ms-full-spinner ms-submit-spinner"></span>
                    <h2>Send transactional emails with MailerSend</h2>
                    <p>Connect your MailerSend account with WooCommerce and start sending custom invoices,
                        order updates and account emails without leaving WordPress!</p>

                    <div class="ms-form-field">
                        <div class="row_field">
                            <div class="ms-form_label"><label for="api_key">API Token:</label></div>
                            <div class="ms-form_content-col cnt">
                                <span class="error ms-msg">Your API token is not valid.</span>
                                <div class="ms-form_content">
                                    <div class="input_wrap">
                                        <textarea name="mailersend_api_key" id="mailersend_api_key" cols="100"><?php echo esc_textarea( get_option('mailersend_api_key') ); ?></textarea>
                                        <p>Generate an API token at your MailerSend <a href="https://app.mailersend.com/domains" target="_blank">Domains</a> page and paste it here.</p>
                                    </div>
                                    <input type="button" value="Validate token" id="api_key_validate" class="button primary button-primary">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ms-connect_email-section">
                    <h2>How to get started</h2>
                    <p>Grow your brand by sending automated email templates in 4 simple steps:</p>
                    <ul class="list">
                        <li>Add your <a href="https://www.mailersend.com/help/managing-api-tokens" target="_blank">API token</a> from MailerSend to this plugin and validate it. <strong>Note:</strong> To send emails, tokens from verified domain names must match the sender address.</li>
                        <li>Create <a href="https://www.mailersend.com/help/how-to-create-a-template" target="_blank">custom templates</a> that showcase your brand and add the template ID to each WooCommerce email.</li>
                        <li>Use <a href="https://www.mailersend.com/help/how-to-start-sending-emails#personalization" target="_blank">variables</a> to personalize your templates with recipient names, order numbers, and more.</li>
                        <li>Test your templates to make sure they’re consistent and engaging!</li>
                    </ul>

                    <div class="ms-email_sender">
                        <h2>Email sending options</h2>
                        <p>Customize the name and email address used for sending emails. These will be the default values of your templates.</p>
                        <div class="ms-form-field">
                            <div class="row_field">
                                <div class="ms-form_label"><label for="from_name">Sender name</label></div>
                                <div class="ms-form_content-col cnt">
                                    <span class="error ms-error">Please enter the Sender name</span>
                                    <input name="from_name" id="from_name" type="text" value="<?php echo esc_attr( get_option('mailersend_from_name') ); ?>" class="regular-text" />
                                </div>
                            </div>
                            <div class="row_field">
                                <div class="ms-form_label"><label for="from_address">Sender Address</label></div>
                                <div class="ms-form_content-col cnt">
                                    <span class="error ms-error" id="invalid_email">Sender Address is invalid</span>
                                    <input name="from_address" id="from_address" type="text" value="<?php echo esc_attr( get_option('mailersend_from_address') ); ?>" class="regular-text" />
                                </div>
                            </div>
                            <div class="row_field">
                                <div class="ms-form_label"><label for="cc_address">CC Addresses</label></div>
                                <div class="ms-form_content-col cnt">
                                    <span class="error ms-error" id="invalid_cc">CC Address is invalid</span>
                                    <input name="cc_address" id="cc_address" type="text" value="<?php echo esc_attr( get_option('mailersend_cc_address') ); ?>" class="regular-text" />
                                </div>
                            </div>
                            <div class="row_field">
                                <div class="ms-form_label"><label for="bcc_address">BCC Addresses</label></div>
                                <div class="ms-form_content-col cnt">
                                    <span class="error ms-error" id="invalid_bcc">BCC Address is invalid</span>
                                    <input name="bcc_address" id="bcc_address" type="text" value="<?php echo esc_attr( get_option('mailersend_bcc_address') ); ?>" class="regular-text" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ms-email_noti">
                        <h2>Email notifications</h2><p></p>
                        <p>Get instant email notifications when any of these events happen. If left empty, no email will be sent for that notification.</p>
                        <div class="ms-form-field">
                            <div class="ms-form-field">
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="new_order">New order <?php echo wp_kses( $orderEmailsVariables , true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="new_order_template_id" id="new_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_new_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="new_order"  data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="cancel_order">Cancelled order <?php echo wp_kses( $orderEmailsVariables , true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="cancel_order_template_id" id="cancel_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_cancel_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="cancel_order"data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="failed_order">Failed order <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="failed_order_template_id" id="failed_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_failed_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="failed_order" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="onhold_order">Order on hold <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="onhold_order_template_id" id="onhold_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_onhold_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="onhold_order" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="processing_order">Processing order <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="processing_order_template_id" id="processing_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_processing_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="processing_order" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="completed_order">Completed order <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="completed_order_template_id" id="completed_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_completed_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="completed_order" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="refunded_order">Refunded order <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="refunded_order_template_id" id="refunded_order_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_refunded_order_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="refunded_order" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="customer_invoice">Customer invoice <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="customer_invoice_template_id" id="customer_invoice_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_customer_invoice_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="customer_invoice" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="customer_note">Customer note <?php echo wp_kses( $orderEmailsVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="customer_note_template_id" id="customer_note_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_customer_note_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="customer_note" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="reset_password">Reset password <?php echo wp_kses( $resetPasswordEmailVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="reset_password_template_id" id="reset_password_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_reset_password_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="reset_password" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field">
                                    <div class="ms-form_label">
                                        <label for="new_account">New account <?php echo wp_kses( $newAccountEmailVariables, true ); ?></label>
                                    </div>
                                    <div class="ms-form_content-col cnt">
                                        <span class="error ms-msg"></span>
                                        <div class="ms-form_content">
                                            <div class="template-cnt">
                                                <input name="new_account_template_id" id="new_account_template_id" type="text" value="<?php echo esc_attr( get_option('mailersend_new_account_template_id') ); ?>" class="regular-text template-id" />
                                                <span class="spinner ms-test-spinner"></span>
                                            </div>
                                            <input type="submit" name="new_account" data-mail_action="mailersend_test_mail" class="button mail_test" value="Test">
                                        </div>
                                    </div>
                                </div>
                                <div class="row_field submit">
                                    <div class="ms-form_label">

                                    </div>
                                    <div class="ms-form_content">
                                        <button class="button button-primary mailersend_form_save_button">Save changes</button> <span class="spinner ms-submit-spinner"></span>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
			<div class="ms-danger_zone-wrapper">
	    		<p class="danger">Danger Zone</p>
                <p>Click <strong>Delete information</strong> if you want to uninstall the plugin and remove all crucial data stored in the database. This will remove <strong>all</strong> your settings and deactivate the plugin. Warning! This action can’t be undone.</p>
	    		<input type="button" name="Delete Information" class="button" onclick="if (confirm('Are you sure you want to delete all your MailerSend account information and deactivate the plugin?')) window.location.href='<?php echo esc_url( $danger_zone_link ); ?>';" value="Delete Information">

	    	</div>
            <div class="credit_go">Made by <a href="http://www.mailersend.com" target="_blank">MailerSend</a> Version <?php echo esc_html( MS_WOO_PLUGIN_VERSION ); ?></div>
		</div>
		<div class="ms-right_content">
			<div class="ms-info_wrapper">
				<h2>MailerSend help</h2>
				<div class="ms_infolink">
                    <h3><a href="https://www.mailersend.com/contact-us" target="_blank">Contact 24/7 customer support</a></h3>
					<h3><a href="https://mailersend.com/help/how-to-integrate-mailersend-with-woocommerce">How to send transactional email with WooCommerce</a></h3>
					<h3><a href="https://app.mailersend.com/billing/choose" target="_blank">Get Premium for multiple domains and more!</a></h3>
					<h3><a href="https://www.mailersend.com/about-us" target="_blank">About MailerSend</a></h3>
                    <h3><a href="https://www.capterra.com/p/214665/MailerSend/" target="_blank"><span style="display: inline-block; padding-right: 5px;">❤️</span> this plugin? Give us a 5-star review!</a></h3>
				</div>
			</div>
		</div>
	</div>
</div>