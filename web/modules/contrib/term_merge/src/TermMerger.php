<?php

namespace Drupal\term_merge;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Implements TermMergerInterface to provide a term merger service.
 */
class TermMerger implements TermMergerInterface {

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $termStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $entityTypeBundleInfo;

  /**
   * TermMerger constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;

    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function mergeIntoNewTerm(array $termsToMerge, $newTermLabel) {
    $this->validateTerms($termsToMerge);

    $firstTerm = reset($termsToMerge);
    $values = [
      'name' => $newTermLabel,
      'vid' => $firstTerm->bundle(),
    ];

    /** @var \Drupal\taxonomy\TermInterface $newTerm */
    $newTerm = $this->termStorage->create($values);

    $this->mergeIntoTerm($termsToMerge, $newTerm);

    return $newTerm;
  }

  /**
   * {@inheritdoc}
   */
  public function mergeIntoTerm(array $termsToMerge, TermInterface $targetTerm) {
    $this->validateTerms($termsToMerge);

    // We have to save the term to make sure we've got an id to reference.
    if ($targetTerm->isNew()) {
      $targetTerm->save();
    }

    $firstTerm = reset($termsToMerge);
    if ($firstTerm->bundle() !== $targetTerm->bundle()) {
      throw new \RuntimeException('The target term must be in the same vocabulary as the terms being merged');
    }

    $this->migrateReferences($termsToMerge, $targetTerm);

    $this->termStorage->delete($termsToMerge);
  }

  /**
   * Asserts that all passed in terms are valid.
   *
   * @param \Drupal\taxonomy\TermInterface[] $termsToAssert
   *   The array to assert.
   */
  private function validateTerms(array $termsToAssert) {
    $this->assertTermsNotEmpty($termsToAssert);
    $this->assertAllTermsHaveSameVocabulary($termsToAssert);
  }

  /**
   * Asserts that all terms have the same vocabulary.
   *
   * @param \Drupal\taxonomy\TermInterface[] $termsToAssert
   *   The array to assert.
   */
  private function assertAllTermsHaveSameVocabulary(array $termsToAssert) {
    $vocabulary = '';

    foreach ($termsToAssert as $term) {
      if (empty($vocabulary)) {
        $vocabulary = $term->bundle();
      }

      if ($vocabulary !== $term->bundle()) {
        throw new \RuntimeException('Only merges within the same vocabulary are supported');
      }
    }
  }

  /**
   * Asserts that the termsToAssert variable is not empty.
   *
   * @param \Drupal\taxonomy\TermInterface[] $termsToAssert
   *   The array to assert.
   */
  private function assertTermsNotEmpty(array $termsToAssert) {
    if (empty($termsToAssert)) {
      throw new \RuntimeException('You must provide at least 1 term');
    }
  }

  /**
   * Updates the term references on all entities referencing multiple terms.
   *
   * @param \Drupal\taxonomy\TermInterface[] $fromTerms
   *   The terms to migrate away from.
   * @param \Drupal\taxonomy\TermInterface $toTerm
   *   The term to migrate to.
   */
  private function migrateReferences(array $fromTerms, TermInterface $toTerm) {
    foreach ($fromTerms as $fromTerm) {
      $this->migrateReference($fromTerm, $toTerm);
    }
  }

  /**
   * Updates the term reference on all entities from the old to the new.
   *
   * @param \Drupal\taxonomy\TermInterface $fromTerm
   *   The term to migrate away from.
   * @param \Drupal\taxonomy\TermInterface $toTerm
   *   The term to migrate to.
   */
  private function migrateReference(TermInterface $fromTerm, TermInterface $toTerm) {
    $referenceFieldNames = $this->findTermReferenceFieldNames();
    $referencingEntities = $this->loadReferencingEntities($fromTerm);

    foreach ($referencingEntities as $entity) {
      foreach ($referenceFieldNames as $fieldName) {
        $values = $entity->{$fieldName}->getValue();
        if (empty($values)) {
          continue;
        }

        $referenceUpdated = FALSE;
        foreach ($values as &$value) {
          if ($value['target_id'] !== $fromTerm->id()) {
            continue;
          }

          $referenceUpdated = TRUE;
          $value['target_id'] = $toTerm->id();
        }

        if (!$referenceUpdated) {
          continue;
        }

        $entity->{$fieldName}->setValue($values);
        $entity->save();
      }
    }
  }

  /**
   * Finds all names of term reference fields.
   *
   * @return string[]
   *   Array of entity reference field names for fields that reference taxonomy
   *   terms.
   */
  private function findTermReferenceFieldNames() {
    $fieldNames = [];

    foreach ($this->findTermReferenceFields() as $bundle) {
      foreach ($bundle as $fieldsInBundle) {
        $fieldNames = array_merge($fieldNames, $fieldsInBundle);
      }
    }

    return $fieldNames;
  }

  /**
   * Finds all term reference fields.
   *
   * @return array
   *   Nested array of field names for taxonomy term entity reference fields.
   *   [entity type id][bundle id] = array of field names.
   */
  private function findTermReferenceFields() {
    $termReferenceFields = [];

    $entityTypes = $this->entityTypeManager->getDefinitions();
    foreach ($entityTypes as $entityType) {
      if (!$entityType->entityClassImplements(FieldableEntityInterface::class)) {
        continue;
      }

      $referenceFields = $this->findTermReferenceFieldsForEntityType($entityType->id());
      if (empty($referenceFields)) {
        continue;
      }

      $termReferenceFields[$entityType->id()] = $referenceFields;
    }

    return $termReferenceFields;
  }

  /**
   * Finds all term reference fields for a given entity type.
   *
   * @param string $entityType
   *   The entity type name.
   *
   * @return array
   *   The term reference fields keyed by their respective bundle.
   */
  private function findTermReferenceFieldsForEntityType($entityType) {
    $bundleNames = array_keys($this->entityTypeBundleInfo->getBundleInfo($entityType));

    $referenceFields = [];
    foreach ($bundleNames as $bundleName) {
      $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions($entityType, $bundleName);
      foreach ($fieldDefinitions as $fieldDefinition) {
        if ($fieldDefinition->getType() !== 'entity_reference') {
          continue;
        }

        if ($fieldDefinition->getSetting('target_type') !== 'taxonomy_term') {
          continue;
        }

        if ($fieldDefinition->isComputed()) {
          continue;
        }

        // Exclude parent fields because they cause fatal errors during the
        // query. This is because they are currently a special case.
        // @see https://www.drupal.org/node/2543726
        if ($fieldDefinition->getName() === 'parent') {
          continue;
        }

        $referenceFields[$bundleName][] = $fieldDefinition->getName();
      }
    }

    return $referenceFields;
  }

  /**
   * Loads all entities with a reference to the given term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to find references to.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   All entities referencing this term.
   */
  private function loadReferencingEntities(TermInterface $term) {
    $referenceFields = $this->findTermReferenceFields();

    $referencingEntities = [];

    foreach ($referenceFields as $entityType => $bundles) {
      foreach ($bundles as $bundle) {
        foreach ($bundle as $fieldName) {
          $entities = $this->entityTypeManager->getStorage($entityType)
            ->loadByProperties([$fieldName => $term->id()]);
          $referencingEntities = array_merge($referencingEntities, $entities);
        }
      }
    }

    return $referencingEntities;
  }

}
