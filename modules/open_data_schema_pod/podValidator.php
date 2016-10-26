<?php

namespace podValidator;

include __DIR__ . '/../../autoload.php';
use JsonSchema\Validator;
class validate {

  function __construct($url) {
    $this->url = $url;
    $this->errors = array();
  }

  public function getDataJSON()
  {
    if (!isset($this->dataset)) {
      $this->dataset = array();
      $data = json_decode(file_get_contents($this->url));
      $this->dataJSON = $data;

      foreach($this->dataJSON->dataset as $dataset) {
        $this->dataset[$dataset->identifier] = $dataset;
      }
      $this->dataJSON->dataset = $this->dataset;
    }
  }

  public function getDataset($id)
  {
    return $this->dataset[$id];
  }

  public function getIdentifiers()
  {
    $this->identifers = array();
    $data = $this->dataJSON;
    foreach ($data->dataset as $dataset) {
      $this->identifiers[] = $dataset->identifier;
    }
  }

  public function process($id) {
    $schemaFolder = DRUPAL_ROOT . '/' . drupal_get_path('module', 'open_data_schema_pod') . '/data/v1.1';
    $data = $this->getDataset($id);
    $validator = new Validator;
    $validator->check($data, (object)['$ref' => 'file://' . $schemaFolder . '/dataset.json']);
    return $validator;
  }

  public function datasetCount()
  {
    $this->getDataJSON();
    return count($this->dataset);
  }

  public function processAll() {
    $this->getDataJSON();
    $this->getIdentifiers();
    $this->validated = array();
    foreach ($this->identifiers as $id) {
      $validator = $this->process($id);

      if ($validator->isValid()) {
      }
      else {
        foreach ($validator->getErrors() as $error) {
          $this->errors[] = array('id' => $id, 'property' => $error['property'], 'error' => $error['message']);
        }
      }
    }
  }

  public function getErrors() {
    return $this->errors;
  }
}
