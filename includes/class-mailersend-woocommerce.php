<?php

/**
 * MailerSend WooCommerce plugin main class
 *
 * @author MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce {

	/**
	 * @var Mailersend_Woocommerce_Loader $loader Keeps a list of all the hooks needed by the plugin and registers them all at once.
	 */
	protected $loader;

	/**
	 * @var string $mailersend_woocommerce The string used to uniquely identify this plugin.
	 */
	protected $mailersend_woocommerce;

	/**
	 * @var object The plugin's admin object.
	 */
	public $admin;

	/**
	 * @var object The plugin's frontend object.
	 */
	public $public;
	
	public $main;

	/**
     * Sets the plugin up
     * Initializes the plugin and deactivates the default WooCommerce emails
	 */
	public function __construct() {

		$this->mailersend_woocommerce = 'mailersend-woocommerce';

		$this->main = $this;

		$this->load_dependencies();
        $this->loader = new Mailersend_Woocommerce_Loader();

		$this->set_locale();
		$this->prepare_admin();

		$mailersend_actions = array (
		    "customer_new_account",
            "new_order",
            "cancelled_order",
            "failed_order",
            "customer_on_hold_order",
            "customer_processing_order",
            "customer_completed_order",
            "customer_refunded_order",
            "customer_partially_refunded_order",
            "customer_invoice",
            "customer_note",
            "customer_reset_password",
            "customer_new_account"
        );

		// Add a hook with the highest priority for each of WooCommerce's checks so that if an email is enabled
        // redirect its hooks to a method that returns false
		foreach($mailersend_actions as $action_id)
		{
			add_filter( 'woocommerce_email_enabled_'.$action_id, array( $this, 'filter_woocommerce_disabled_default_emails') , 10, 3 );
		}

		// add an additional hook to kill the default emails by emptying the recipient.
   		add_filter( 'woocommerce_email_recipient_customer_invoice', array ($this, 'woocommerce_email_customer_invoice_add_recipients' ), 10, 2);

		// remove all default WooCommerce notification hooks and add new ones for the plugin
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email_hooks' ));


		add_action("wp_ajax_check_api_key" , array( $this,"validate_api_key"));
		add_action("wp_ajax_mailersend_test_mail" , array( $this,"mailersend_test_mail"));
	}


    /**
     * Empty
     * @param $recipient
     * @param $order
     * @return string
     */
    function woocommerce_email_customer_invoice_add_recipients( $recipient, $order )
    {

		return '';
	}

    /**
     * Is called when the woocommerce_email_enabled_* hook fires and returns false
     * @param $enabled
     * @param $user
     * @param $email
     * @return false
     */
	function filter_woocommerce_disabled_default_emails( $enabled, $user, $email )
    {

		return false;
	}


    /**
     * Returns the email of the logged in administrator user or null if the user isn't an admin
     * @return mixed
     */
	public function get_logged_in_admin()
	{
		$current_user = wp_get_current_user();
		if($current_user->ID){

			$user = new WP_User( $current_user->ID );

			if ( ! empty( $user->roles ) && is_array( $user->roles ) && in_array( 'administrator', $user->roles )
                && current_user_can('administrator') ) {

			    return $user->data->user_email;
			}
		}

		return null;
	}


    /**
     * Gets the mail details (subject and recipient) when sending an email to the admin when something happened (e.g. new order)
     * @param string $type
     * @return array|mixed|null
     */
	public function get_mail_details($type="")
	{

		$wc_emails = WC()->mailer();
		// Get available emails notifications
		$emails_array = $wc_emails->get_emails();
		$email_details=array();

		if (isset(Mailersend_Woocommerce_Helper::$msEmailMapping[$type])) {

		    $order_email = $emails_array[ Mailersend_Woocommerce_Helper::$msEmailMapping[ $type ]['wcEmail']];
            $email_details['recipient'] = $this->get_logged_in_admin();
            $email_details['subject'] = $order_email->get_option( 'subject', $order_email->get_default_subject() );
            return $email_details;
        }

        return $order_recipient = $this->get_logged_in_admin();
	}


    /**
     * Is called when the user presses the Test button on one of the templates
     */
	public function mailersend_test_mail()
	{
	    wp_reset_postdata();

	    $emailType = sanitize_text_field( $_POST['mail_type'] );

	    $templateId = sanitize_text_field( $_POST['template_id'] );

	    $emailDetailsData = $this->get_mail_details($emailType);

        $wc_emails = WC()->mailer();

        $emailsArray = $wc_emails->get_emails();
        $mailersendCall = $emailsArray['Mailersend_Woocommerce_Send'];

        $testEmailRecipient = $this->get_logged_in_admin();

	    if ($emailType == 'reset_password' || $emailType == 'new_account') {

	        $response = $mailersendCall->sendUserEmail(get_current_user_id(), 'reset_key', $emailType, ['email' => $testEmailRecipient, 'name' => $testEmailRecipient], $templateId);

            echo json_encode($response);
	        exit;
        }

        // get the latest order
        $latestOrder = wc_get_orders([
           'type' => 'shop_order',
           'status' => 'any',
           'limit' => 1
        ]);

        if (!empty($latestOrder)) {

            $latestOrder = $latestOrder[0];
        } else {

            echo json_encode([
               'success' => false,
               'response' => 'Please make sure you have a placed order to test the template.'
            ]);

            exit;
        }

        $orderId = $latestOrder->get_id();

        $order = wc_get_order($orderId);

        $response = $mailersendCall->sendOrderEmail($order, $emailType, ['email' => $testEmailRecipient, 'name' => $testEmailRecipient], $templateId, "sample note to customer");

        echo json_encode($response);

        // we exit here instead of returning because the request is handled by admin-ajax and if we let it run
        // it ends by writing a 0 in the output which will mess with the json parse of the response
        exit;
	}


    /**
     * Is called when the user clicks the Validate key button in the admin settings
     */
	public function validate_api_key()
    {
			$api_key = sanitize_text_field( $_POST["api_key"] );
            update_option('mailersend_api_key', $api_key);

            $msApi = Mailersend_Woocommerce_Api::getInstance();

            $response = $msApi->getRequest('/domains');

            $responseArray = [
                'validated' => $msApi->responseCode == 200,
                'response' => json_decode($response)
            ];

            if ($msApi->responseCode != 200) {

                $responseArray['error'] = esc_html($msApi->requestError);
                $responseArray['code'] = intval( $msApi->responseCode );
            }

            echo json_encode($responseArray);

            exit;
    }


    /**
     * Removes the WooCommerce default email hooks and adds the MailerSend hooks
     * @param $emails
     * @return mixed
     */
	public function register_email_hooks( $emails )
    {

	    // remove all default WC hooks
        $wcEmailHooks = Mailersend_Woocommerce_Helper::$wcMsEmailsMapping;

        foreach ($wcEmailHooks as $hook) {

            // since we need to pass the exact priority that was used when the hook was created, we need to read the priority first
            $priority = has_action($hook['hook'], array(  $emails[ $hook['wcEmail'] ], 'trigger'));
            remove_action($hook['hook'], array( $emails[ $hook['wcEmail'] ], 'trigger'), $priority);
        }


        // it is very important that this is included here and not loaded with the other dependencies
        // that's because when execution reaches here, the WooCommerce email subsystem would be loaded and accessible
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailersend-woocommerce-send.php';

		$emails['Mailersend_Woocommerce_Send'] = new Mailersend_Woocommerce_Send();

		return $emails;
	}


	/**
	 * Loads the required dependencies for this plugin.
	 */
	private function load_dependencies()
    {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailersend-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailersend-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mailersend-woocommerce-admin.php';

		/**
		 * Class that contains various definitions and helper methods
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailersend-woocommerce-helper.php';

        /**
         * Class that contains simple methods to access the MailerSend API
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailersend-woocommerce-api.php';

	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mailersend_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale()
    {
		$plugin_i18n = new Mailersend_Woocommerce_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}


	/**
	 * Create the admin object and register the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function prepare_admin()
    {
		$this->admin = new Mailersend_Woocommerce_Admin( $this->get_mailersend_woocommerce(), MS_WOO_PLUGIN_VERSION, $this->main );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $this->admin, 'add_plugin_admin_menu', 71 );

		if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')))) {

            $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . MS_WOO_PLUGIN_NAME . '.php');
            $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $this->admin, 'add_action_links');
		}
	
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run()
    {
		$this->loader->run();
	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_mailersend_woocommerce() {
		return $this->mailersend_woocommerce;
	}
}