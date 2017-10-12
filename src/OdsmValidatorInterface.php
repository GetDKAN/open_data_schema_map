<?php

/**
 * Interface odsmValidatorInterface
 * @package openDataSchemaMap
 */
interface OdsmValidatorInterface {

  /**
   * Load Data From API.
   */
  public function loadData();

  /**
   * Get Dataset by ID.
   *
   * @param string $id
   *   Dataset UUID
   *
   * @return object
   *   Dataset object
   */
  public function getDataset($id);

  /**
   * Get Identifiers.
   *
   * @return array
   *   Array of identifiers.
   */
  public function getIdentifiers();

  /**
   * Get schema info of schema map.
   *
   * @return \stdClass
   *   Object with attributes of schema, schema_folder, api_endpoint.
   */
  public function getSchemaInfo();

  /**
   * Process datasets for validation.
   *
   * @param string $id
   *   Dataset UUID to process
   *
   * @return \JsonSchema\Validator
   *   Validator object.
   */
  public function process($id);


  /**
   * Get URLs from Dataset.
   *
   * @param string $id
   *   Dataset UUID to process
   *
   * @return array
   *   Array of URL => field
   */
  public function getDatasetURLs($id);

  /**
   * Add URLs to validation table (does not queue them).
   *
   * @param string $id
   *   Dataset UUID
   */
  public function addUrlsForValidation($id);

  /**
   * Queue URLs for Validation.
   */
  public function queueUrlsForValidation();

  /**
   * Clear URLs from validation table.
   */
  public function clearUrlsForValidation();

  /**
   * Get number of datasets.
   * @return int
   *   Number of datasets.
   */
  public function datasetCount();

  /**
   * Process all datasets for validation.
   */
  public function processAll();

  /**
   * Get validation errors.
   *
   * @return array
   *   Array of validation errors array, with id/property/error keys.
   */
  public function getErrors();

  /**
   * Get # of URL Validations that should be performed per batch run.
   *
   * @return int
   *   Number of URL validations.
   */
  public function getNumValidationsPerRun();

  /**
   * Get # of URLs requiring validation.
   *
   * @return int
   *   Number of URLs requiring validation.
   */
  public function getNumUrlsReqValidation();

  /**
   * Process batch of validations.
   */
  public function processValidations();
}
