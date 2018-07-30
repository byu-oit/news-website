<?php

namespace Drupal\Tests\term_merge\Kernel\Form;

use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\term_merge\Form\MergeTermsConfirm;
use Drupal\Tests\term_merge\Kernel\MergeTermsTestBase;
use Drupal\Tests\term_merge\Kernel\TestDoubles\TermMergerDummy;
use Drupal\Tests\term_merge\Kernel\TestDoubles\TermMergerSpy;

/**
 * @group term_merge
 */
class MergeTermsConfirmTest extends MergeTermsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    \Drupal::getContainer()->set('term_merge.term_merger', new TermMergerDummy());
  }

  /**
   * @return array
   */
  public function selectedTermsProvider() {

    $testData['no terms new target'] = [
      'terms' => [],
      'target' => 'New term',
    ];

    $testData['no terms existing target'] = [
      'terms' => [],
      'target' => '',
    ];

    $testData['one term new target'] = [
      'terms' => [1],
      'target' => 'New term',
    ];

    $testData['one term existing target'] = [
      'terms' => [1],
      'target' => '',
    ];

    $testData['two terms new target'] = [
      'terms' => [1, 2],
      'target' => 'New term',
    ];

    $testData['two terms existing target'] = [
      'terms' => [1, 2],
      'target' => '',
    ];

    $testData['three terms new target'] = [
      'terms' => [1, 2, 3],
      'target' => 'New term',
    ];

    $testData['three terms existing target'] = [
      'terms' => [1, 2, 3],
      'target' => '',
    ];

    $testData['four terms new target'] = [
      'terms' => [1, 2, 3, 4],
      'target' => 'New term',
    ];

    $testData['four terms existing target'] = [
      'terms' => [1, 2, 3, 4],
      'target' => '',
    ];

    return $testData;
  }

  /**
   * @test
   * @dataProvider selectedTermsProvider
   **/
  public function titleCallback(array $selectedTerms) {
    $sut = $this->createSubjectUnderTest();
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $selectedTerms);

    $expected = new TranslatableMarkup('Are you sure you wish to merge %termCount terms?', ['%termCount' => count($selectedTerms)]);
    self::assertEquals($expected, $sut->titleCallback());
  }

  /**
   * @test
   * @dataProvider selectedTermsProvider
   */
  public function buildForm(array $selectedTerms, $target) {
    $target = $this->prepareTarget($target);
    $sut = $this->createSubjectUnderTest();
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $selectedTerms);
    $this->privateTempStoreFactory->get('term_merge')->set('target', $target);

    $actual = $sut->buildForm([], new FormState(), $this->vocabulary);

    if (count($selectedTerms) < 2) {
      self::assertEquals([], $actual);
      $this->assertSingleErrorMessage(new TranslatableMarkup("You must submit at least two terms."));
    }
    else {
      $this->assertConfirmationForm($selectedTerms, $actual, $target);
    }
  }

  /**
   * @param array $selectedTerms
   * @param $actual
   */
  private function assertConfirmationForm(array $selectedTerms, $actual, $target) {
    $items = [];
    foreach ($selectedTerms as $termIndex) {
      $items[] = $this->terms[$termIndex]->label();
    }

    $newOrExisting = is_string($target) ? 'new' : 'existing';
    $termLabel = is_string($target) ? $target : $target->label();

    $expected = [
      'message' => [
        '#markup' => new TranslatableMarkup("You are about to merge %termCount terms into {$newOrExisting} term %termName. This action can't be undone. Are you sure you wish to continue with merging the terms below?", ['%termCount' => count($selectedTerms), '%termName' => $termLabel]),
      ],
      'terms' => [
        '#title' => new TranslatableMarkup("Terms to be merged"),
        '#theme' => 'item_list',
        '#items' => $items,
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => new TranslatableMarkup('Confirm merge'),
      ],
    ];

    self::assertEquals($expected, $actual);
  }

  /**
   * @param string $expectedMessage
   */
  private function assertSingleErrorMessage($expectedMessage) {
    $messages = drupal_get_messages();
    $errorMessages = $messages['error'];

    self::assertCount(1, $messages);
    self::assertEquals($expectedMessage, array_pop($errorMessages));
  }

  /**
   * @test
   * @expectedException \LogicException
   * @expectedExceptionMessage Invalid target type. Should be string or implement TermInterface
   **/
  public function IncorrectTargetThrowsException() {
    $sut = $this->createSubjectUnderTest();

    $this->privateTempStoreFactory->get('term_merge')->set('terms', [1,2]);
    $this->privateTempStoreFactory->get('term_merge')->set('target', (object) []);

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);
    $sut->submitForm($build, $formState);
  }

  public function termMergerMethodProvider() {
    $methods['new term'] = [
      'methodName' => 'mergeIntoNewTerm',
      'target' => 'New term',
    ];

    $methods['existing term'] = [
      'methodName' => 'mergeIntoTerm',
      'target' => '',
    ];

    return $methods;
  }

  /**
   * @test
   * @dataProvider termMergerMethodProvider
   **/
  public function submitFormInvokesCorrectTermMergerMethod($methodName, $target) {
    $termMergerSpy = new TermMergerSpy();
    \Drupal::getContainer()->set('term_merge.term_merger', $termMergerSpy);
    $sut = $this->createSubjectUnderTest();
    $terms = [reset($this->terms)->id(), end($this->terms)->id()];
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $terms);
    $this->privateTempStoreFactory->get('term_merge')->set('target', $this->prepareTarget($target));

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);

    $sut->submitForm($build, $formState);

    self::assertEquals([$methodName], $termMergerSpy->calledFunctions());
  }

  /**
   * @test
   * @dataProvider termMergerMethodProvider
   **/
  public function submitRedirectsToMergeRoute($methodName, $target) {
    $sut = $this->createSubjectUnderTest();
    $terms = [reset($this->terms)->id(), end($this->terms)->id()];
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $terms);
    $this->privateTempStoreFactory->get('term_merge')->set('target', $this->prepareTarget($target));

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);

    $sut->submitForm($build, $formState);

    $routeName = 'entity.taxonomy_vocabulary.merge_form';
    self::assertRedirect($formState, $routeName, $this->vocabulary->id());
  }

  /**
   * @test
   **/
  public function submitSetsSuccessMessage() {
    $sut = $this->createSubjectUnderTest();
    $terms = [reset($this->terms)->id(), end($this->terms)->id()];
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $terms);
    $this->privateTempStoreFactory->get('term_merge')->set('target', 'Target');

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);

    $sut->submitForm($build, $formState);

    $arguments = [
      '%count' => 2,
      '%target' => 'Target'
    ];
    $expected = [
      'status' => [
        new TranslatableMarkup('Successfully merged %count terms into %target', $arguments),
      ],
    ];

    self::assertEquals($expected, drupal_get_messages('status'));
  }

  /**
   * @return \Drupal\term_merge\Form\MergeTermsConfirm
   */
  private function createSubjectUnderTest() {
    return new MergeTermsConfirm($this->entityTypeManager, $this->privateTempStoreFactory, \Drupal::service('term_merge.term_merger'));
  }

  /**
   * {@inheritdoc}
   */
  protected function numberOfTermsToSetUp() {
    return 4;
  }
}
