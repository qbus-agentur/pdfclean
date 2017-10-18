<?php
/************************************************************************
 * Extension Manager/Repository config file for ext "svg_sanitizer".
 ************************************************************************/
$EM_CONF[$_EXTKEY] = [
    'title' => 'SVG Sanitizer',
    'description' => 'Sanitize SVG files on upload, works with all fall uploads.',
    'category' => 'extension',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-9.99.99'
        ],
        'conflicts' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'T3G\\SvgSanitizer\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Frank Nägler',
    'author_email' => 'frank.naegler@typo3.com',
    'author_company' => 'TYPO3 GmbH',
    'version' => '1.0.0',
];
