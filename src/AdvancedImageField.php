<?php
namespace Doubleedesign\ACF\AdvancedImageField;
use acf_field;

/**
 * Class AdvancedImageField
 * The main field type class, containing the field definition, admin rendering, value formatting, etc.
 *
 * @package Doubleedesign\ACF\AdvancedImageField
 */
class AdvancedImageField extends acf_field {
    /** @var string Class name to use for the HTML block wrapper in the admin + the BEM block name for its sub-fields. */
    protected string $htmlBlockClass = 'advanced-image-field';
    protected AspectRatio $default_aspect_ratio = AspectRatio::SQUARE;
    protected array $default_focal_point = ['x' => 50, 'y' => 50];
    protected array $default_offset = ['x' => 0, 'y' => 0];

    public function __construct() {
        $this->name = 'image_advanced';
        $this->label = __('Image (advanced)', 'acf-advanced-image-field');
        $this->category = 'content';
        $this->description = __('Enhanced image field with aspect ratio and focal point options', 'acf-advanced-image-field');
        $this->show_in_rest = true;
        $this->doc_url = false;
        $this->preview_image = false;

        // The parent constructor must be called at the end, otherwise the method overrides won't be called.
        parent::__construct();
    }

    /**
     * Enqueue the client-side assets for the editor.
     *
     * @return void
     */
    public function input_admin_enqueue_scripts(): void {
        wp_enqueue_style(
            'advanced-image-field-editor',
            plugins_url('src/advanced-image-field.css', __DIR__),
            [],
            PluginEntryPoint::get_version()
        );

        wp_enqueue_script(
            'advanced-image-field-editor',
            plugins_url('src/advanced-image-field.js', __DIR__),
            ['jquery', 'acf'],
            PluginEntryPoint::get_version(),
            true
        );
    }

    /**
     * Format the value for use in template functions.
     *
     * @param  array|null  $value  The raw field value.
     * @param  int|string  $post_id  The post ID for this value.
     * @param  array  $field  The field specifications.
     *
     * @return array{
     *     image_id: int,
     *     src: string,
     *     alt: string,
     *     caption: string,
     *     title: string,
     *     aspect_ratio: AspectRatio,
     *     focal_point: array{x: int, y: int},
     *     image_offset: array{x: int, y: int}
     * }
     */
    public function format_value(?array $value, int|string $post_id, array $field): array {
		if($value === null) {
			return [];
		}

        $formatted = $this->strip_prefix_from_array_keys($value, "{$field['key']}__");

        // Find any second-level arrays and strip their field name prefix from their keys (
        // e.g., focal_point.focal_point__x becomes focal_point.x)
        array_walk($formatted, function(&$item, $key) use ($field) {
            if (is_array($item)) {
                $item = $this->strip_prefix_from_array_keys($item, "{$key}_");
            }
        });

        // Rename the image ID to image_id and add further values fetched from the attachment data
        $image_id = isset($formatted['image']) ? (int)$formatted['image'] : null;
        unset($formatted['image']);
        $formatted['image_id'] = $image_id;
        $formatted['src'] = $image_id ? wp_get_attachment_image_url($image_id, ACF_ADVANCED_IMAGE_FIELD_SIZE) : '';
        $formatted['alt'] = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
        $formatted['caption'] = $image_id ? wp_get_attachment_caption($image_id) : '';
        $formatted['title'] = $image_id ? get_the_title($image_id) : '';

        return $formatted;
    }

    /**
     * Utility function to strip prefixes from the keys of a field value array.
     *
     * @param  array  $array
     * @param  string  $prefix
     *
     * @return array
     */
    private function strip_prefix_from_array_keys(array $array, string $prefix): array {
        $indexed = array_map(function($key, $value) use ($prefix) {
            $short_key = str_replace($prefix, '', $key);

            // If the value is also an array, recursively strip prefixes from its keys too
            if (is_array($value)) {
                $value = $this->strip_prefix_from_array_keys($value, $prefix);
            }

            return [$short_key => $value];
        }, array_keys($array), array_values($array));

        // Flatten the resultant indexed array into a single associative array
        return array_merge(...$indexed);
    }

    /**
     * Prepare the sub-fields that make up this field.
     *
     * @param  string  $parent_key
     * @param  array  $saved_value
     *
     * @return array
     */
    protected function create_sub_fields(string $parent_key, array $saved_value): array {
        $aspect_ratio = $this->default_aspect_ratio;
        $focal_point = $this->default_focal_point;
        $offset = $this->default_offset;

        if (isset($saved_value["{$parent_key}__aspect_ratio"]) && AspectRatio::tryFrom($saved_value["{$parent_key}__aspect_ratio"]) !== null) {
            $aspect_ratio = AspectRatio::tryFrom($saved_value["{$parent_key}__aspect_ratio"]);
        }
        if (isset($saved_value["{$parent_key}__focal_point"]) && is_array($saved_value["{$parent_key}__focal_point"])) {
            $focal_point_field = "{$parent_key}__focal_point";
            $focal_point = array(
                'x' => $saved_value[$focal_point_field]["{$parent_key}__focal_point_x"] ?? $this->default_focal_point['x'],
                'y' => $saved_value[$focal_point_field]["{$parent_key}__focal_point_y"] ?? $this->default_focal_point['y']
            );
        }
        if (isset($saved_value["{$parent_key}__image_offset"]) && is_array($saved_value["{$parent_key}__image_offset"])) {
            $offset_field = "{$parent_key}__image_offset";
            $offset = array(
                'x' => $saved_value[$offset_field]["{$parent_key}__image_offset__x"] ?? $this->default_offset['x'],
                'y' => $saved_value[$offset_field]["{$parent_key}__image_offset__y"] ?? $this->default_offset['y']
            );
        }

        $options = array(
            AspectRatioSubfield::create($parent_key, $aspect_ratio),
            FocalPointSubfield::create($parent_key, $focal_point),
            ImageOffsetSubfield::create($parent_key, $offset)
        );

        // Allow plugin and theme developers to add more options fields, modify field instructions and admin wrappers, etc.
        $options = apply_filters('acf_advanced_image_options_fields', $options, $parent_key);

        return array(
            ImageSubfield::create(
                $parent_key,
                (isset($saved_value["{$parent_key}__image"]) && is_numeric($saved_value["{$parent_key}__image"]))
                    ? (int)$saved_value["{$parent_key}__image"]
                    : 0
            ),
            ...$options
        );
    }

    /**
     * Render the field in the admin.
     *
     * @param  $field
     *
     * @return void
     */
    public function render_field($field): void {
        // If the field has a value saved, create the sub-fields with the saved values; otherwise defaults will be used.
        $sub_fields = $this->create_sub_fields($field['key'], (is_array($field['value']) ? $field['value'] : []));

        // Attach to parent and ensure the sub-fields have the values required to render correctly
        $sub_fields = array_map(function($sub_field) use ($field) {
            $sub_field['prefix'] = $field['name'];
            $sub_field['value'] = $field['value'][$sub_field['name']] ?? null;
            $sub_field['_name'] = $field['name'] . '[' . $sub_field['name'] . ']';
            if (isset($sub_field['sub_fields'])) {
                foreach ($sub_field['sub_fields'] as &$sub_sub_field) {
                    $sub_sub_field['prefix'] = $sub_field['name'];
                    $sub_sub_field['value'] = $field['value'][$sub_field['name']][$sub_sub_field['name']] ?? null;
                    $sub_sub_field['_name'] = $field['name'] . '[' . $sub_field['name'] . ']' . '[' . $sub_sub_field['name'] . ']';
                }
            }

            return acf_prepare_field($sub_field);
        }, $sub_fields);

        ob_start();
        acf_render_fields(array_slice($sub_fields, 0, 1));
        $image = ob_get_clean();

        ob_start();
        acf_render_fields(array_slice($sub_fields, 1));
        $options = ob_get_clean();

        echo <<<HTML
			<div class="{$this->htmlBlockClass}">
				<div class="{$this->htmlBlockClass}__image">
					$image
				</div>
				<div class="{$this->htmlBlockClass}__options">
					$options
				</div>
			</div>
		HTML;

    }
}
