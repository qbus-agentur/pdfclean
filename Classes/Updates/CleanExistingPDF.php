<?php

/*
 * This file is part of the package qbus/pdfclean.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Qbus\Pdfclean\Updates\v9;

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

use Qbus\Pdfclean\Service\UpdateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class CleanExistingPDF
 */
class CleanExistingPDF implements UpgradeWizardInterface, ConfirmableInterface
{
    protected $confirmation;

    public function __construct()
    {
        $this->confirmation = new Confirmation(
            'Are you really sure?',
            $this->getDescription(),
            false,
            'yes, please sanitize',
            'no, don\'t sanitize',
            false
        );
    }

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        // this is the origin class name to prevent trouble with class renaming
        return 'Qbus\Pdfclean\Updates\CleanExistingPNG';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return '[EXT:pdfclean] Scan and clean existing PDF files in fileadmin folder';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'This upgrade wizard will sanitize all PDF file in the fileadmin folder.'
            . ' This means that the content of your PDF files will be changed. This automatic process can break your PDF files.'
            . ' PLEASE: Create a backup of your PDF files, before starting this wizard!'
            . ' Are you really sure, you want to do this now?';
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        return true;
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        return GeneralUtility::makeInstance(UpdateService::class)->executeUpdate();
    }

    /**
     * Returns an array of class names of Prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * Return a confirmation message instance
     *
     * @return Confirmation
     */
    public function getConfirmation(): Confirmation
    {
        return $this->confirmation;
    }
}
