<?php

/**
 * Provides simple methods to access to the MailerSend API
 *
 * @author     MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce_Api {

    private $endpointBase = 'https://api.mailersend.com/v1';

    public $requestError = false;
    public $responseCode = null;

    private static $instance = null;

    /**
     * Returns the instance of the Mailersend_Woocommerce_Api class
     * @return Mailersend_Woocommerce_Api
     */
    public static function getInstance()
    {
        if (!self::$instance) {

            self::$instance = new Mailersend_Woocommerce_Api();
        }

        return self::$instance;
    }


    /**
     * Does a POST request to the given MailerSend endpoint
     * It returns false if there was an error
     * @param $endpoint
     * @param array|object $requestBody
     * @return bool|string
     */
    public function postRequest($endpoint, $requestBody)
    {

        $this->requestError = false;
        $this->responseCode = null;

        $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . sanitize_text_field( get_option('mailersend_api_key') )
            ],
            'body' => json_encode($requestBody)
        ];

        $response = wp_remote_post($this->endpointBase . $endpoint, $requestArgs);

        if (!is_wp_error($response)) {

            $this->responseCode = $response['response']['code'];

            return wp_remote_retrieve_body($response);
        }

        $this->requestError = true;

        return false;
    }


    /**
     * Does a GET request to the given MailerSend endpoint
     * It returns false if there was an error
     * @param $endpoint
     * @return bool|string
     */
    public function getRequest($endpoint)
    {

        $this->requestError = false;
        $this->responseCode = null;

        $requestArgs = [
            'httpversion' => '1.1',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . sanitize_text_field( get_option('mailersend_api_key') )
            ]
        ];

        $response = wp_remote_get($this->endpointBase . $endpoint, $requestArgs);

        if (!is_wp_error($response)) {

            $this->responseCode = $response['response']['code'];

            return wp_remote_retrieve_body($response);
        }

        $this->requestError = true;

        return false;
    }
}