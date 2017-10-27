<?php

use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use JsonSchema\Validator;

/**
 * Interface odsmValidatorInterface
 * @package openDataSchemaMap
 */
abstract class OdsmValidator implements OdsmValidatorInterface {
  private $data = [];
  private $datasets = [];
  private $errors = [];

  abstract protected function getDatasetIdProperty();

  abstract protected function getSchemaInfo();

  abstract protected function getDatasetsFromData($data);

  abstract protected function getDatasetsFromData($data);

  private function getUrl() {
    global $base_url;
    $schema_info = $this->getSchemaInfo();
    return $base_url . '/' . $schema_info->api_endpoint;
  }

  /**
   * {@inheritdoc}
   */
  public function datasetCount() {
    $datasets = $this->getDatasets();
    return count($datasets);
  }

  public function validate() {
    if (empty($this->errors)) {
      $datasets = $this->getDatasets();
      foreach ($datasets as $dataset) {
        if($errors = $this->validateDataset($dataset)) {
          $this->errors = array_merge($this->errors, $errors);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    if (empty($this->errors)) {
     $this->validate();
    }
    return $this->errors;
  }

  private function getDatasets() {
    if (empty($this->datasets)) {
      $data = $this->getData();
      foreach ($this->getDatasetsFromData($data) as $dataset) {
        $id = $this->getDatasetId($dataset);
        $this->datasets[$id] = $dataset;
      }
    }

    return $this->datasets;
  }

  private function validateDataset($dataset) {
    $id = $this->getDatasetId($dataset);

    $retriever = new UriRetriever();

    $schema_info = $this->getSchemaInfo();

    RefResolver::$maxDepth = 10;
    $ref_resolver = new RefResolver($retriever);
    $ref_resolver->resolve($schema_info->schema, 'file://' . $schema_info->schema_folder . '/');
    $validator = new Validator();
    $validator->check($dataset, $schema_info->schema);

    $errors = [];
    foreach ($validator->getErrors() as $error) {
      $errors[] = ['id' => $id, 'property' => $error['property'], 'error' => $error['message']];
    }
    
    return $errors;
  }

  private function getDatasetId($dataset) {
    return isset($dataset->{$this->getDatasetIdProperty()}) ? $dataset->{$this->getDatasetIdProperty()} : NULL;
  }

  private function getData() {
    if (empty($this->data)) {
      $arr_context_options = array(
        'ssl' => array(
          'verify_peer' => FALSE,
          'verify_peer_name' => FALSE,
        ),
      );
      // Setting a 5 minute timeout for loading api data.
      $timeout = 600;
      $resp = drupal_http_request($this->getUrl(), array('context' => stream_context_create($arr_context_options), 'timeout' => $timeout));
      if ($resp->code == 200) {
        $this->data = json_decode($resp->data);
      }
      else {
        $message = t("URL Validator timeout or could not access %url: %error", array("%url" => $this->url, "%error" => $resp->error));
        $this->errors[] = array(
          'error' => $message,
        );
      }
    }

    return $this->data;
  }
}
