<?php
namespace IiifDownload;

return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/IiifDownload/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'IiifDownload' => View\Helper\IiifDownload::class,
        ]
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
];