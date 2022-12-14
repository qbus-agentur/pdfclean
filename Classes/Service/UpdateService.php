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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UpdateService
{
    public function executeUpdate(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_storage');
        $rows = $queryBuilder
            ->select('uid')
            ->from('sys_file_storage')
            ->where($queryBuilder->expr()->eq('is_writable', 1))
            ->execute()
            ->fetchAll();

        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        foreach ($rows as $row) {
            $filter = GeneralUtility::makeInstance(FileExtensionFilter::class);
            $filter->setAllowedFileExtensions(['pdf']);

            $storage = $resourceFactory->getStorageObject((int)$row['uid']);
            $storage->setFileAndFolderNameFilters([[$filter, 'filterFileList']]);
            $files = $storage->getFilesInFolder($storage->getRootLevelFolder(), 0, 0, true, true);

            $pdfSanitizerService = GeneralUtility::makeInstance(PdfCleanService::class);
            foreach ($files as $file) {
                $localFile = $file->getForLocalProcessing(false);
                $newFile = GeneralUtility::tempnam('pdf_update_');
                $success = $pdfSanitizerService->cleanPdfFile($localFile, $newFile);

                if ($success && filesize($newFile)) {
                    $pdfSanitizerService->noop = true;
                    $storage->replaceFile($file, $newFile);
                    $pdfSanitizerService->noop = false;
                }
                if (is_file($newFile)) {
                    GeneralUtility::unlink_tempfile($newFile);
                }
                unset($file);
            }
        }
        return true;
    }
}
