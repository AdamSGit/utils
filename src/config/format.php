<?php
/**
 * Format class config
 */

return [
    'csv' => [
        'import' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'newline'   => "\n",
            'escape'    => '\\',
        ],
        'export' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'newline'   => "\n",
            'escape'    => '\\',
        ],
        'regex_newline'   => "\n",
        'enclose_numbers' => true,
    ],
    'xml' => [
        'basenode'            => 'xml',
        'use_cdata'           => false,
        'bool_representation' => null,
    ],
    'json' => [
        'encode' => [
            'options' => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP,
        ],
    ],
];
