<?php

namespace Drupal\paragraphs_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\paragraphs_browser\BrowserGroupItem;
use Drupal\paragraphs_browser\BrowserGroupList;
use Drupal\paragraphs_browser\BrowserTypeInterface;

/**
 * Defines the ParagraphsType entity.
 *
 * @ConfigEntityType(
 *   id = "paragraphs_browser_type",
 *   label = @Translation("Paragraphs browser type"),
 *   handlers = {
 *     "list_builder" = "Drupal\paragraphs_browser\Controller\BrowserListBuilder",
 *     "form" = {
 *       "add" = "Drupal\paragraphs_browser\Form\BrowserTypeForm",
 *       "edit" = "Drupal\paragraphs_browser\Form\BrowserTypeForm",
 *       "groups" = "Drupal\paragraphs_browser\Form\BrowserGroupsForm",
 *       "group_add" = "Drupal\paragraphs_browser\Form\GroupAddForm",
 *       "delete" = "Drupal\paragraphs_browser\Form\BrowserTypeDeleteConfirm"
 *     }
 *   },
 *   config_prefix = "paragraphs_browser_type",
 *   admin_permission = "administer paragraphs browser",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "groups",
 *     "map",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/paragraphs_type/browsers/{paragraphs_browser_type}/edit",
 *     "delete-form" = "/admin/structure/paragraphs_type/browsers/{paragraphs_browser_type}/delete",
 *     "groups-form" = "/admin/structure/paragraphs_type/browsers/{paragraphs_browser_type}/groups",
 *     "collection" = "/admin/structure/paragraphs_type/browsers",
 *   }
 * )
 */
class BrowserType extends ConfigEntityBase implements BrowserTypeInterface
{

  /**
   * The ParagraphsType ID.
   *
   * @var string
   */
  public $id;


  /**
   * An array of options configuring this index.
   *
   * @var array
   *
   * @see getOptions()
   */
  protected $groups = array();


  /**
   * Array of groups
   *
   * On presave, items here are saved back to $groups;
   *
   * @var \Drupal\paragraphs_browser\BrowserGroupList|null
   */
  protected $groupList;

  /**
   * An array of options configuring this index.
   *
   * @var array
   *
   * @see getOptions()
   */
  public $map = array();

  /**
   * The ParagraphsType label.
   *
   * @var string
   */
  public $label;

  protected function initGroupList() {
    if(!isset($this->groupList)) {
      $this->groupList = new BrowserGroupList();
      foreach($this->groups as $id => $group) {
        $this->groupList->addGroup($id, $group['label'], $group['weight']);
      }
    }
  }
//
//  public function getGroup($id) {
//    $this->initGroupList();
//    return $this->groupList->getGroup($id);
//  }
//
//
//  public function addGroup($id, $name, $weight = null) {
//    $this->initGroupList();
//    $this->groupList->addGroup($id, $name, $weight);
//  }

  public function groupManager() {
    $this->initGroupList();
    return $this->groupList;
  }

//  public function setGroups(BrowserGroupList $groups) {
//    $this->groupList = $groups;
//  }

//  public function removeGroup($id) {
//    $this->initGroupList();
//    $this->groupList->removeGroup($id);
//  }

  public function getGroupMap($paragraph_type_id) {
    if(isset($this->map[$paragraph_type_id])) {
      return $this->map[$paragraph_type_id];
    }

    return NULL;
  }

  public function setGroupMap($paragraph_type_id, $group_machine_name) {
    $this->map[$paragraph_type_id] = $group_machine_name;
  }

  public function removeGroupMap($paragraph_type_id) {
    unset($this->map[$paragraph_type_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $this->groups = array();
    /** @var BrowserGroupItem $group */
    foreach($this->groupManager()->getGroups() as $group) {
      $this->groups[$group->getId()] = $group->toArray();
    }
    uasort($this->groups, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $this->initGroupList();
    return parent::save();
  }

}
