# PDF exif meta data cleaner

This extension will clean any PDF file uploaded to the TYPO3 system, but only with the default options.
Please read the following section carefully for all details.

## Important to know

This extension removes all metadata information in uploaded PDF files.
It requires exiftool and qpdf command line utilities to be available:

.. code-block:: bash

    vendor/bin/typo3 extension:activate nginx_cache

    # Fedora (RPM)
    sudo dnf install perl-Image-ExifTool qpdf

    # Debian (dpkg)
    sudo apt install exiftool qpdf


## What this extension does

- Hooks into FAL API: ``ResourceFactory::addFile()`` and ``ResourceFactory::replaceFile()``
- Hooks into FAL API: ``ResourceStorage::setFileContents()``
- Hooks into DataHandler: Handling files for group/select function
- Hooks into ``GeneralUtility::upload_copy_move()``
- Hooks into ``GeneralUtility::upload_to_tempfile()``
- Provide an upgrade wizard for existing PDF files (please read the warnings in the upgrade wizard carefully)

## WARNING

This extension can only sanitize the files if the upload is done in one of the ways described above.
For example, if a third-party extension allows to upload files and does not use the core APIs described above, the PDF cleaner will not run.

## Credits

Thanks to the TYPO3 GmbH and their [t3g/svg-sanitizer](https://github.com/TYPO3GmbH/svg_sanitizer) extension which was the technical basis for this extension.
