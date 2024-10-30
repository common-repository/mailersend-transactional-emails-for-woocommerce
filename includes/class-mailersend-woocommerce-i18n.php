<?php

/**
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @author MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mailersend-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
