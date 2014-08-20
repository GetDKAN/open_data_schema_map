<?php

/**
 * @file
 * Let there be hooks.
 */

/**
 * Declares endpoint without user interface.
 * 
 * @return object
 *   Record object with the following:
 *     - name
 *     - machine_name
 *     - description (optional)
 *     - endpoint
 *     - arguments (optional)
 *     - callback
 * /
function hook_open_data_schema_map_load($machine_name) {
  if ($machine_name == 'my_machine_name') {
    $record = new new stdClass();
    $record->name = 'My endpoint name';
    $record->machine_name = $machine_name;
    $record->endpoint = 'api/action/3/my_endpoint';
    $record->callback = 'my_module_endpoint_callback';
    return $record;
  }
}

/**
 *  Callback for my custom endpoint.
 * 
 * @return array
 *   Results of my custom function or query.
 * /
function my_module_endpoint_callback() {
  // Here we can call a query on a single table or provide other callback to generate items.
  $items = my_sudo_code_query();
  $results = array(
    'description' => t('My endpoint'),
    'results' => $items,
  );
  return $results;
}

/**
 * Declare new open data schema.
 */
function hook_open_data_schema() {
  return array(
    'short_name' => 'new_schema',
    'title' => 'MY New Schema',
    // This is the path to the schema. Schema MUST be in json 3 or json
    // 4 format.
    'schema_file' => $path,
    'description' => t('This new schema rocks.'),
  );

}

/**
 * Allows adding new schema types.
 *
 * Currently onl json-4 and json-3 are accepted.
 */
function hook_open_data_schema_map_schema_types_alter(&$schemas) {
}

/**
 * Allows overriding final results about to be rendered.
 */
function open_data_schema_map_results_alter(&$result, $api_machine_name, $schema) {
  if ($schema == 'new_schema') {
    // Wrap results in 'output' array.
    $result['output'] = $results;
  }
}

/**
 * Allows changing the output of a processed field.
 */
function hook_open_data_schema_map_process_field_alter(&$result, $api_field, $token) {
  if ($api_field == 'foo') {
    $result = 'bar';
  }
}

/**
 * Allows altering of arguments before they are queried.
 *
 * @param array $token
 *   Exploded token. This will be queried by $query->fieldConditioin if
 *   $token[0] = 'node' or $query->propertyCondition if $token[0] = 'field'.
 *   $token[1] will be the node property or field queried.
 * @param array $arg
 *   Exploded argument.
 */
function hook_open_data_schema_map_args_alter(&$token, &$arg) {
  if ($arg['token']['value'] == '[node:url:arg:last]') {
    // Change property being queried. The nid column in the node table will now
    // be queried.
    $token[1] = 'nid';
  }
}
