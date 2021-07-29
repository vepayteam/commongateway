<?php

namespace app\services\payment\banks\bank_adapter_responses\decta;

use app\services\base\traits\Fillable;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;

class CreatePayResponse extends BaseResponse
{
    use Fillable;

    public $transac;
    public $url;

    public $type;
    public $id;
    public $products;
    public $client;
    public $request_client_info;
    public $brand;
    public $website;
    public $currency;
    public $number;
    public $due;
    public $deny_overdue_payment;
    public $skip_capture;
    public $language;
    public $notes;
    public $is_test;
    public $is_payable;
    public $terminal_processing_id;
    public $success_redirect;
    public $failure_redirect;
    public $cancel_redirect;
    public $custom_invoice_url;
    public $link;
    public $download_link;
    public $download_links;
    public $print_link;
    public $print_links;
    public $full_page_checkout;
    public $iframe_checkout;
    public $direct_post;
    public $iframe_checkout_send_invoice;
    public $subtotal;
    public $total_tax;
    public $total_discount;
    public $total;
    public $subtotal_override;
    public $total_tax_override;
    public $total_discount_override;
    public $total_override;
    public $refund_amount;
    public $amount_refunded;
    public $amount_refund_initial;
    public $amount_refund_reversal;
    public $created_by;
    public $issuer;
    public $status;
    public $status_changes;
    public $transaction_details;
    public $issued;
    public $modified;
    public $viewed;
    public $captured;
    public $paid;
    public $issued_override;
    public $client_display_name;
    public $timezone;
    public $from_api;
    public $from_subscription;
    public $issued_by_client;
    public $referrer;
    public $referrer_display_name;
    public $permitted_actions;
    public $show_preview;
    public $is_moto;
    public $dynamic_descriptor;
    public $api_do_url;
    public $payment_system;
    public $author;
    public $save_card;
    public $api_do_applepay;
    public $pan_first;
    public $recurring_3d;
    public $verify_card;
    public $use_verified_card;
    public $sdwo_merchant_id;
    public $max_payment_attempts;
    public $account;
    public $api_init_paypal;
    public $payment_execution_method;
}
