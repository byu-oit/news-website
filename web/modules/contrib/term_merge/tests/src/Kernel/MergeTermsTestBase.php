<?php

namespace Drupal\Tests\term_merge\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\taxonomy\Functional\TaxonomyTestTrait;
use Drupal\Tests\term_merge\Kernel\Form\MergeTermsTargetTest;

abstract class MergeTermsTestBase extends KernelTestBase {

  use TaxonomyTestTrait {
    TaxonomyTestTrait::createVocabulary as traitCreateVocabulary;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'term_merge',
    'taxonomy',
    'text',
    'user',
    'system',
  ];

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * @var \Drupal\taxonomy\TermInterface[]
   */
  protected $terms;

  /**
   * @return \Drupal\taxonomy\Entity\Vocabulary
   *   The created vocabulary
   */
  public function createVocabulary() {
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    $vocabulary = $this->traitCreateVocabulary();
    return $vocabulary;
  }

  /**
   * @return int
   *   The number of terms that should be set up by the setUp function.
   */
  abstract protected function numberOfTermsToSetUp();

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['filter']);
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installSchema('system', ['key_value_expire']);

    $accountProxy = new AccountProxy();
    $account = self::getMock(AccountInterface::class);
    $account->method('id')->willReturn(24);
    /** @var \Drupal\Core\Session\AccountInterface $account */
    $accountProxy->setAccount($account);
    \Drupal::getContainer()->set('current_user', $accountProxy);
    $this->privateTempStoreFactory = \Drupal::service('user.private_tempstore');

    $this->entityTypeManager = \Drupal::entityTypeManager();

    $this->vocabulary = $this->createVocabulary();

    $this->createTerms($this->numberOfTermsToSetUp());
  }

  /**
   * Prepares the target provided by mergeTermFunctionsProvider for use.
   *
   * Dataproviders run before the tests are set up and are therefore unable to
   * create proper taxonomy terms. Which means we'll have to do so in the test.
   *
   * @param string $target
   *
   * @return \Drupal\taxonomy\Entity\Term|string
   *   A newly created term if the target was an empty string, the original
   *   string otherwise.
   */
  protected function prepareTarget($target) {
    if (!empty($target)) {
      return $target;
    }

    return $this->createTerm($this->vocabulary);
  }

  /**
   * Asserts whether a given formState has it's redirect set to a given route.
   *
   * @param \Drupal\Core\Form\FormState $formState
   * @param $routeName
   * @param $vocabularyId
   */
  protected function assertRedirect(Formstate $formState, $routeName, $vocabularyId) {
    $routeParameters['taxonomy_vocabulary'] = $vocabularyId;
    $expected = new Url($routeName, $routeParameters);
    KernelTestBase::assertEquals($expected, $formState->getRedirect());
  }

  /**
   * @param $count
   */
  protected function createTerms($count) {
    for ($i = 0; $i < $count; $i++) {
      $term = $this->createTerm($this->vocabulary);
      $this->terms[$term->id()] = $term;
    }
  }

}
