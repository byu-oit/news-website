<?php

namespace Drupal\Tests\term_merge\Kernel\TestDoubles;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

class TermMergerMock extends TermMergerDummy {

  public function mergeIntoNewTerm(array $termsToMerge, $newTermLabel) {
    return new Term([], 'taxonomy_term');
  }

}
