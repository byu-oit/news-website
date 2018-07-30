<?php

namespace Drupal\Tests\term_merge\Kernel\TestDoubles;

use Drupal\taxonomy\TermInterface;

class TermMergerSpy extends TermMergerMock {

  private $functionCalls = [];

  /**
   * {@inheritdoc}
   */
  public function mergeIntoNewTerm(array $termsToMerge, $newTermLabel) {
    $this->functionCalls[__FUNCTION__] = [$termsToMerge, $newTermLabel];
    return parent::mergeIntoNewTerm($termsToMerge, $newTermLabel);
  }

  /**
   * {@inheritdoc}
   */
  public function mergeIntoTerm(array $termsToMerge, TermInterface $targetTerm) {
    $this->functionCalls[__FUNCTION__] = [$termsToMerge, $targetTerm];
    parent::mergeIntoTerm($termsToMerge, $targetTerm);
  }

  public function assertFunctionCalled($function) {
    if (!isset($this->functionCalls[$function])) {
      throw new \Exception("{$function} was not called");
    }
  }

  public function calledFunctions() {
    return array_keys($this->functionCalls);
  }

}
