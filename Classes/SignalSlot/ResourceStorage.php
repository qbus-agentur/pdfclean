<?php

/*
 * This file is part of the package qbus/pdfclean.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Qbus\Pdfclean\SignalSlot;

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
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ResourceStorage
 */
class ResourceStorage
{

    /**
     * @param string $targetFileName
     * @param Folder $targetFolder
     * @param string $sourceFilePath
     * @param \TYPO3\CMS\Core\Resource\ResourceStorage $parentObject
     * @param DriverInterface $driver
     *
     * @throws \InvalidArgumentException
     */
    public function preFileAdd(&$targetFileName, $targetFolder, $sourceFilePath, $parentObject, $driver)
    {
        $pdfService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($pdfService->isPdfFile($sourceFilePath)) {
            $pdfService->sanitizePdfFile($sourceFilePath);
        }
    }

    /**
     * @param FileInterface $file
     * @param string $localFilePath
     *
     * @throws \InvalidArgumentException
     */
    public function preFileReplace($file, $localFilePath)
    {
        $pdfService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($pdfService->isPdfFile($localFilePath)) {
            $pdfService->sanitizePdfFile($localFilePath);
        }
    }

    /**
     * @param FileInterface $file
     * @param string $content
     *
     * @throws \InvalidArgumentException
     */
    public function postFileSetContents($file, $content)
    {
        $pdfService = GeneralUtility::makeInstance(PdfCleanService::class);
        if ($pdfService->isPdfFile($file->getForLocalProcessing(false))) {
            $newContent = $pdfService->sanitizeAndReturnPdfContent($content);
            // prevent endless loop because this hook is called again and again and again and...
            if ($newContent !== $content) {
                $file->setContents($newContent);
            }
        }
    }
}
