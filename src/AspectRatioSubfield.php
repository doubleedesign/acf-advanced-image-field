<?php
namespace Doubleedesign\ACF\AdvancedImageField;

/**
 * Factory-ish class to create the ACF sub-field definition for the aspect ratio.
 * Separated out to keep the main field definition class simple and focused.
 */
class AspectRatioSubfield {

    /**
     * Create the aspect ratio field sub-field, populating it with the given saved or fallback value.
     *
     * @param  string  $parent_key
     * @param  AspectRatio  $value
     *
     * @return array
     */
    public static function create(string $parent_key, AspectRatio $value): array {
        $enum_array = array_combine(
            array_column(AspectRatio::cases(), 'name'),
            array_column(AspectRatio::cases(), 'value')
        );

        $options = array_reduce(array_keys($enum_array), function($carry, $key) use ($enum_array) {
            $value = $enum_array[$key];
            $label = str_replace('_', ' ', strtolower($key));
            $carry[$value] = ucwords($label) . " ($value)";

            return $carry;
        }, []);

        return array(
            'key'           => $parent_key . '__aspect_ratio',
            'label'         => 'Ideal aspect ratio',
            'instructions'  => 'Crop the image to suit this aspect ratio by default, where possible. Note that your theme code may override this in some situations (for example, making a banner higher than cinema ratios on small viewports)',
            'name'          => 'aspect_ratio',
            'type'          => 'select',
            'choices'       => $options,
            'default_value' => $value->value,
            'return_format' => 'value',
            'multiple'      => false,
            'allow_null'    => 0,
            'ui'            => 0,
            'wrapper'       => [
                'data-global-key' => 'aspect-ratio' // data attribute rendered in the admin for use in JS/CSS, unaffected by ACF per-instance processing
            ]
        );
    }
}
