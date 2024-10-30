<?php

/**
*
* Plugin Name: MailerSend - Transactional emails for WooCommerce
* Plugin URI: https://wordpress.org/plugins/mailersend-transactional-emails-for-woocommerce
* Description: Replace WooCommerce’s standard transactional emails with your own custom templates, manage them in MailerSend and send them with our API.
* Version: 1.2.4
* Requires PHP: 7.4 or Latest
* Author: MailerSend
* Author URI: www.mailersend.com
* Developer: MailerSend
* Developer URI: www.mailersend.com
* License: GPLv3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: woocommerce-mailersend
* WC tested up to: 8.9.3
* WC requires at least: 5.1.0
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'MS_WOO_PLUGIN_NAME', 'mailersend-woocommerce' );

define ('MS_WOO_PLUGIN_VERSION', '1.2.4');

define( 'MS_WOO_PLUGIN_NAME_BASE_DIR', plugin_dir_path( __FILE__ ) );

define( 'MS_WOO_PLUGIN_NAME_BASE_NAME', plugin_basename( __FILE__ ) );


/**
 * Plugin deactivation callback. If there's a danger_zone parameter in the url, all plugin settings will be deleted.
 */
function deactivate_mailersend_woocommerce()
{

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mailersend-woocommerce-deactivator.php';
	Mailersend_Woocommerce_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_mailersend_woocommerce' );


/**
 * Runs when the admin_init hook fires
 * Checks that the WooCommerce plugin is installed. Deactivates the plugin if it isn't.
 */
function mailersend_load_plugin()
{
    // check that WooCommerce exists. If it doesn't, deactivate the plugin and add an admin notice
    if (!class_exists('WooCommerce')) {

        add_action('admin_notices', 'mailersend_self_deactivate_notice');

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {

            unset($_GET['activate']);
        }
    }
}
add_action('admin_init', 'mailersend_load_plugin');


/**
 * Display an error message when the WooCommerce plugin is missing
 * Runs depending on the outcome of the load_plugin method above
 */
function mailersend_self_deactivate_notice()
{
    ?>
    <div class="notice notice-error">
       <p> Please install and activate WooCommerce plugin before activating this plugin.</p>
    </div>
    <?php
}

/**
 * Declare HPOS compatibility
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

require plugin_dir_path( __FILE__ ) . 'includes/class-mailersend-woocommerce.php';

// instantiate the main plugin object
global $pbt_prefix_mailersend_woocommerce;
$pbt_prefix_mailersend_woocommerce = new Mailersend_Woocommerce();
$pbt_prefix_mailersend_woocommerce->run();