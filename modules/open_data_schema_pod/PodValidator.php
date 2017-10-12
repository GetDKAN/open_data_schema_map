<?php

include __DIR__ . '/../../autoload.php';
use JsonSchema\Uri\UriRetriever;

/**
 * Class validate
 * @package podValidator
 */
class PodValidator extends OdsmValidator {
  /**
   * {@inheritdoc}
   */
  public function loadData() {
    parent::loadData();
    if (empty($this->dataset) && !empty($this->data)) {
      foreach ($this->data->dataset as $dataset) {
        $this->dataset[$dataset->identifier] = $dataset;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifiers() {
    if (empty($this->identifiers) && !empty($this->dataset)) {
      foreach ($this->dataset as $dataset) {
        $this->identifiers[] = $dataset->identifier;
      }
    }
    return $this->identifiers;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetURLs($id) {
    $urls = array();
    $dataset = $this->getDataset($id);

    // Determine URL fields.
    $fields = array(
      'describedBy',
      'landingPage',
      'license',
      'systemOfRecords',
    );
    foreach ($fields as $field) {
      if (!empty($dataset->$field)) {
        $urls[trim($dataset->$field)][] = $field;
      }
    }

    if (!empty($dataset->{'@rdf:about'})) {
      $urls[$dataset->{'@rdf:about'}][] = '@rdf:about';
    }
    if (isset($dataset->distribution)) {
      foreach ($dataset->distribution as $distribution) {
        $distribution_fields = array(
          'accessURL',
          'downloadURL',
        );
        foreach ($distribution_fields as $distribution_field) {
          if (!empty($distribution->$distribution_field)) {
            $urls[trim($distribution->$distribution_field)][] = $distribution_field;
          }
        }
      }
    }
    return $urls;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaInfo() {
    $retriever = new UriRetriever();
    $schema_folder = DRUPAL_ROOT . '/' . drupal_get_path('module', 'open_data_schema_pod') . '/data/v1.1';
    if (module_exists('open_data_federal_extras')) {
      $schema_filename = 'dataset.json';
    }
    else {
      $schema_filename = 'dataset-non-federal.json';
    }
    $schema = $retriever->retrieve('file://' . $schema_folder . '/' . $schema_filename);

    $schema_obj = new \stdClass();
    $schema_obj->schema = $schema;
    $schema_obj->schema_folder = $schema_folder;
    $schema_obj->api_endpoint = 'data.json';
    return $schema_obj;
  }
}
