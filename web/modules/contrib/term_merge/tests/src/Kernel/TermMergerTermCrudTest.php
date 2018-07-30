<?php

namespace Drupal\Tests\term_merge\Kernel;

use Drupal\taxonomy\TermInterface;
use Drupal\term_merge\TermMerger;

/**
 * @group term_merge
 */
class TermMergerTermCrudTest extends MergeTermsTestBase {

  /**
   * @return array
   */
  public function mergeTermFunctionsProvider() {

    $functions['::mergeIntoNewTerm'] = [
      'methodName' => 'mergeIntoNewTerm',
      'target' => 'new term',
    ];

    $functions['::mergeIntoTerm'] = [
      'methodName' => 'mergeIntoTerm',
      'target' => '',
    ];

    return $functions;
  }

  /**
   * @test
   * @dataProvider mergeTermFunctionsProvider
   * @expectedException \RuntimeException
   * @expectedExceptionMessage Only merges within the same vocabulary are supported
   *
   * @param string $methodName
   * @param string $target
   */
  public function canOnlyMergeTermsInTheSameVocabulary($methodName, $target) {
    $vocab2 = $this->createVocabulary();
    $term3 = $this->createTerm($vocab2);

    $terms = [reset($this->terms), $term3];

    $sut = $this->createSubjectUnderTest();

    $sut->{$methodName}($terms, $this->prepareTarget($target));
  }

  /**
   * @test
   * @dataProvider mergeTermFunctionsProvider
   * @expectedException \RuntimeException
   * @expectedExceptionMessage You must provide at least 1 term
   *
   * @param string $methodName
   * @param string $target
   */
  public function minimumTermsValidation($methodName, $target) {
    $sut = $this->createSubjectUnderTest();

    $sut->{$methodName}([], $this->prepareTarget($target));
  }

  /**
   * @test
   **/
  public function mergeIntoNewTermCreatesNewTerm() {
    $sut = $this->createSubjectUnderTest();

    $termLabel = 'newTerm';
    $term = $sut->mergeIntoNewTerm($this->terms, $termLabel);

    self::assertTrue($term instanceof TermInterface);
    self::assertSame($termLabel, $term->label());
    // Id is only set if the term has been saved.
    self::assertNotNull($term->id());
  }

  /**
   * @test
   * @expectedException \RuntimeException
   * @expectedExceptionMessage The target term must be in the same vocabulary as the terms being merged
   **/
  public function existingTermMustBeInSameVocabularyAsMergedTerms() {
    $sut = $this->createSubjectUnderTest();

    $term = $this->createTerm($this->createVocabulary());

    $sut->mergeIntoTerm($this->terms, $term);
  }

  /**
   * @test
   **/
  public function mergeIntoTermSavesTermIfNewTermIsPassedIn() {
    $sut = $this->createSubjectUnderTest();
    $values = [
      'name' => 'Unsaved term',
      'vid' => $this->vocabulary->id(),
    ];
    /** @var TermInterface $term */
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->create($values);
    self::assertEmpty($term->id());

    $sut->mergeIntoTerm($this->terms, $term);

    self::assertNotEmpty($term->id());
  }

  /**
   * @test
   * @dataProvider mergeTermFunctionsProvider
   *
   * @param string $methodName
   * @param string $target
   */
  public function mergedTermsAreDeleted($methodName, $target) {
    $sut = $this->createSubjectUnderTest();

    $sut->{$methodName}($this->terms, $this->prepareTarget($target));

    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $termIds = array_keys($this->terms);
    self::assertEquals([], $termStorage->loadMultiple($termIds));
  }

  /**
   * @return \Drupal\term_merge\TermMerger
   */
  private function createSubjectUnderTest() {
    $sut = new TermMerger($this->entityTypeManager, \Drupal::service('entity_type.bundle.info'), \Drupal::service('entity_field.manager'));
    return $sut;
  }


  /**
   * {@inheritdoc}
   */
  protected function numberOfTermsToSetUp() {
    return 2;
  }
}
