<?php

namespace Drupal\Tests\term_merge\Kernel\Form;

use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\term_merge\Form\MergeTermsTarget;
use Drupal\Tests\term_merge\Kernel\MergeTermsTestBase;

/**
 * @group term_merge
 */
class MergeTermsTargetTest extends MergeTermsTestBase {

  /**
   * @test
   **/
  public function hasTitle() {
    $sut = new MergeTermsTarget($this->entityTypeManager, $this->privateTempStoreFactory);

    $expected = new TranslatableMarkup('Please select a target term');

    self::assertEquals($expected, $sut->titleCallback());
  }

  /**
   * @test
   **/
  public function buildsForm() {
    $sut = new MergeTermsTarget($this->entityTypeManager, $this->privateTempStoreFactory);

    $knownTermIds = array_keys($this->terms);
    $selectedTermIds = array_slice($knownTermIds, 0,2);
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $selectedTermIds);

    $options = [];
    foreach ($knownTermIds as $termId) {
      if (in_array($termId, $selectedTermIds)) {
        continue;
      }
      $options[$termId] = $this->terms[$termId]->label();
    }

    $expected = [
      'description' => [
        '#markup' => new TranslatableMarkup('Please enter a new term or select an existing term to merge into.'),
      ],
      'new' => [
        '#type' => 'textfield',
        '#title' => new TranslatableMarkup('New term'),
      ],
      'existing' => [
        '#type' => 'select',
        '#title' => new TranslatableMarkup('Existing term'),
        '#empty_option' => new TranslatableMarkup('Select an existing term'),
        '#options' => $options,
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => new TranslatableMarkup('Submit'),
      ],
    ];

    $actual = $sut->buildForm([], new FormState(), $this->vocabulary);
    self::assertEquals($expected, $actual);
  }

  /**
   * @return string[]
   *   Options that allow the invoking test to know which targets to select.
   */
  public function selectedTargetsProvider() {
    $testData['no target selected'] = ['none'];
    $testData['both targets selected'] = ['both'];

    return $testData;
  }

  /**
   * @test
   * @dataProvider selectedTargetsProvider
   **/
  public function newOrExistingTermMustBeSelected($selectedTerms) {
    $sut = new MergeTermsTarget($this->entityTypeManager, $this->privateTempStoreFactory);

    $knownTermIds = array_keys($this->terms);
    $selectedTermIds = array_slice($knownTermIds, 0,2);
    $this->privateTempStoreFactory->get('term_merge')->set('terms', $selectedTermIds);

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);
    self::assertEmpty($formState->getErrors());

    if ($selectedTerms == 'both') {
      $formState->setValue('new', 'New term');
      $formState->setValue('existing', end($knownTermIds));
    }

    $sut->validateForm($build, $formState);
    $expectedError = new TranslatableMarkup('You must either select an existing term or enter a new term.');
    self::assertEquals(['new' => $expectedError], $formState->getErrors());
  }

  /**
   * @test
   **/
  public function newTermFormSubmission() {
    $sut = new MergeTermsTarget($this->entityTypeManager, $this->privateTempStoreFactory);

    $knownTermIds = array_keys($this->terms);
    $selectedTermIds = array_slice($knownTermIds, 0,2);
    $termMergeCollection = $this->privateTempStoreFactory->get('term_merge');
    $termMergeCollection->set('terms', $selectedTermIds);

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);

    $target = 'newTarget';
    $formState->setValue('new', $target);
    $sut->validateForm($build, $formState);
    $sut->submitForm($build, $formState);

    self::assertSame($target, $termMergeCollection->get('target'));
    $this->assertRedirect($formState, 'entity.taxonomy_vocabulary.merge_confirm', $this->vocabulary->id());
  }

  /**
   * @test
   **/
  public function existingTermSubmission() {
    $sut = new MergeTermsTarget($this->entityTypeManager, $this->privateTempStoreFactory);

    $knownTermIds = array_keys($this->terms);
    $selectedTermIds = array_slice($knownTermIds, 0,2);
    $termMergeCollection = $this->privateTempStoreFactory->get('term_merge');
    $termMergeCollection->set('terms', $selectedTermIds);

    $formState = new FormState();
    $build = $sut->buildForm([], $formState, $this->vocabulary);

    $target = end($knownTermIds);
    $formState->setValue('existing', $target);
    $sut->validateForm($build, $formState);
    $sut->submitForm($build, $formState);

    $targetTerm = $this->entityTypeManager->getStorage('taxonomy_term')->load($target);
    self::assertEquals($targetTerm, $termMergeCollection->get('target'));
    $this->assertRedirect($formState, 'entity.taxonomy_vocabulary.merge_confirm', $this->vocabulary->id());
  }

  /**
   * {@inheritdoc}
   */
  protected function numberOfTermsToSetUp() {
    return 4;
  }
}
