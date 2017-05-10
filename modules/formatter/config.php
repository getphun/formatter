<?php
/**
 * formatter config file
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'formatter',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/formatter',
    '__files' => [
        'modules/formatter' => [
            'install',
            'remove',
            'update'
        ]
    ],
    '__dependencies' => [
        'core',
        '/media/db-mysql'
    ],
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'Formatter'                     => 'modules/formatter/library/Formatter.php',
            'Formatter\\Object\\DateTime'   => 'modules/formatter/object/DateTime.php',
            'Formatter\\Object\\Embed'      => 'modules/formatter/object/Embed.php',
            'Formatter\\Object\\Enum'       => 'modules/formatter/object/Enum.php',
            'Formatter\\Object\\Location'   => 'modules/formatter/object/Location.php',
            'Formatter\\Object\\Media'      => 'modules/formatter/object/Media.php',
            'Formatter\\Object\\Number'     => 'modules/formatter/object/Number.php',
            'Formatter\\Object\\Text'       => 'modules/formatter/object/Text.php',
        ],
        'files' => []
    ]
];