<?php

/**
 * Fires during plugin deactivation.
 *
 * @author MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce_Deactivator {

	/**
	 * Is called during plugin deactivation.
     * If there's a danger_zone parameter in the url, it removes all plugin settings
	 */
	public static function deactivate() {

	    // if the user got here by clicking the Delete Information button, we need to remove all of the plugin settings
		if(isset($_GET['danger_zone']))
		{
		    $settings = [
                'mailersend_api_key',
                'mailersend_from_name',
                'mailersend_from_address',
                'mailersend_cc_address',
                'mailersend_bcc_address',
                'mailersend_new_order_template_id',
                'mailersend_cancel_order_template_id',
                'mailersend_failed_order_template_id',
                'mailersend_onhold_order_template_id',
                'mailersend_processing_order_template_id',
                'mailersend_completed_order_template_id',
                'mailersend_refunded_order_template_id',
                'mailersend_customer_invoice_template_id',
                'mailersend_customer_note_template_id',
                'mailersend_reset_password_template_id',
                'mailersend_new_account_template_id',
            ];

            // Remove each setting
            foreach ( $settings as $setting ) {

                delete_option( $setting );
            }
		}
		
        flush_rewrite_rules();
	}
}
