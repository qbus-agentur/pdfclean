<?php

/*
 * This file is part of the package qbus/pdfclean.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Qbus\Pdfclean\Service;

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
use TYPO3\CMS\Core\Type\File\FileInfo;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PdfCleanService
 */
class PdfCleanService
{
    protected $possibleMimeTypes = ['image/svg', 'image/svg+xml', 'application/svg', 'application/svg+xml'];

    /**
     * @param string $fileNameAndPath
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isSvgFile($fileNameAndPath)
    {
        $fileInfo = GeneralUtility::makeInstance(FileInfo::class, $fileNameAndPath);
        return $fileInfo->getExtension() === 'svg'
            || \in_array(strtolower($fileInfo->getMimeType()), $this->possibleMimeTypes, true);
    }

    /**
     * @param string $fileNameAndPath
     * @param string $outputFileNameAndPath
     * @throws \BadFunctionCallException
     */
    public function sanitizeSvgFile($fileNameAndPath, $outputFileNameAndPath = null)
    {
        if ($outputFileNameAndPath === null) {
            $outputFileNameAndPath = $fileNameAndPath;
        }
        $dirtySVG = file_get_contents($fileNameAndPath);
        $cleanSVG = $this->sanitizeAndReturnSvgContent($dirtySVG);
        if ($cleanSVG !== $dirtySVG) {
            file_put_contents($outputFileNameAndPath, $cleanSVG);
        }
    }

    /**
     * @param string $dirtySVG
     *
     * @return string
     * @throws \BadFunctionCallException
     */
    public function sanitizeAndReturnSvgContent($dirtySVG)
    {
        $extensionBasePath = ExtensionManagementUtility::extPath('pdfclean');
        if (!class_exists(Sanitizer::class)) {
            @include 'phar://' . $extensionBasePath . 'Libraries/enshrined-svg-sanitize.phar/vendor/autoload.php';
        }
        $sanitizer = new Sanitizer();
        $sanitizer->removeRemoteReferences(true);
        return $sanitizer->sanitize($dirtySVG);
    }
}
