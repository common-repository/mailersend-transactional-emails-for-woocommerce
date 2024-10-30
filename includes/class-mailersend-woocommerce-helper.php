<?php

/**
 * Class Mailersend_Woocommerce_Helper
 * Helper class that contains various definitions and helper methods
 *
 * @author MailerSend <support@mailersend.com>
 */
class Mailersend_Woocommerce_Helper {
    
    // Maps WC hooks to WC and MS emails
    // these were compiled from Mailersend_Woocommerce_Send and Mailersend_Woocommerce:register_email
    public static $wcMsEmailsMapping = [
        [
            'hook' => 'woocommerce_new_order',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_payment_complete',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],

        // new order triggers
        [
            'hook' => 'woocommerce_order_status_pending_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
            'msEmail' => 'new_order_mail',
            'args' => 2
        ],


        // on-hold triggers
        [
            'hook' => 'woocommerce_order_status_pending_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
            'msEmail' => 'onhold_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
            'msEmail' => 'onhold_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
            'msEmail' => 'onhold_mail',
            'args' => 2
        ],


        // refunded order triggers
        [
            'hook' => 'woocommerce_order_fully_refunded_notification',
            'wcEmail' => 'WC_Email_Customer_Refunded_Order',
            'msEmail' => 'refund_trigger_full',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_partially_refunded_notification',
            'wcEmail' => 'WC_Email_Customer_Refunded_Order',
            'msEmail' => 'refund_trigger_full',
            'args' => 2
        ],


        // cancelled order triggers
        [
            'hook' => 'woocommerce_order_status_processing_to_cancelled_notification',
            'wcEmail' => 'WC_Email_Cancelled_Order',
            'msEmail' => 'cancelled_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_cancelled_notification',
            'wcEmail' => 'WC_Email_Cancelled_Order',
            'msEmail' => 'cancelled_order_mail',
            'args' => 2
        ],


        // processing order triggers
        [
            'hook' => 'woocommerce_order_status_cancelled_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
            'msEmail' => 'processing_order',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
            'msEmail' => 'processing_order',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
            'msEmail' => 'processing_order',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
            'msEmail' => 'processing_order',
            'args' => 2
        ],


        // failed order triggers
        [
            'hook' => 'woocommerce_order_status_pending_to_failed_notification',
            'wcEmail' => 'WC_Email_Failed_Order',
            'msEmail' => 'failed_order_mail',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_failed_notification',
            'wcEmail' => 'WC_Email_Failed_Order',
            'msEmail' => 'failed_order_mail',
            'args' => 2
        ],

        // customer completed order trigger
        [
            'hook' => 'woocommerce_order_status_completed_notification',
            'wcEmail' => 'WC_Email_Customer_Completed_Order',
            'msEmail' => 'completed_order_mail',
            'args' => 2
        ],



        // misc triggers
        [
            'hook' => 'woocommerce_new_customer_note_notification',
            'wcEmail' => 'WC_Email_Customer_Note',
            'msEmail' => 'customer_note_mail',
            'args' => 1
        ],

        [
            'hook' => 'woocommerce_reset_password_notification',
            'wcEmail' => 'WC_Email_Customer_Reset_Password',
            'msEmail' => 'reset_password_mail',
            'args' => 2
        ],


        [
            'hook' => 'woocommerce_created_customer_notification',
            'wcEmail' => 'WC_Email_Customer_New_Account',
            'msEmail' => 'new_account_mail',
            'args' => 3
        ],


        [
            'hook' => 'woocommerce_before_resend_order_emails',
            'wcEmail' => 'WC_Email_Customer_Invoice',
            'msEmail' => 'order_invoice_mail',
            'args' => 2
        ],
        
    ];


    /**
     * Mapping between the email type and the WC email and saved template option name
     * @var string[][]
     */
    public static $msEmailMapping = [

        'new_order' => [
            'wcEmail' => 'WC_Email_New_Order',
            'optionName' => 'mailersend_new_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'cancel_order' => [
            'wcEmail' => 'WC_Email_Cancelled_Order',
            'optionName' => 'mailersend_cancel_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'failed_order' => [
            'wcEmail' => 'WC_Email_Failed_Order',
            'optionName' => 'mailersend_failed_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'onhold_order' => [
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
            'optionName' => 'mailersend_onhold_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'processing_order' => [
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
            'optionName' => 'mailersend_processing_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'completed_order' => [
            'wcEmail' => 'WC_Email_Customer_Completed_Order',
            'optionName' => 'mailersend_completed_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'refunded_order' => [
            'wcEmail' => 'WC_Email_Customer_Refunded_Order',
            'optionName' => 'mailersend_refunded_order_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'customer_invoice' => [
            'wcEmail' => 'WC_Email_Customer_Invoice',
            'optionName' => 'mailersend_customer_invoice_template_id',
            'orderData' => true,
            'userData' => false
        ],
        'customer_note' => [
            'wcEmail' => 'WC_Email_Customer_Note',
            'optionName' => 'mailersend_customer_note_template_id',
            'orderData' => true,
            'userData' => true
        ],
        'reset_password' => [
            'wcEmail' => 'WC_Email_Customer_Reset_Password',
            'optionName' => 'mailersend_reset_password_template_id',
            'orderData' => false,
            'userData' => true
        ],
        'new_account' => [
            'wcEmail' => 'WC_Email_Customer_New_Account',
            'optionName' => 'mailersend_new_account_template_id',
            'orderData' => false,
            'userData' => true
        ]
    ];
}