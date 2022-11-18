# PDF exif meta data cleaner

This extension will clean any PDF file uploaded to the TYPO3 system, but only with the default options.
Please read the following section carefully for all details.

## Important to know

This extension removes all metadata information in uploaded PDF files.
It requires exiftool and qpdf command line utilities to be available:


```sh
# ddev
ddev config --webimage-extra-packages=libimage-exiftool-perl,qpdf

# Fedora (RPM)
sudo dnf install perl-Image-ExifTool qpdf

# Debian (dpkg)
sudo apt install libimage-exiftool-perl qpdf
```

## Update wizard

Running the update wizard processes roughly 10gb/hour, so make sure
to run it via SSH/CLI.

Make a backup first:

```sh
mkdir -p ../fileadmin_pdf_backup/
rsync -avz --include '*.pdf' --exclude '*.*' fileadmin/ ../fileadmin_pdf_backup/
```

Then run the wizard:

```sh
php -d memory_limit=1G -d error_log=syslog \
    typo3/sysext/core/bin/typo3 upgrade:run 'Qbus\Pdfclean\Updates\CleanExistingPDF'
```

Also make sure to disable extensions that hook into FAL operations
and clear caches in between, they will result in a massive slowdown.
(Example: `fs_media_gallery`)

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

Thanks to the TYPO3 GmbH and their
[t3g/svg-sanitizer](https://github.com/TYPO3GmbH/svg_sanitizer) extension which
provided the technical basis for this extension.
