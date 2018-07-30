<?php

namespace Drupal\download_all_files\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\download_all_files\Plugin\Archiver\Zip;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
* Class DownloadController.
*
* @package Drupal\download_all_files\Controller
*/
class DownloadController extends ControllerBase {

  /**
   * Method archive all file associated with node and stream it for download.
   *
   * @param $node_id
   *   Node id.
   * @param $field_name
   *   Node file field name.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function downloadAllFiles($node_id, $field_name) {
    $node = Node::load($node_id);
    $zip_files_directory = DRUPAL_ROOT . '/sites/default/files/daf_zips';
    $file_path = $zip_files_directory . '/' . $node->getTitle() . ' - ' . $field_name . '.zip';

    // If zip file is already present and node is not been changed since
    // Then just stream it directly.
    if (file_exists($file_path)) {
      $file_last_modified = filemtime($file_path);
      $node_changed = $node->getChangedTime();
      if ($node_changed < $file_last_modified) {
        return $this->streamZipFile($file_path);
      }
    }

    $redirect_on_error_to = empty($_SERVER['HTTP_REFERER']) ? '/' : $_SERVER['HTTP_REFERER'];
    $files = [];

    // Construct zip archive and add all files, then stream it.
    $node_field_files = $node->get($field_name)->getValue();
    foreach ($node_field_files as $file) {
      $file_obj = File::load($file['target_id']);
      if ($file_obj) {
        $files[] = $file_obj->getFileUri();
      }
    }

    $file_zip = NULL;
    if (file_prepare_directory($zip_files_directory, FILE_CREATE_DIRECTORY)) {
      foreach ($files as $file) {
        $file = \Drupal::service('file_system')->realpath($file);
        if (!$file_zip instanceof Zip) {

          $file_zip = new Zip($file_path);
        }
        $file_zip->add($file);
      }

      if ($file_zip instanceof Zip) {
        $file_zip->close();
        return $this->streamZipFile($file_path);
      }
      else {
        drupal_set_message('No files found for this node to be downloaded', 'error', TRUE);
        return new RedirectResponse($redirect_on_error_to);
      }
    }
    else{
      drupal_set_message('Zip file directory not found.', 'error', TRUE);
      return new RedirectResponse($redirect_on_error_to);
    }
  }

  /**
   * Method to stream created zip file.
   *
   * @param $file_path
   *   File physical path.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  protected function streamZipFile($file_path) {
    $binary_file_response = new BinaryFileResponse($file_path);
    $binary_file_response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file_path));

    return $binary_file_response;
  }

}
