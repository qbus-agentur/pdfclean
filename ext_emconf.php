<?php
/************************************************************************
 * Extension Manager/Repository config file for ext "pdfclean".
 ************************************************************************/
$EM_CONF[$_EXTKEY] = [
    'title' => 'PDF exif clean',
    'description' => 'Remove meta data from PDF files on upload',
    'category' => 'extension',
    'constraints' => [
        'depends' => [
            'typo3' => '9.2.0-11.5.99'
        ],
        'conflicts' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Qbus\\Pdfclean\\' => 'Classes',
        ],
        'classmap' => array(
            'Resources/Private/PHP',
        ),
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Qbus Internetagentur GmbH',
    'author_email' => 'tech@qbus.de',
    'author_company' => 'Qbus Internetagentur GmbH',
    'version' => '1.0.1',
];
