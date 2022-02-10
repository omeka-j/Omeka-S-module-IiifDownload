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
        ],
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
    "iiifdownload" => [
        "config" => [
            'iiifdownload_url' => "https://static.ldas.jp/viewer/iiif/downloader/?manifest=",
            "iiifdownload_description" => 'ファイルサイズが大きくなる場合がありますのでご注意ください。ダウンロードがうまく実行されない場合には、<a target="_blank" href="https://www.lib.u-tokyo.ac.jp/ja/library/contents/archives-top/iiifimagedownloader">こちら</a>のダウンローダをご利用ください（但しWindowsのみに対応）。',
        ]
    ],
    // 依存モジュール追加
    'dependencies' => [
        'IiifServer',
    ],
];