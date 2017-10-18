<?php

include __DIR__ . '/../../autoload.php';
$module_path = drupal_get_path('module', 'open_data_schema_map_xml_output');
include implode('/', array($module_path, 'autoload.php'));

use JsonSchema\Uri\UriRetriever;

class DcatValidator extends OdsmValidator {
  private $schemaInfo;

  /**
   * {@inheritdoc}
   */
  protected function getDatasetIdProperty() {
    return "dct:identifier";
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatasetURLs($dataset) {
    $urls = array();

    // Determine URL fields.
    if (!empty($dataset->{'@rdf:about'})) {
      $urls[$dataset->{'@rdf:about'}][] = '@rdf:about';
    }
    foreach ($dataset->{'dcat:Distribution'} as $distribution) {
      $distribution_fields = array(
        'dcat:accessURL',
        'dcat:downloadURL',
        'foaf:page',
      );
      foreach ($distribution_fields as $distribution_field) {
        if (!empty($distribution->$distribution_field)) {
          $urls[trim($distribution->$distribution_field)][] = $distribution_field;
        }
      }
    }
    return $urls;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaInfo() {
    if (empty($this->schemaInfo)) {
      $retriever = new UriRetriever();
      $schema_folder = DRUPAL_ROOT . '/' . drupal_get_path('module', 'open_data_schema_dcat') . '/data';
      $schema = $retriever->retrieve('file://' . $schema_folder . '/distribution.json');

      $this->schemaInfo = new \stdClass();
      $this->schemaInfo->schema = $schema;
      $this->schemaInfo->schema_folder = $schema_folder;
      $this->schemaInfo->api_endpoint = 'catalog.json';
    }
    return $this->schemaInfo;
  }
}
