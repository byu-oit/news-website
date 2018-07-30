<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 9/29/16
 * Time: 8:42 AM
 */

namespace Drupal\paragraphs_browser;


class BrowserGroupList {

  protected $groups = array();

  /**
   * Returns list of paragraphs groups
   *
   * @return array
   */
  public function getGroups() {
    return $this->groups;
  }

  /**
   * @return array
   */
  public function getDisplayGroups() {
    $groups = $this->groups;
    $groups['_na'] = new BrowserGroupItem('_na', 'Other');
    return $groups;
  }

  /**
   * @param array $groups
   */
  public function setGroups($groups) {
    foreach($groups as $group) {
      if($group instanceof BrowserGroupItem) {
        $this->setGroup($group);
      }
    }
  }

  /**
   * @param integer $id
   *
   * @return \Drupal\paragraphs_browser\BrowserGroupItem
   */
  public function getGroup($id) {
    return isset($this->groups[$id]) ? $this->groups[$id] : null;
  }

  /**
   * @param \Drupal\paragraphs_browser\BrowserGroupItem $group
   */
  public function setGroup(BrowserGroupItem $group) {
    $this->groups[$group->getId()] = $group;
  }

  /**
   * Adds group to end of groups list, resets weight to heaviest.
   *
   * @param string $machine_name
   * @param string $label
   * @param integer $weight
   *
   * @return \Drupal\paragraphs_browser\BrowserGroupItem
   */
  public function addGroup($machine_name, $label, $weight = null) {
    if(is_null($weight)) {
      $weight = ($last_group = $this->getLastGroup()) ? $last_group->getWeight() + 1 : 0;
    }
    $this->groups[$machine_name] = new BrowserGroupItem($machine_name, $label, $weight);
    return $this->groups[$machine_name];
  }

  /**
   * Removes a group
   *
   * @param $id
   */
  public function removeGroup($id) {
    unset($this->groups[$id]);
  }

  /**
   * Gets the last group in the list
   *
   * @return \Drupal\paragraphs_browser\BrowserGroupItem
   */
  public function getLastGroup() {
    foreach($this->getGroups() as $group) {
      if(!isset($weight)) {
        $weight = $group->getWeight();
        $last = $group;
      }
      elseif ($group->getWeight() > $weight) {
        $weight = $group->getWeight();
        $last = $group;
      }
    }
    return isset($last) ? $last : null;
  }
}