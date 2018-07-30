<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 9/29/16
 * Time: 8:39 AM
 */

namespace Drupal\paragraphs_browser;


class BrowserGroupItem {

  protected $id;

  protected $label;

  protected $weight;


  public function __construct($machine_name, $label, $weight = 0) {
    $this->setId($machine_name);
    $this->setLabel($label);
    $this->setWeight($weight);
  }

  public function getLabel() {
     return $this->label;
  }

  public function setLabel($value) {
     $this->label = $value;
  }

  public function getWeight() {
    return is_numeric($this->weight) ? $this->weight : 0;
  }

  public function setWeight($value) {
    $this->weight = $value;
  }

  public function getId() {
    return $this->id;
  }

  protected function setId($machine_name) {
    if(empty($this->id)) {
      $this->id = $machine_name;
    }
  }

  public function toArray() {
    return array(
      'label' => $this->getLabel(),
      'id' => $this->getId(),
      'weight' => $this->getWeight(),
    );
  }

}