<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    $typo3Version = (defined('TYPO3_version'))
        ? TYPO3_version
        : \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getVersion();

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Core\Utility\GeneralUtility']['moveUploadedFile'][]
        = \Qbus\Pdfclean\Hooks\GeneralUtilityHook::class . '->processMoveUploadedFile';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['Qbus\Pdfclean\Updates\CleanExistingPDF']
        = \Qbus\Pdfclean\Updates\CleanExistingPDF::class;

    // The following hook has been removed with v10:
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processUpload']['pdfclean']
        = \Qbus\Pdfclean\Hooks\DataHandlerHook::class;

    // The following hooks/signal have been deprecated in 10.2 and removed with v11:
    // As a replacement for the deprecated signals according PSR-14 events have been added, see Configuration/Services.yaml.
    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($typo3Version) < 1002000) {
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher
            ->connect(
                \TYPO3\CMS\Core\Resource\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFileAdd,
                \Qbus\Pdfclean\SignalSlot\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFileAdd
            );
        $signalSlotDispatcher
            ->connect(
                \TYPO3\CMS\Core\Resource\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFileReplace,
                \Qbus\Pdfclean\SignalSlot\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreFileReplace
            );
        $signalSlotDispatcher
            ->connect(
                \TYPO3\CMS\Core\Resource\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileSetContents,
                \Qbus\Pdfclean\SignalSlot\ResourceStorage::class,
                \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFileSetContents
            );
    }
});
