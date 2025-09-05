<?php

namespace Doubleedesign\ACF\AdvancedImageField;

/**
 * Class PluginEntrypoint
 * Registers the Advanced Image Field with ACF and handles activation/deactivation/uninstall hooks.
 * This is the only class that should be instantiated outside the namespace (e.g., in the main plugin file).
 *
 * @package Doubleedesign\ACF\AdvancedImageField
 */
class PluginEntrypoint {
    private static string $version = '0.0.1';

    public function __construct() {
        add_action('acf/init', [$this, 'register_advanced_image_field'], 5);
        new AdminUI();
    }

    public static function get_version(): string {
        return self::$version;
    }

    public function register_advanced_image_field(): void {
        if (!function_exists('acf_register_field_type')) {
            return;
        }
        if (!class_exists('acf_field__group')) {
            return;
        }

        acf_register_field_type(AdvancedImageField::class);
    }

    public static function activate() {
        // TODO: Handle re-activation field updates
    }

    public static function deactivate() {
        // TODO: On deactivation, update existing fields to use standard image field
        // but in a way that enables them to be switched back on re-activation
    }

    public static function uninstall() {
        // TODO: On uninstall, remove metadata that enables the switch back on re-activation
    }
}
