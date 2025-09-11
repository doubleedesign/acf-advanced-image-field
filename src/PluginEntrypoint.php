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
        add_action('init', [$this, 'register_image_size']);
        add_action('acf/init', [$this, 'register_advanced_image_field'], 5);
        new AdminUI();
    }

    public static function get_version(): string {
        return self::$version;
    }

    /**
     * Register an image size to use, so larger images are scaled down
     * to max 1200 on their longest side without cropping.
     *
     * @return void
     */
    public function register_image_size(): void {
        define('ACF_ADVANCED_IMAGE_FIELD_SIZE', 'image_advanced_resized');
        add_image_size(ACF_ADVANCED_IMAGE_FIELD_SIZE, 1200, 1200, false);
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
