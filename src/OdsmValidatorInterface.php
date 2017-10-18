<?php

/**
 * Interface odsmValidatorInterface
 * @package openDataSchemaMap
 */
interface OdsmValidatorInterface {

  /**
   * Process all datasets for validation.
   */
  public function validate();

  /**
   * Get number of datasets.
   * @return int
   *   Number of datasets.
   */
  public function datasetCount();

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

  /**
   * Clear URLs from validation table.
   */
  public function clearUrlsForValidation();

  /**
   * Queue URLs for Validation.
   */
  public function queueUrlsForValidation();
}
