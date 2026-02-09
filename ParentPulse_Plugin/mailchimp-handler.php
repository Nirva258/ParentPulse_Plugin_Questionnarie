<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Subscribe a user to Mailchimp using the DrewM/Mailchimp-API library.
 *
 * @param string $email The subscriber's email address.
 * @param string $first_name Optional first name.
 * @param string $last_name Optional last name.
 * @param string $answers Optional answers string.
 * @return array|WP_Error The API response array or a WP_Error on failure.
 */
function subscribe_user_to_mailchimp( $email, $first_name = '', $last_name = '', $answers = '' ) {
    if ( empty( $email ) ) {
        return new WP_Error( 'no_email', 'Email is required' );
    }

    // Instantiate the Mailchimp API class using your API key.
    $MailChimp = new \DrewM\MailChimp\MailChimp( MAILCHIMP_API_KEY );

    // Prepare the subscriber data.
    $data = [
        'email_address' => $email,
        'status'        => 'subscribed', // Use "pending" if you want double opt-in.
        'merge_fields'  => [
            'FNAME'   => $first_name,
            'LNAME'   => $last_name,
            'ANSWERS' => $answers, // Merge field to store quiz answers.
        ],
    ];

    // Post the data to the Mailchimp API endpoint for your list.
    $result = $MailChimp->post( "lists/" . MAILCHIMP_LIST_ID . "/members", $data );

    if ( $MailChimp->success() ) {
        return $result;
    } else {
        // Return an error if something goes wrong.
        return new WP_Error( 'mailchimp_error', $MailChimp->getLastError() );
    }
}

/**
 * AJAX handler for subscribing a user via Mailchimp.
 */
function handle_mailchimp_subscription() {
    // Optionally, verify a nonce here for extra security.
    // check_ajax_referer( 'your_nonce_action', 'security' );

    // Get and sanitize input data.
    $email      = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
    $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
    $answers    = isset( $_POST['user_answers'] ) ? sanitize_text_field( $_POST['user_answers'] ) : '';

    $result = subscribe_user_to_mailchimp( $email, $first_name, $last_name, $answers );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    } else {
        wp_send_json_success( 'Subscribed successfully!' );
    }
}
add_action( 'wp_ajax_subscribe_mailchimp', 'handle_mailchimp_subscription' );
add_action( 'wp_ajax_nopriv_subscribe_mailchimp', 'handle_mailchimp_subscription' );
