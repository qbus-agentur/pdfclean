<?php

/*
 * This file is part of the package qbus/pdfclean.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Qbus\Pdfclean\EventListener;

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

use Qbus\Pdfclean\Service\PdfCleanService;
use TYPO3\CMS\Core\Resource\Event\AfterFileContentsSetEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileAddedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileReplacedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResourceStorageListener
{

    public function beforeFileAdded(BeforeFileAddedEvent $event)
    {
        $sourceFilePath = $event->getSourceFilePath();
        $svgService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($svgService->isSvgFile($sourceFilePath)) {
            $svgService->sanitizeSvgFile($sourceFilePath);
        }
    }

    public function beforeFileReplaced(BeforeFileReplacedEvent $event)
    {
        $localFilePath = $event->getLocalFilePath();
        $svgService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($svgService->isSvgFile($localFilePath)) {
            $svgService->sanitizeSvgFile($localFilePath);
        }
    }

    public function afterFileContentsSet(AfterFileContentsSetEvent $event)
    {
        $file = $event->getFile();
        $content = $event->getContent();
        $svgService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($svgService->isSvgFile($file->getForLocalProcessing(false))) {
            $newContent = $svgService->sanitizeAndReturnSvgContent($content);
            // prevent endless loop because this hook is called again and again and again and...
            if ($newContent !== $content) {
                $file->setContents($newContent);
            }
        }
    }
}
