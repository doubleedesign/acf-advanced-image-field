<?php
/**
 * Plugin Name: ACF Advanced Image Field
 * Description: Enhanced image field for Advanced Custom Fields with aspect ratio and focal point settings + real-time in-editor preview.
 * Requires PHP: 8.3
 * Author: Double-E Design
 * Plugin URI: https://github.com/doubleedesign/acf-advanced-image-field
 * Author URI: https://www.doubleedesign.com.au
 * Version: 0.0.1
 * Requires plugins: advanced-custom-fields-pro
 * Text domain: acf-advanced-image-field
 */

include __DIR__ . '/vendor/autoload.php';
use Doubleedesign\ACF\AdvancedImageField\PluginEntrypoint;

new PluginEntrypoint();

function activate_acf_advanced_image_field(): void {
    PluginEntrypoint::activate();
}
function deactivate_acf_advanced_image_field(): void {
    PluginEntrypoint::deactivate();
}
function uninstall_acf_advanced_image_field(): void {
    PluginEntrypoint::uninstall();
}
register_activation_hook(__FILE__, 'activate_acf_advanced_image_field');
register_deactivation_hook(__FILE__, 'deactivate_acf_advanced_image_field');
register_uninstall_hook(__FILE__, 'uninstall_acf_advanced_image_field');
