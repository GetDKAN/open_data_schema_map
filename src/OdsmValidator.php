<?php

use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use JsonSchema\Validator;

/**
 * Interface odsmValidatorInterface
 * @package openDataSchemaMap
 */
class OdsmValidator implements OdsmValidatorInterface {
  protected $url = '';
  protected $errors = array();
  protected $dataset;
  protected $data;
  protected $identifiers = array();
  protected $schemaInfo = NULL;

  /**
   * OdsmValidator constructor.
   */
  public function __construct() {
    global $base_url;
    $schema = $this->getSchemaInfo();
    $this->url = $base_url . '/' . $schema->api_endpoint;
  }

  /**
   * {@inheritdoc}
   */
  public function loadData() {
    if (empty($this->dataset)) {
      $arr_context_options = array(
        'ssl' => array(
          'verify_peer' => FALSE,
          'verify_peer_name' => FALSE,
        ),
      );
      // Setting a 5 minute timeout for loading api data.
      $timeout = 600;
      $resp = drupal_http_request($this->url, array('context' => stream_context_create($arr_context_options), 'timeout' => $timeout));
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
  }

  /**
   * {@inheritdoc}
   */
  public function getDataset($id) {
    $this->loadData();
    return $this->dataset[$id];
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifiers() {
    // Requires extending class to implement.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaInfo() {
    // Requires extending class to implement.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function process($id) {
    $retriever = new UriRetriever();

    $dataset = $this->getDataset($id);
    $schema_info = $this->getSchemaInfo();

    RefResolver::$maxDepth = 10;
    $ref_resolver = new RefResolver($retriever);
    $ref_resolver->resolve($schema_info->schema, 'file://' . $schema_info->schema_folder . '/');
    $validator = new Validator();
    $validator->check($dataset, $schema_info->schema);

    $this->addUrlsForValidation($id);

    return $validator;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetURLs($id) {
    // Requires extending class to implement.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addUrlsForValidation($id) {
    $node = NULL;
    $urls = $this->getDatasetURLs($id);
    $schema_info = $this->getSchemaInfo();
    $nodes = entity_uuid_load('node', array($id));
    if ($nodes) {
      $node = reset($nodes);
    }

    if ($urls) {
      foreach ($urls as $url => $field) {
        $insert_fields = array(
          'url' => $url,
          'schema_field' => implode(',', $field),
          'api_endpoint' => $schema_info->api_endpoint,
          'resource_uuid' => $id,
        );
        if ($node) {
          $insert_fields['resource_nid'] = $node->nid;
        }

        try {
          db_insert('odsm_url_validation')
            ->fields($insert_fields)
            ->execute();
        }
        catch (Exception $e) {
          // Ignore errors on duplication.
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function queueUrlsForValidation() {
    $schema_info = $this->getSchemaInfo();
    db_update('odsm_url_validation')
      ->fields(array(
        'validation_required' => 1,
      ))
      ->condition('api_endpoint', $schema_info->api_endpoint)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clearUrlsForValidation() {
    $query = new EntityFieldQuery();
    $schema_info = $this->getSchemaInfo();

    $result = $query->entityCondition('entity_type', 'odsm_url_validation')
      ->propertyCondition('api_endpoint', $schema_info->api_endpoint)
      ->execute();

    if ($result) {
      entity_delete_multiple('odsm_url_validation', array_keys($result['odsm_url_validation']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function datasetCount() {
    $this->loadData();
    return count($this->dataset);
  }

  /**
   * {@inheritdoc}
   */
  public function processAll() {
    $this->loadData();
    // Return if an error occurred loading data.
    if (!empty($this->errors)) {
      return;
    }
    $this->getIdentifiers();
    foreach ($this->identifiers as $id) {
      $validator = $this->process($id);

      if ($validator->isValid()) {
      }
      else {
        foreach ($validator->getErrors() as $error) {
          $this->errors[] = array(
            'id' => $id,
            'property' => $error['property'],
            'error' => $error['message'],
          );
        }
      }
    }
    $this->queueUrlsForValidation();
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumValidationsPerRun() {
    return 50;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumUrlsReqValidation($api_endpoint = '') {
    if (empty($api_endpoint)) {
      // Schema info will be set if called from an overridding class.
      $schema_info = $this->getSchemaInfo();
      if ($schema_info) {
        $api_endpoint = $schema_info->api_endpoint;
      }
    }
    $query = new EntityFieldQuery();

    // Get URLs for checking.
    $query->entityCondition('entity_type', 'odsm_url_validation')
      ->propertyCondition('validation_required', 1);
    if (!empty($api_endpoint)) {
      $query->propertyCondition('api_endpoint', $api_endpoint);
    }
    $result = $query->count()->execute();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function processValidations() {
    $query = new EntityFieldQuery();

    // Get max_execution_time from configuration, override 0 with 240 seconds.
    $max_execution_time = ini_get('max_execution_time') == 0 ? 240 : ini_get('max_execution_time');

    // Make sure we have enough time to validate all of the links.
    drupal_set_time_limit($max_execution_time);

    // Make sure this is the only process trying to run this function.
    if (!lock_acquire(__FUNCTION__, $max_execution_time)) {
      watchdog('open_data_schema_map', 'Attempted to re-run link checks while they are already running.', array(), WATCHDOG_WARNING);
      return FALSE;
    }

    $useragent = 'Drupal (+http://drupal.org/)';

    // Get URLs for checking.
    $result = $query->entityCondition('entity_type', 'odsm_url_validation')
      ->propertyCondition('validation_required', 1)
      ->range(0, $this->getNumValidationsPerRun())
      ->execute();

    $url_entities = entity_load('odsm_url_validation', array_keys($result['odsm_url_validation']));

    foreach ($url_entities as $url_entity) {
      $headers = array();
      $headers['User-Agent'] = $useragent;
      $headers['Range'] = 'bytes=0-1024';

      // Add in the headers.
      $options = array(
        'headers' => $headers,
        'method' => 'GET',
        'max_redirects' => 1,
        'timeout' => 10,
      );

      $response = drupal_http_request($url_entity->url, $options);

      if (!isset($response->error)) {
        $response->error = '';
      }
      if (!isset($response->status_message)) {
        $response->status_message = '';
      }
      $response->error = trim(drupal_convert_to_utf8($response->error, 'ISO-8859-1'));
      $response->status_message = trim(drupal_convert_to_utf8($response->status_message, 'ISO-8859-1'));
      $url_entity->response_code = $response->code;
      $url_entity->response_message = $response->error;
      $url_entity->validation_required = 0;
      $url_entity->date_validated = time();
      entity_save('odsm_url_validation', $url_entity);

      if ((timer_read('page') / 1000) > ($this->getNumValidationsPerRun() / 2)) {
        // Stop once we have used over half of the maximum execution time.
        break;
      }
    }

    // Release the lock.
    lock_release(__FUNCTION__);
  }
}
