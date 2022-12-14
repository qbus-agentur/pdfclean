<?php

/*
 * This file is part of the package qbus/pdfclean.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Qbus\Pdfclean\Tests\Functional\Service;

/*
 * This file is part of the TYPO3 extension pdfclean.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\Finder\Finder;
use Qbus\Pdfclean\Service\PdfCleanService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class PdfCleanService
 */
class PdfCleanServiceTest extends FunctionalTestCase
{
    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->testExtensionsToLoad[] = 'web/typo3conf/ext/pdfclean';

        if (!class_exists(Sanitizer::class)) {
            $extensionBasePath = dirname(__DIR__ . '/../../../../');
            @include 'phar://' . $extensionBasePath . '/Libraries/enshrined-svg-sanitize.phar/vendor/autoload.php';
        }
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return array
     */
    public function svgImagesDataProvider()
    {
        $basePath = dirname(__DIR__ . '/../../../') . '/Fixtures/';
        $finder = new Finder();
        $finder
            ->files()
            ->in($basePath . 'DirtySVG/')
            ->name('*.svg');
        $data = [];
        foreach ($finder as $file) {
            $fileName = $file->getFilename();
            $data[$fileName] = ['DirtySVG/' . $fileName, 'CleanSVG/' . $fileName];
        }
        return $data;
    }

    /**
     * @param string $inputFile
     * @param string $expectedOutputFile
     * @dataProvider svgImagesDataProvider
     */
    public function testThatImagesCanBeCleaned($inputFile, $expectedOutputFile)
    {
        $basePath = dirname(__DIR__ . '/../../../') . '/Fixtures/';
        $service = new PdfCleanService();
        self::assertStringEqualsFile(
            $basePath . $expectedOutputFile,
            $service->sanitizeAndReturnSvgContent(file_get_contents($basePath . $inputFile))
        );
    }
}
