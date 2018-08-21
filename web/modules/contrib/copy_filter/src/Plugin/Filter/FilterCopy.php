<?php

namespace Drupal\copy_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *  id = "filter_copy",
 *  title = @Translation("Copy Filter"),
 *  description = @Translation("Remove extraneous formatting when copying from 3rd party text editors."),
 *  typo = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class FilterCopy extends FilterBase {
    public function process($text, $langcode) {
        $new_text = preg_replace('\'(\\sstyle=".*?")|(<p.*> <\\/p>)\'', '', $text);
        return new FilterProcessResult($new_text);
    }
}