<?php

namespace Drupal\imageapi_optimize_binaries;


/**
 * Storage controller class for "image optimize pipeline" configuration entities.
 */
class ShellOperations implements ImageAPIOptimizeShellOperationsInterface {

  /**
   * Search the local system for the given executable binary.
   *
   * @param null $executable
   *   The name of the executable binary to find on the local system. If not
   *   specified the default executeable name for this class will be used.
   *
   * @return string|false
   *   The path to the binary on the local system, or FALSE if it could not be
   *   located.
   */
  public function findExecutablePath($executable = NULL) {
    $output = array();
    $return_var = 0;
    $path = exec('which ' . escapeshellarg($executable), $output, $return_var);
    if ($return_var == 0) {
      return $path;
    }
    return FALSE;
  }

  /**
   * Execute a shell command on the local system.
   *
   * @param $command
   *   The command to execute.
   * @param $options
   *   An array of options for the command. This will not be escaped before executing.
   * @param $arguments
   *   An array of arguments for the command. These will be escaped.
   *
   * @return bool
   *   Returns TRUE if the command completed successfully, FALSE otherwise.
   */
  public function execShellCommand($command, $options, $arguments) {
    $output = array();
    $return_val = 0;
    $option_string = implode(' ', $options);
    $argument_string = implode(' ', array_map('escapeshellarg', $arguments));
    exec(escapeshellcmd($command) . ' ' . $option_string . ' ' . $argument_string, $output, $return_val);

    if ($return_val == 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function saveCommandStdoutToFile($cmd, $dst) {
    $return_val = 0;
    ob_start();
    passthru($cmd);
    $output = ob_get_contents();
    ob_end_clean();

    file_unmanaged_save_data($output, $dst, FILE_EXISTS_REPLACE);

    if ($return_val == 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
