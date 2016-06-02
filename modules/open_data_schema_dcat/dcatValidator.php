<?php

namespace dcatValidator;

include __DIR__ . '/../../vendor/autoload.php';
$module_path = drupal_get_path('module', 'open_data_schema_map_xml_output');
include implode('/', array($module_path, 'autoload.php'));

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;


use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use JsonSchema\Validator;

class validate {

  function __construct($url) {
    $this->url = $url;
    $this->errors = array();
  }

  public function getDataRDF()
  {
    if (!isset($this->dataset)) {
      $this->dataset = array();
      $fileContents = file_get_contents($this->url);
      $xml = $fileContents;
      $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
      $fileContents = trim(str_replace('"', "'", $fileContents));

$xml_root = DEFAULT_XML_ROOT;
        $encoders = array(
    new XmlEncoder($xml_root)
  );

  $normalizers = array(new GetSetMethodNormalizer());
  $serializer = new Serializer($normalizers, $encoders);
  $object = $serializer->deserialize($xml, 'XmlEncoder', 'array');
print_r($xml); exit;
      $simpleXml = simplexml_load_string($fileContents);
      $json = json_encode($simpleXml);
      $data = json_decode($json, TRUE);
      $this->dataRDF = $data;

      foreach($this->dataRDF->dataset as $dataset) {
        $this->dataset[$dataset->identifier] = $dataset;
      }
      $this->dataRDF->dataset = $this->dataset;
    }
  }

  public function getDataset($id)
  {
    return $this->dataset[$id];
  }

  public function getIdentifiers()
  {
    $this->identifers = array();
    $data = $this->dataRDF;
    foreach ($data->dataset as $dataset) {
      $this->identifiers[] = $dataset->identifier;
    }
  }

  public function process($id) {
    $retriever = new UriRetriever;
    $schemaFolder = DRUPAL_ROOT . '/' . drupal_get_path('module', 'open_data_schema_dcat') . '/data';
    $schema = $retriever->retrieve('file://' . $schemaFolder . '/distribution.json');
    $data = $this->getDataset($id);

    RefResolver::$maxDepth = 10;
    $refResolver = new RefResolver($retriever);
    $refResolver->resolve($schema, 'file://' . $schemaFolder . '/');
    $validator = new Validator();
    $validator->check($data, $schema);
    return $validator;
  }

  public function datasetCount()
  {
    $this->getDataRDF();
    return count($this->dataset);
  }

  public function processAll() {
    $this->getDataRDF();
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
