<?php

namespace Doubleedesign\ACF\AdvancedImageField;

/**
 * Factory-ish class to create the ACF sub-field definition for the offset.
 * Separated out to keep the main field definition class simple and focused.
 */
class ImageOffsetSubfield {

    /**
     * Create the image offset field sub-field, populating it with the given saved or fallback value.
     *
     * @param  string  $parent_key
     * @param  array{x: int, y: int}  $value  - X and Y offsets percentages as integers
     *
     * @return array
     */
    public static function create(string $parent_key, array $value): array {

        return array(
            'key'          => $parent_key . '__image_offset',
            'label'        => 'Image offset',
            'name'         => 'image_offset',
            'instructions' => 'Automatically-calculated offsets used to crop the image to suit the aspect ratio and focal point selections',
            'type'         => 'group',
            'layout'       => 'block',
            'sub_fields'   => array(
                array(
                    'key'           => $parent_key . '__image_offset_x',
                    'label'         => 'X',
                    'name'          => 'x',
                    'type'          => 'number',
                    'default_value' => $value['x'],
                    'append'        => '%',
                    'readonly'      => true,
                    'wrapper'       => [
                        'data-global-key' => 'image-offset-x' // data attribute rendered in the admin for use in JS/CSS, unaffected by ACF per-instance processing
                    ]
                ),
                array(
                    'key'           => $parent_key . '__image_offset_y',
                    'label'         => 'Y',
                    'name'          => 'y',
                    'type'          => 'number',
                    'default_value' => $value['y'],
                    'append'        => '%',
                    'readonly'      => true,
                    'wrapper'       => [
                        'data-global-key' => 'image-offset-y' // data attribute rendered in the admin for use in JS/CSS, unaffected by ACF per-instance processing
                    ]
                ),
            )
        );
    }
}
