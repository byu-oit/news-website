<?php

namespace Drupal\download_all_files\Plugin\Archiver;

use Drupal\Core\Archiver\Zip as BaseZip;
use Drupal\Core\Archiver\ArchiverException;

/**
 * Defines an archiver implementation for .zip files.
 *
 * @Archiver(
 *   id = "DownloadAllFileZip",
 *   title = @Translation("Download all files zip"),
 *   description = @Translation("Handles zip files for download all files."),
 *   extensions = {"zip"}
 * )
 */
class Zip extends BaseZip {

  /**
   * {@inheritdoc}
   */
  public function __construct($file_path) {
    $this->zip = new \ZipArchive();
    if ((file_exists($file_path) && $this->zip->open($file_path, \ZipArchive::OVERWRITE) !== TRUE) || $this->zip->open($file_path, \ZipArchive::CREATE) !== TRUE) {
      throw new ArchiverException(t('Cannot open %file_path', array('%file_path' => $file_path)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function add($file_path) {
    $local_name = basename($file_path);
    $this->zip->addFile($file_path, $local_name);

    return $this;
  }

  /**
   * Method to close the opened archive file.
   */
  public function close() {
    $this->zip->close();
  }
}
