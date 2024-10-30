<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @author MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce_Admin {

    /**
    * The ID of this plugin.
    */
    private $mailersend_woocommerce;

    /**
    * The version of this plugin.
    */
    private $version;

    /**
    * Store plugin main class to allow public access.
    */
    public $main;


    /**
     * Initializes the class and set its properties.
     * @param string $mailersend_woocommerce The name of this plugin.
     * @param string $version The version of this plugin.
     * @param object $plugin_main The reference to Mailersend_Woocommerce
     */
    public function __construct( $mailersend_woocommerce, $version, $plugin_main )
    {

        $this->mailersend_woocommerce = $mailersend_woocommerce;
        $this->version = $version;
        $this->main = $plugin_main;
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }


    /**
     * Sets up the docs and supports links that are shown in the plugins page
     * @param $links
     * @param $file
     * @return array
     */
    public static function plugin_row_meta( $links, $file )
    {

        if ( strpos($file, MS_WOO_PLUGIN_NAME_BASE_NAME) !== false) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url(apply_filters('mailersend_woocommerce_docs_url',
                        'https://www.mailersend.com/help')) . '" aria-label="' . esc_attr__('View Mailersend WooCommerce documentation',
                        'mailersend_woocommerce') . '">' . esc_html__('Docs', 'mailersend_woocommerce') . '</a>',
                'support' => '<a href="' . esc_url(apply_filters('mailersend_woocommerce_support_url',
                        'https://www.mailersend.com/contact-us')) . '" aria-label="' . esc_attr__('Visit community forums',
                        'mailersend_woocommerce') . '">' . esc_html__('Support', 'mailersend_woocommerce') . '</a>',
            );

            $links = array_merge( $links, $row_meta );
        }

        return $links;
    }


    /**
     * Sets up the links that are displayed in the plugins page, under the plugin name.
     * @param $links
     * @return array
     */
    public function add_action_links( $links )
    {
        $settings_link = array(
            '<a href="' . admin_url( 'admin.php?page=' . MS_WOO_PLUGIN_NAME ) . '">' . __( 'Settings' ) . '</a>',
        );
        return array_merge( $settings_link, $links );
    }


    /**
    * Register the stylesheets for the admin area.
    */
    public function enqueue_styles()
    {

        wp_enqueue_style( $this->mailersend_woocommerce, plugin_dir_url( __FILE__ ) . 'css/mailersend-woocommerce-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'Mailersend_WC_admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
    }


    /**
    * Register the JavaScript for the admin area.
    */
    public function enqueue_scripts()
    {

        // load the WooCommerce admin script the same way WooCommerce does
        wp_register_script( 'woocommerce_admin',  plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), '1.0' );

        $locale = localeconv();
        $decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
        $params = array(
            /* translators: %s: decimal */
            'i18n_decimal_error' => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
            /* translators: %s: price decimal separator */
            'i18n_mon_decimal_error' => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), 'a' ),
            'i18n_country_iso_error' => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
            'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
            'decimal_point' => $decimal,
            'mon_decimal_point' => ',',
            'strings' => array(
                'import_products' => __( 'Import', 'woocommerce' ),
                'export_products' => __( 'Export', 'woocommerce' ),
            ),
            'urls' => array(
                'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
                'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
            ),
        );

        wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
        wp_enqueue_script( 'woocommerce_admin' );

        // load the plugin's admin script
        wp_enqueue_script( $this->mailersend_woocommerce, plugin_dir_url( __FILE__ ) . 'js/mailersend-woocommerce-admin.js', array( 'jquery' ), $this->version, false );
    }


    /**
     * Adds the submenu item under the WooCommerce menu
     * The submenu item runs displayPluginAdminSettings on click
     */
    public function add_plugin_admin_menu()
    {

        // Add woocommerce menu subitem
        add_submenu_page(
            'woocommerce',
            __( 'MailerSend WooCommerce', 'mailersend-woocommerce' ),
            __( 'MailerSend', 'mailersend-woocommerce' ),
            'administrator',
            $this->mailersend_woocommerce,
            array( $this, 'displayPluginAdminSettings' )
        );
    }


    /**
     * Is called when the user presses Save settings in the plugin admin settings page
     * Saves the settings to the database and verifies the email domain.
     * @return array
     */
    private function handle_form()
    {

        if ( ! isset( $_POST['ms-nonce'] ) || ! wp_verify_nonce( $_POST['ms-nonce'], 'ms-nonce' ) ) {

            return [
              'success' => false,
              'message' => 'Sorry your nonce was not correct. Please try again.'

            ];

        } else {

            // When execution reaches here it means that the API token is valid and there is a template id for each email

            $fromAddress = sanitize_email( $_POST['from_address'] );

            $ccAddress =  sanitize_text_field( $_POST['cc_address'] );

            if ( ! empty( $ccAddress ) ) {

                foreach ( explode( ',', $ccAddress ) as $cc_email ) {

                    if ( ! filter_var( trim( $cc_email ), FILTER_VALIDATE_EMAIL ) ) {

                        return [
                            'success' => false,
                            'message' => 'Sorry your CC email address was not valid. Please try again.'

                        ];
                    }
                }
            }

            $bccAddress = sanitize_text_field( $_POST['bcc_address'] );

            if ( ! empty( $bccAddress ) ) {

                foreach ( explode( ',', $bccAddress ) as $bcc_email ) {

                    if ( ! filter_var( trim( $bcc_email ), FILTER_VALIDATE_EMAIL ) ) {

                        return [
                            'success' => false,
                            'message' => 'Sorry your BCC email address was not valid. Please try again.'

                        ];
                    }
                }
            }

            update_option( 'mailersend_api_key', sanitize_text_field( $_POST['mailersend_api_key'] ));
            update_option( 'mailersend_from_name',  sanitize_text_field( $_POST['from_name'] ));
            update_option( 'mailersend_from_address', $fromAddress);
            update_option( 'mailersend_cc_address', $ccAddress);
            update_option( 'mailersend_bcc_address', $bccAddress);
            update_option( 'mailersend_new_order_template_id', sanitize_text_field( $_POST['new_order_template_id'] ));
            update_option( 'mailersend_cancel_order_template_id', sanitize_text_field( $_POST['cancel_order_template_id'] ));
            update_option( 'mailersend_failed_order_template_id', sanitize_text_field( $_POST['failed_order_template_id'] ));
            update_option( 'mailersend_onhold_order_template_id', sanitize_text_field( $_POST['onhold_order_template_id'] ));
            update_option( 'mailersend_processing_order_template_id', sanitize_text_field( $_POST['processing_order_template_id'] ));
            update_option( 'mailersend_completed_order_template_id', sanitize_text_field( $_POST['completed_order_template_id'] ));
            update_option( 'mailersend_refunded_order_template_id', sanitize_text_field( $_POST['refunded_order_template_id'] ));
            update_option( 'mailersend_customer_invoice_template_id', sanitize_text_field( $_POST['customer_invoice_template_id'] ));
            update_option( 'mailersend_customer_note_template_id', sanitize_text_field( $_POST['customer_note_template_id'] ));
            update_option( 'mailersend_reset_password_template_id', sanitize_text_field( $_POST['reset_password_template_id'] ));
            update_option( 'mailersend_new_account_template_id', sanitize_text_field( $_POST['new_account_template_id'] ));

            // verify the from address
            if (!$this->isDomainVerified($fromAddress)) {

                return [
                    'success' => false,
                    'message' => 'Your email domain is not verified on MailerSend.'

                ];
            }

            return [
                'success' => true,
                'message' => 'Your settings were saved!'

            ];
        }

    }


    /**
     * Calls the MailerSend API to check if the domain of the given email is verified.
     * @param $senderEmail
     * @return bool
     */
    private function isDomainVerified($senderEmail)
    {

        // get the domain part of the email
        $emailParts = explode('@', $senderEmail);

        $domain = null;

        if (count($emailParts) == 2) {

            $domain = $emailParts[1];
        } else {

            return false;
        }

        $msApi = Mailersend_Woocommerce_Api::getInstance();

        $endpoint = '/domains?limit=100';

        $currentPage = 1;

        $domainVerified = false;

        $counter = 100; // simple way to avoid an infinite loop that keeps calling the api
        while($counter > 0) {

            $counter--;

            $domainsBatch = $msApi->getRequest($endpoint . '&page=' . $currentPage);

            if ($domainsBatch) {

                $batchObj = json_decode($domainsBatch, true);

                if (isset($batchObj['data'])) {
                    foreach ($batchObj['data'] as $d) {

                        if ($d['name'] == $domain) {

                            if ($d['is_verified']) {

                                $domainVerified = true;
                            }

                            return $domainVerified;
                        }
                    }

                    $currentPage++;
                    if ($currentPage > $batchObj['meta']['last_page']) {

                        return false;
                    }
                } else {

                    return false;
                }
            } else {

                return false;
            }
        }

        return false;
    }


    /**
     * Generates and returns the link to delete the data and deactivate the plugin
     * @param $plugin
     * @param string $action
     * @return string
     */
    public function generateDataDeletionLink( $plugin, $action = 'deactivate' )
    {
        if ( strpos( $plugin, '/' ) ) {

            $plugin = str_replace( '\/', '%2F', $plugin );
        }

        $url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&danger_zone=1&paged=1&s' ), $plugin );

        $url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );

        return $url;
    }


    /**
     * Displays the plugin admin settings page
     */
    public function displayPluginAdminSettings()
    {

        $successMessage = null;
        $errorMessage = null;

        if ( isset( $_POST['updated'] ) ) {

            $result = $this->handle_form();

            if ($result['success']) {

                $successMessage = $result['message'];
            } else {

                $errorMessage = $result['message'];
            }
        }

        $danger_zone_link = $this->generateDataDeletionLink( MS_WOO_PLUGIN_NAME_BASE_NAME );
        require_once 'views/'.$this->mailersend_woocommerce.'-admin-settings-display.php';
    }
}