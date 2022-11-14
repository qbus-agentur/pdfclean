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

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use TYPO3\CMS\Core\Type\File\FileInfo;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PdfCleanService
 */
class PdfCleanService
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $possibleMimeTypes = ['application/pdf'];

    public function __construct()
    {
        //$this->logger = new NullLogger;
        $this->logger = new class extends AbstractLogger
        {
            public function log($level, $message, $context = [])
            {
                error_log('[' . $level . '] ' . $this->interpolate($message, $context));
            }

            private function interpolate($message, array $context = array())
            {
                // build a replacement array with braces around the context keys
                $replace = array();
                foreach ($context as $key => $val) {
                    // check that the value can be cast to string
                    if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                        $replace['{' . $key . '}'] = $val;
                    }
                }

                // interpolate replacement values into the message and return
                return strtr($message, $replace);
            }
        };
    }

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
    public function cleanPdfFile($fileNameAndPath, $outputFileNameAndPath = null)
    {
        if ($outputFileNameAndPath === null) {
            $outputFileNameAndPath = $fileNameAndPath;
        }

        $intermediateFile = GeneralUtility::tempnam('pdf_intermediate_');
        $cleanedFile = GeneralUtility::tempnam('pdf_cleaned_');

        $this->run('exiftool -all= -Author= -tagsfromfile @ -title -keywords -subject -description %s -o - > %s', $fileNameAndPath, $intermediateFile);
        $this->run('qpdf --linearize %s %s', $intermediateFile, $cleanedFile);
        $this->run('cp -f %s %s', $cleanedFile, $outputFileNameAndPath);
        GeneralUtility::unlink_tempfile($intermediateFile);
        GeneralUtility::unlink_tempfile($cleanedFile);
    }

    /**
     * @param string $dirtyPDF
     * @return string
     * @throws \BadFunctionCallException
     */
    public function cleanAndReturnPdfContent($dirtyPDF)
    {
        $tmpFile = GeneralUtility::tempnam('pdf_clean_tmp_');
        file_put_contents($tmpFile, $dirtyPDF);
        $this->cleanPdfFile($tmpFile);
        $cleanedPDF = file_get_contents($tmpFile);
        GeneralUtility::unlink_tempfile($tmpFile);

        return $cleanedPDF;
    }

    protected function run($cmdline, ...$args) {
        $cmd = vsprintf($cmdline, array_map('escapeshellarg', $args));
        if ($cmd === false) {
            throw new \Exception('Invalid arguments for commandline: ' . $cmdline . ' ' . json_encode($args));
        }

        if (is_callable([Process::class, 'fromShellCommandLine'])) {
            $process = Process::fromShellCommandline($cmd);
        } else {
            $process = new Process($cmd);
        }
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
