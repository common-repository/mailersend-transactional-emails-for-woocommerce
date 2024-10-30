<?php

/**
 * Implements email preparation and sending functionality
 *
 * @author MailerSend <support@mailersend.com>
 */

if ( class_exists( 'WC_Email' ) ) :

class Mailersend_Woocommerce_Send extends WC_Email {

    /**
     * On init, it registers all supported email hooks
     */
    public function __construct() {

        // register all of our email hooks
        $emailHooks = Mailersend_Woocommerce_Helper::$wcMsEmailsMapping;

        $instance = $this;

        foreach ($emailHooks as $hook) {

            // assume a default priority of 10 for all of our hooks
            add_action( $hook['hook'], function($param1, $param2 = "") use ($hook, $instance) {

                $instance->handleEmailSend($hook, $param1, $param2);
            }, 10, $hook['args']);
        }

        parent::__construct();
    }


    /**
     * Hanldes the woocommerce email hooks triggers. Calls sendOrderEmail or sendUserEmail depending on the hook type
     * @param array $emailMapEntry The entry to Mailersend_Woocommerce_Helper::$wcMsEmailsMapping that contains the email types
     * @param mixed $param1 Contains the order or user id depending on the hook type
     * @param mixed $param2 Contains either the order object, user password reset key, new user information or the refunded order id depending on the hook type
     */
    public function handleEmailSend($emailMapEntry, $param1, $param2)
    {

        // this method is called during the checkout.
        // it shouldn't throw any errors or write anything to the output.

        // find the email info to get the template id and variables to pass
        $emailInfo = null;
        $emailType = null;

        foreach (Mailersend_Woocommerce_Helper::$msEmailMapping as $type => $mailInfo) {

            if ($mailInfo['wcEmail'] == $emailMapEntry['wcEmail']) {

                $emailType = $type;
                $emailInfo = $mailInfo;
                break;
            }
        }

        // validate that we have the email information and the template
        if ($emailInfo == null || empty(sanitize_text_field( get_option($emailInfo['optionName'] )))) {

            // get action id
            $action = strtolower(str_replace('WC_Email_', '', $emailMapEntry['wcEmail']));

            // re-enable action
            add_filter( 'woocommerce_email_enabled_' . $action, '__return_true' );

            // send the email through the native mailer
            WC()->mailer()->emails[$emailMapEntry['wcEmail']]->trigger( $param1, $param2, true );

            // not much to do, just return
            return;
        }

        // handle the user specific emails here and return without running the rest
        if ($emailType == 'reset_password' || $emailType == 'new_account') {

            $this->sendUserEmail($param1, $param2, $emailType);
            return;
        }

        $orderId = $param1;
        $refundedOrderId = null;
        $order = null;

        if ($emailType != 'refunded_order') {

            $order = $param2;
        }

        $noteToCustomer = null;

        // for customer notes, $param1 will contain an array with the order id and customer note
        if ($emailType == 'customer_note') {

            $orderId = $param1['order_id'];
            $order = wc_get_order( $orderId );
            $noteToCustomer = $param1['customer_note'];
        }

        if ( $orderId && ! is_a( $order, 'WC_Order' ) ) {

            $order = wc_get_order( $orderId );
        }

        $this->sendOrderEmail($order, $emailType, null, null, $noteToCustomer);
    }


    /**
     * Sends an order status email
     * @param object $order The order object
     * @param string $emailType The email type to send
     * @param mixed $recipient The email of the recipient. If null, the email will be sent to the order billing email
     * @param string|null $forceTemplateId The template id to use. If null, the template stored in wp_options for the given email type is used. If it is empty, no email is sent
     * @param string|null $noteToCustomer Is only used when the email type is customer note
     * @return array Returns an array containing the outcome and any error messages
     */
    public function sendOrderEmail($order, $emailType, $recipient = null, $forceTemplateId = null, $noteToCustomer = null)
    {

        $emailInfo = Mailersend_Woocommerce_Helper::$msEmailMapping[$emailType];

        $emailObject = new $emailInfo['wcEmail']();
        $subject = $emailObject->get_default_subject();

        $templateId = null;

        if ($forceTemplateId != null) {

            $templateId = $forceTemplateId;
        } else {

            $templateId = sanitize_text_field( get_option($emailInfo['optionName']) );
        }

        // if there's no template id just return a fake success response
        // since we will not send an email.
        if (!$templateId) {

            return [
                'success' => true,
                'status' => 200,
                'response' => ''
            ];
        }

        $fromAddress = [
            'email' => sanitize_text_field( get_option('mailersend_from_address') ),
            'name' => sanitize_text_field( get_option('mailersend_from_name') )
        ];

        $toAddress = [];

        if ($recipient != null) {

            $toAddress = [

                'email' => $recipient['email'],
                'name' => $recipient['name']
            ];
        } else {

            // some emails go to the admin and not to the customers
            // for those emails get_recipient() will return a value and customer_email will be true
            $defaultRecipient = $emailObject->get_recipient();

            if ($defaultRecipient && !$emailObject->customer_email) {

                $toAddress = [

                    'email' => $defaultRecipient,
                    'name' => $emailObject->get_from_name()
                ];
            } else {

                $toAddress = [

                    'email' => $order->get_billing_email(),
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                ];
            }
        }

        $requestData = [
            'to' => [ $toAddress ],
            'template_id' => $templateId,
            'subject' => $subject
        ];

        // check if cc addresses was entered and set as parameters for the api request
        $ccAddress = sanitize_text_field( get_option('mailersend_cc_address') );

        $cc = [];

        if ( ! empty( $ccAddress ) ) {

            foreach ( explode( ',', $ccAddress ) as $cc_email ) {

                if ( filter_var( trim( $cc_email ), FILTER_VALIDATE_EMAIL ) ) {

                    $cc[] = [
                        'email' => $cc_email
                    ];
                }
            }
        }

        if ( count($cc) > 0 ) {

            $requestData['cc'] = $cc;
        }

        // check if bcc addresses was entered and set as parameters for the api request
        $bccAddress = sanitize_text_field( get_option('mailersend_bcc_address') );

        $bcc = [];

        if ( ! empty( $bccAddress ) ) {

            foreach ( explode( ',', $bccAddress ) as $bcc_email ) {

                if ( filter_var( trim( $bcc_email ), FILTER_VALIDATE_EMAIL ) ) {

                    $bcc[] = [
                        'email' => $bcc_email
                    ];
                }
            }
        }

        if ( count($bcc) > 0 ) {

            $requestData['bcc'] = $bcc;
        }

        // if the user hasn't saved the from address, we will not pass it to the MailerSend API
        // and the template's from information would be used instead
        if (!empty($fromAddress['email'])) {

            $requestData['from'] = $fromAddress;
        }

        $personalizationData = [];

        if ($noteToCustomer != null) {

            $personalizationData['note_to_customer'] = $noteToCustomer;
        }

        if ($emailInfo['orderData']) {

            $personalizationData = array_merge($personalizationData, $this->getGenericVariables($order->get_id(), $emailType));

            // if there are no items in the order do not send the email
            // this might happen in cases where woocommerce fires the webhook before it has saved the order data
            // (e.g. on new orders with account creation, the first new_order trigger wil not have any items but the second will have)
            if (empty($personalizationData['items'])) {

                return [];
            }
        }

        if ($emailInfo['userData']) {

            $personalizationData = array_merge($personalizationData, $this->getUserVariables($order->get_user_id()));
        }

        $personalizationData = array_merge($personalizationData, $this->getStoreVariables());

        // replace the supported woocommerce variables in the subject
        $mainWooVariables = [
            'site_title' => $personalizationData['store']['name'],
            'site_url' => $personalizationData['store']['url'],
            'order_date' => $personalizationData['order']['date'],
            'order_number' => $personalizationData['order']['order_number']
        ];

        foreach ($mainWooVariables as $wooVar => $val) {

            $requestData['subject'] = str_replace('{' . $wooVar . '}', $val, $requestData['subject']);
        }


        $requestData['personalization'] = [];
        $requestData['personalization'][] = [
          'email' => $toAddress['email'],
          'data' => $personalizationData
        ];

        $mailersendApi = Mailersend_Woocommerce_Api::getInstance();
        $response = $mailersendApi->postRequest('/email', $requestData);

        // check for validation errors
        if ($mailersendApi->responseCode == 422) {

            $responseMessage = '';

            $respObj = json_decode($response, true);

            if (isset($respObj['errors']['from.email'])) { // domain verification check

                // domain is not verified
                $responseMessage = 'Your email domain is not verified.';

            } else if (isset($respObj['errors']['template_id'])) { // template id check

                $responseMessage = 'The template id is not correct.';

            } else {

                // generic validation message
                $responseMessage = 'The sent data could not be validated. Please try again or contact us.';
            }

            return [

              'success' => false,
              'status' => 422,
              'response' => esc_html( $responseMessage )
            ];
        }

        if ($mailersendApi->requestError) {

            return [
                'success' => false,
                'status' => intval( $mailersendApi->responseCode ),
                'error' => esc_html( $response )
            ];
        } else {

            return [
                'success' => true,
                'status' => intval( $mailersendApi->responseCode ),
                'response' => esc_html( $response )
            ];
        }
    }


    /**
     * Sends a user notification email.
     * @param int $userLogin The username that the email refers to
     * @param string|array $param2 Either contains the reset password key or the new user information depending on the email type
     * @param string $emailType The type of email to send
     * @param string|null $recipient The email of the recipient. If null, the email will be sent to the order billing email
     * @param string|null $forceTemplateId The template id to use. If null, the template stored in wp_options for the given email type is used. If it is empty, no email is sent
     * @return array Returns an array containing the outcome and any error messages
     */
    public function sendUserEmail($userLogin, $param2, $emailType, $recipient = null, $forceTemplateId = null)
    {

        // if this is a new customer user email, $param2 will contain an array with the new user data
        // if its a reset password email, $param2 will contain the reset key

        $user = new WP_User($userLogin);

        if (!$user) {

            return [
                'success' => false,
                'response' => 'User not found'
            ];
        }

        $emailInfo = Mailersend_Woocommerce_Helper::$msEmailMapping[$emailType];

        $emailObject = new $emailInfo['wcEmail']();
        $subject = $emailObject->get_default_subject();

        $templateId = null;

        if ($forceTemplateId != null) {

            $templateId = $forceTemplateId;
        } else {

            $templateId = sanitize_text_field( get_option($emailInfo['optionName']) );
        }

        $fromAddress = [
            'email' => sanitize_text_field( get_option('mailersend_from_address') ),
            'name' => sanitize_text_field( get_option('mailersend_from_name') )
        ];

        $toAddress = [];

        if ($recipient != null) {

            $toAddress = [

                'email' => $recipient['email'],
                'name' => $recipient['name']
            ];
        } else {

            $toAddress = [

                'email' => $user->user_email,
                'name' => $user->display_name
            ];
        }

        $requestData = [
            'to' => [ $toAddress ],
            'template_id' => $templateId,
            'subject' => $subject
        ];

        // check if cc addresses was entered and set as parameters for the api request
        $ccAddress = sanitize_text_field( get_option('mailersend_cc_address') );

        $cc = [];

        if ( ! empty( $ccAddress ) ) {

            foreach ( explode( ',', $ccAddress ) as $cc_email ) {

                if ( filter_var( trim( $cc_email ), FILTER_VALIDATE_EMAIL ) ) {

                    $cc[] = [
                        'email' => $cc_email
                    ];
                }
            }
        }

        if ( count($cc) > 0 ) {

            $requestData['cc'] = $cc;
        }

        // check if bcc addresses was entered and set as parameters for the api request
        $bccAddress = sanitize_text_field( get_option('mailersend_bcc_address') );

        $bcc = [];

        if ( ! empty( $bccAddress ) ) {

            foreach ( explode( ',', $bccAddress ) as $bcc_email ) {

                if ( filter_var( trim( $bcc_email ), FILTER_VALIDATE_EMAIL ) ) {

                    $bcc[] = [
                        'email' => $bcc_email
                    ];
                }
            }
        }

        if ( count($bcc) > 0 ) {

            $requestData['bcc'] = $bcc;
        }

        // if the user hasn't saved the from address, we will not pass it to the MailerSend API
        // and the template's from information would be used instead
        if (!empty($fromAddress['email'])) {

            $requestData['from'] = $fromAddress;
        }


        $personalizationData = [];

        if ($emailInfo['userData']) {

            $personalizationData = $this->getUserVariables($userLogin);

            if ($emailType == 'reset_password') {

                // generate the reset link the same way WooCommerce does
                $resetEndpointUrl = wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'));
                $resetUrl = add_query_arg(array('key' => $param2, 'id' => $user->ID), $resetEndpointUrl);
                $personalizationData['user']['reset_link'] = $resetUrl;
                $personalizationData['user']['reset_key'] = $param2;
            }
        }

        $personalizationData = array_merge($personalizationData, $this->getStoreVariables());

        $mainWooVariables = [
            'site_title' => $personalizationData['store']['name'],
            'site_url' => $personalizationData['store']['url']
        ];

        foreach ($mainWooVariables as $wooVar => $val) {

            $requestData['subject'] = str_replace('{' . $wooVar . '}', $val, $requestData['subject']);
        }


        $requestData['personalization'] = [];
        $requestData['personalization'][] = [
            'email' => $toAddress['email'],
            'data' => $personalizationData
        ];

        $mailersendApi = Mailersend_Woocommerce_Api::getInstance();
        $response = $mailersendApi->postRequest('/email', $requestData);

        // check for validation errors
        if ($mailersendApi->responseCode == 422) {

            $responseMessage = '';

            $respObj = json_decode($response, true);

            if (isset($respObj['errors']['from.email'])) { // domain verification check

                // domain is not verified
                $responseMessage = 'Your email domain is not verified.';

            } else if (isset($respObj['errors']['template_id'])) { // template id check

                $responseMessage = 'The template id is not correct.';

            } else {

                // generic validation message
                $responseMessage = 'The sent data could not be validated. Please try again or contact us.';
            }

            return [

                'success' => false,
                'status' => 422,
                'response' => esc_html( $responseMessage )
            ];
        }

        if ($mailersendApi->requestError) {

            return [
                'success' => false,
                'status' => intval( $mailersendApi->responseCode ),
                'error' => esc_html( $response )
            ];
        } else {

            return [
                'success' => true,
                'status' => intval( $mailersendApi->responseCode ),
                'response' => esc_html( $response )
            ];
        }
    }


    /**
     * Gets the generic personalization variables for the order
     * @param int $orderId
     * @param string $emailType
     * @return array|null
     */
    public function getGenericVariables($orderId, $emailType = 'normal')
    {

        if (!$orderId) {

            return null;
        }

        $order = wc_get_order($orderId);

        if (!$order) {

            return null;
        }

        $data = [];
        $data['items'] = [];

        $orderItems = $order->get_items();

        foreach ($orderItems as $itemId => $item) {

            $orderItem = [];

            /** @var WC_Product $product */
            $product = $item->get_product();

            $orderItem['product'] = $item->get_name();
            $orderItem['quantity'] = $item->get_quantity();
            $orderItem['sku'] = $product->get_sku();
            $orderItem['image'] = $this->getProductImage($product);

            $orderItem['price'] = $this->formatPrice( $order->get_item_total($item, false, true) );

            $downloads = $item->get_item_downloads();

            if (count($downloads) > 0) {

                $orderItem['downloads'] = [];

                foreach ($downloads as $download) {

                    $orderItem['downloads'][] = [

                        'name' => $download['download_name'],
                        'url' => $download['download_url']
                    ];
                }
            }

            $metas = $item->get_formatted_meta_data('_', true);

            $orderItem['meta_data'] = '';
            foreach ( $metas as $metadata ) {

                $orderItem['meta_data'] .= $metadata->key . ': ' . $metadata->value . "; ";
            }

            $orderItem['meta_data'] = trim($orderItem['meta_data'],'; ');

            if ($emailType == 'refunded_order') {

                $orderItem['refunded_quantity'] = $order->get_qty_refunded_for_item($itemId) * -1;
                $orderItem['new_quantity'] = $orderItem['quantity'] - $orderItem['refunded_quantity'];
            }

            $data['items'][] = $orderItem;
        }

        $data['customer'] = [

            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'name' => $order->get_formatted_billing_full_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone()
        ];

        $orderUser = $order->get_user();

        if ($orderUser) {

            $data['customer']['username'] = $orderUser->user_login;
        }

        $shipping_methods = $order->get_items( 'shipping' );
        $shipping_method  = '';

        if ( ! empty( $shipping_methods ) ) {
            $shipping_method = reset($shipping_methods)->get_method_title();
        }

        $data['order'] = [

            'order_number' => $order->get_order_number(),
            'date' => wc_format_datetime( $order->get_date_created() ),
            'billing_address' => str_replace('<br/>', ', ', $order->get_formatted_billing_address()),
            'shipping_address' => str_replace('<br/>', ', ',$order->get_formatted_shipping_address()),
            'billing_company_name' => $order->get_billing_company(),
            'shipping_company_name' => $order->get_shipping_company(),
            'shipping_method' => $shipping_method,
            'view_url' => $order->get_view_order_url(),
            'customer_message' => $order->get_customer_note()
        ];

        $data['order_coupons'] = [];

        $couponsUsed = $order->get_coupons();
        foreach ($couponsUsed as $coupon) {

            $c = [];
            $c['code'] = $coupon->get_code();

            $c['discount'] = $this->formatPrice( $coupon->get_discount() );

            $data['order_coupons'][] = $c;
        }

        $data['invoice'] = [

            'subtotal' => $this->formatPrice( $order->get_subtotal() ),
            'discount_total' => $this->formatPrice( $order->get_total_discount() ),
            'tax' => $this->formatPrice( $order->get_total_tax() ),
            'shipping_total' => $this->formatPrice( $order->get_shipping_total() ),
            'pay_method' => $order->get_payment_method_title(),
            'pay_url' => $order->get_checkout_payment_url(),
            'total' => $this->formatPrice( $order->get_total() )
        ];

        if ($emailType == 'refunded_order') {

            $orderRefunds = $order->get_refunds();

            if (count($orderRefunds) > 0) {

                // the latest refund reason will be first
                $refund = $orderRefunds[0];
                if (method_exists($refund, 'get_reason') && $refund->get_reason()) {

                    $data['order']['refund_reason'] = $refund->get_reason();
                }
            }

            $data['invoice']['total_refunded'] = $this->formatPrice( $order->get_total_refunded() );
            $data['invoice']['new_total'] = $this->formatPrice( $order->get_total() - $order->get_total_refunded() );
        }

        return $data;
    }


    /**
     * Gets the personalization variables for the user with the given id
     * @param $userId
     * @return array
     */
    public function getUserVariables($userId)
    {

        $user = new WP_User($userId);

        if (!$user) {

            return [];
        }

        return [
            'user' => [
                'username' => $user->user_login,
                'name' => $user->display_name,
                'email' => $user->user_email
            ]
        ];
    }


    /**
     * Returns store specific variables
     * @return array
     */
    public function getStoreVariables()
    {

        return [
            'store' => [
                'url' => get_home_url(),
                'name' => get_bloginfo('name')
            ]
        ];
    }


    /**
     * Formats the given string as a WooCommerce price and decodes the currency entities (e.g. &euro; to €)
     * @param $price
     * @return string
     */
    private function formatPrice($price)
    {

        $formattedPrice = strip_tags( wc_price($price) );

        return html_entity_decode($formattedPrice, ENT_QUOTES, "UTF-8");
    }


    /**
     * Retrieves the url of the image of the given product
     * @param $product
     * @param string $size
     * @return mixed|null
     */
    private function getProductImage($product, $size = 'thumbnail')
    {
        if ($product->get_image_id()) {

            $image = wp_get_attachment_image_src( $product->get_image_id(), $size, false );
            list( $src, $width, $height ) = $image;

            return $src;
        } else if ($product->get_parent_id()) {

            $parentProduct = wc_get_product( $product->get_parent_id() );
            if ( $parentProduct ) {

                return $this->getProductImage($parentProduct, $size);
            }
        }

        return null;
    }
}
endif;