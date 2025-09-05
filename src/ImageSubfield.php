<?php
namespace Doubleedesign\ACF\AdvancedImageField;

/**
 * Factory-ish class to create the ACF sub-field definition for the image.
 * Separated out to keep the main field definition class simple and focused.
 */
class ImageSubfield {

    /**
     * Create the image field sub-field, populating it with the given saved or fallback value.
     *
     * @param  string  $parent_key
     * @param  int  $value  - the ID of the attachment object to load into the field. Use 0 for a new/empty field.
     *
     * @return array
     */
    public static function create(string $parent_key, int $value): array {
        return array(
            'key'                   => $parent_key . '__image',
            'label'                 => 'Image',
            'name'                  => 'image',
            'type'                  => 'image',
            'default_value'         => $value,
            'required'              => true,
            'return_format'         => 'array',
            'preview_size'          => 'full',
            'library'               => 'all'
        );
    }
}
