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

use enshrined\pdfSanitize\Sanitizer;
use TYPO3\CMS\Core\Type\File\FileInfo;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PdfCleanService
 */
class PdfCleanService
{
    protected $possibleMimeTypes = ['application/pdf'];

    /**
     * @param string $fileNameAndPath
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isPdfFile($fileNameAndPath)
    {
        $fileInfo = GeneralUtility::makeInstance(FileInfo::class, $fileNameAndPath);
        return $fileInfo->getExtension() === 'pdf'
            || \in_array(strtolower($fileInfo->getMimeType()), $this->possibleMimeTypes, true);
    }

    /**
     * @param string $fileNameAndPath
     * @param string $outputFileNameAndPath
     * @throws \BadFunctionCallException
     */
    public function sanitizePdfFile($fileNameAndPath, $outputFileNameAndPath = null)
    {
        if ($outputFileNameAndPath === null) {
            $outputFileNameAndPath = $fileNameAndPath;
        }

        $intermediateFile = GeneralUtility::tempnam('pdf_clean_');

        $this->run('exiftool -all= -Author='' -tagsfromfile @ -title -keywords -subject -description %s -o %s', $fileNameAndPath, $intermediate);
        $this->run('qpdf --linearize %s %s', $intermediate, $outputFileNameAndPath);
        GeneralUtility::unlink_tempfile($intermediateFile);
    }

    /**
     * @param string $dirtyPDF
     * @return string
     * @throws \BadFunctionCallException
     */
    public function sanitizeAndReturnPdfContent($dirtyPDF)
    {
        $tmpFile = GeneralUtility::tempnam('pdf_clean_tmp_');
        file_put_contents($tmpFile, $dirtyPDF);
        $this->sanitizePdfFile($tmpFile);
        $cleanedPDF = file_get_contents($tmpFile);
        GeneralUtility::unlink_tempfile($tmpFile);

        return $cleanedPDF;
    }

    protected function run($cmdline, ...$args) {
        $cmd = vsprintf($cmdline, array_map('escapeshellarg', $args));
        if ($cmd === false) {
            throw new \Exception('Invalid arguments for commandline: ' . $cmdline . ' ' . json_encode($args));
        }

        $process = Process::fromShellCommandline($cmd, null , $env);
        $process->setTimeout(30);
        $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        //$this->logger->log(LogLevel::INFO, '[' . $time . '] >> ' . $cmd . PHP_EOL);
        $output = '';
        try {
            $prefixNL = '';
            $logger = $this->logger;
            $exitcode = $process->run(function ($type, $buffer) use (&$output, $logger, &$prefixNL) {
                $output .= $buffer;
                if ($type === Process::ERR) {
                    $logger->log(LogLevel::ERROR, $buffer);
                } else {
                    $logger->log(LogLevel::INFO, $buffer);
                }
                $prefixNL = substr($buffer, -1) !== PHP_EOL ? PHP_EOL : '';
            });

            $this->logger->log(LogLevel::INFO, $prefixNL . 'EXIT Code: ' . $exitcode . ($exitcode == 0 ? ' (success)' : ' (failure)') . PHP_EOL . PHP_EOL);
            return $output;
        } catch (ProcessTimedOutException $e) {
            $this->logger->log(LogLevel::ERROR, sprintf('Exception: (%s) %s ', get_class($e), $e->getMessage()));
            return false;
        } catch (ProcessFailedException $e) {
            $this->logger->log(LogLevel::ERROR, sprintf('Exception: (%s) %s ', get_class($e), $e->getMessage()));
            return false;
        }

        return false;
    }

}
