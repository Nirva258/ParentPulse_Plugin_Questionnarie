<?php
/*
Plugin Name: Parent Pulse
Description: A plugin to help users find the right parenting coach by answering a quiz and subscribing to updates.
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Autoload dependencies using Composer
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// ===============================
// Mailchimp Configuration
// Need to change the key with your actual key
// ===============================

// Define the API key and List ID used to integrate with Mailchimp
define('MAILCHIMP_API_KEY', 'YOUR_MAILCHIMP_API_KEY1');
define('MAILCHIMP_LIST_ID', '8f1a17ef6f');

// ===============================
// Plugin Path Setup
// ===============================

// Define plugin path
define('QUESTIONS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// ===============================
// Include Main Plugin Logic
// ===============================
// Include the Questions Handler file
include_once(QUESTIONS_PLUGIN_PATH . 'Plugin.php');

// ===============================
// Plugin Activation Hook
// ===============================
function questions_plugin_activate() {
    // Code to run during plugin activation
}
register_activation_hook(__FILE__, 'questions_plugin_activate');

// ===============================
// Plugin Deactivation Hook
// ===============================

// Deactivation hook
function questions_plugin_deactivate() {
    // Code to run during plugin deactivation
}
register_deactivation_hook(__FILE__, 'questions_plugin_deactivate');

// ===============================
// Include Mailchimp Integration Logic
// ===============================
// Load the file that handles Mailchimp list subscription and related features
include_once plugin_dir_path(__FILE__) . 'mailchimp-handler.php';



