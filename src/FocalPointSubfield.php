<?php
namespace Doubleedesign\ACF\AdvancedImageField;

/**
 * Factory-ish class to create the ACF sub-field definition for the focal point.
 * Separated out to keep the main field definition class simple and focused.
 */
class FocalPointSubfield {

    /**
     * Create the focal point field sub-field, populating it with the given saved or fallback value.
     *
     * @param  string  $parent_key
     * @param  array{x: int, y: int}  $value  - coordinates (0-100) as integers
     *
     * @return array
     */
    public static function create(string $parent_key, array $value): array {
        return array(
            'key'          => $parent_key . '__focal_point',
            'label'        => 'Focal point',
            'name'         => 'focal_point',
            'instructions' => 'A point on the image to prioritise when cropping; enter values from top left corner or click on the image to select',
            'type'         => 'group',
            'layout'       => 'block',
            'sub_fields'   => array(
                array(
                    'key'           => $parent_key . '__focal_point_x',
                    'label'         => 'X',
                    'name'          => 'x',
                    'type'          => 'number',
                    'default_value' => $value['x'],
                    'min'           => 0,
                    'max'           => 100,
                    'append'        => '%',
                    'wrapper'       => [
                        'data-global-key' => 'focal-point-x' // data attribute rendered in the admin for use in JS/CSS, unaffected by ACF per-instance processing
                    ]
                ),
                array(
                    'key'           => $parent_key . '__focal_point_y',
                    'label'         => 'Y',
                    'name'          => 'y',
                    'type'          => 'number',
                    'default_value' => $value['y'],
                    'min'           => 0,
                    'max'           => 100,
                    'append'        => '%',
                    'wrapper'       => [
                        'data-global-key' => 'focal-point-y' // data attribute rendered in the admin for use in JS/CSS, unaffected by ACF per-instance processing
                    ]
                ),
            )
        );
    }
}
