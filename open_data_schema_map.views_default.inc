<?php
/**
 * @file
 * open_data_schema_map.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function open_data_schema_map_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'url_validations';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'odsm_url_validation';
  $view->human_name = 'URL Validations';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'URL Validations';
  $handler->display->display_options['use_ajax'] = TRUE;
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '30';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'url' => 'url',
    'schema_field' => 'schema_field',
    'response_code' => 'response_code',
    'validation_required' => 'validation_required',
    'date_validated' => 'date_validated',
    'resource_uuid' => 'resource_uuid',
    'edit_node' => 'edit_node',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'url' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'schema_field' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'response_code' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'validation_required' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'date_validated' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'resource_uuid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'edit_node' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Header: Global: Result summary */
  $handler->display->display_options['header']['result']['id'] = 'result';
  $handler->display->display_options['header']['result']['table'] = 'views';
  $handler->display->display_options['header']['result']['field'] = 'result';
  $handler->display->display_options['header']['result']['content'] = 'Displaying @start - @end of @total URLs';
  /* Relationship: Open Data Schema Map URL Validation: Resource_nid */
  $handler->display->display_options['relationships']['resource_nid']['id'] = 'resource_nid';
  $handler->display->display_options['relationships']['resource_nid']['table'] = 'odsm_url_validation';
  $handler->display->display_options['relationships']['resource_nid']['field'] = 'resource_nid';
  $handler->display->display_options['relationships']['resource_nid']['label'] = 'Resource';
  /* Field: Open Data Schema Map URL Validation: Url */
  $handler->display->display_options['fields']['url']['id'] = 'url';
  $handler->display->display_options['fields']['url']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['url']['field'] = 'url';
  $handler->display->display_options['fields']['url']['label'] = 'URL';
  $handler->display->display_options['fields']['url']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['url']['alter']['path'] = '[url]';
  $handler->display->display_options['fields']['url']['alter']['target'] = '_blank';
  /* Field: Open Data Schema Map URL Validation: Schema_field */
  $handler->display->display_options['fields']['schema_field']['id'] = 'schema_field';
  $handler->display->display_options['fields']['schema_field']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['schema_field']['field'] = 'schema_field';
  $handler->display->display_options['fields']['schema_field']['label'] = 'Schema Field(s)';
  /* Field: Open Data Schema Map URL Validation: Response_code */
  $handler->display->display_options['fields']['response_code']['id'] = 'response_code';
  $handler->display->display_options['fields']['response_code']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['response_code']['field'] = 'response_code';
  $handler->display->display_options['fields']['response_code']['label'] = 'Response Code';
  /* Field: Open Data Schema Map URL Validation: Validation_required */
  $handler->display->display_options['fields']['validation_required']['id'] = 'validation_required';
  $handler->display->display_options['fields']['validation_required']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['validation_required']['field'] = 'validation_required';
  $handler->display->display_options['fields']['validation_required']['label'] = 'Queued For Validation';
  $handler->display->display_options['fields']['validation_required']['type'] = 'unicode-yes-no';
  $handler->display->display_options['fields']['validation_required']['not'] = 0;
  /* Field: Open Data Schema Map URL Validation: Date_validated */
  $handler->display->display_options['fields']['date_validated']['id'] = 'date_validated';
  $handler->display->display_options['fields']['date_validated']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['date_validated']['field'] = 'date_validated';
  $handler->display->display_options['fields']['date_validated']['label'] = 'Date Last Checked';
  $handler->display->display_options['fields']['date_validated']['date_format'] = 'iso_8601_date';
  $handler->display->display_options['fields']['date_validated']['second_date_format'] = 'iso_8601_date';
  /* Field: Open Data Schema Map URL Validation: Resource_uuid */
  $handler->display->display_options['fields']['resource_uuid']['id'] = 'resource_uuid';
  $handler->display->display_options['fields']['resource_uuid']['table'] = 'odsm_url_validation';
  $handler->display->display_options['fields']['resource_uuid']['field'] = 'resource_uuid';
  $handler->display->display_options['fields']['resource_uuid']['exclude'] = TRUE;
  /* Field: Content: Edit link */
  $handler->display->display_options['fields']['edit_node']['id'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['edit_node']['field'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['relationship'] = 'resource_nid';
  $handler->display->display_options['fields']['edit_node']['label'] = 'Edit Resource';
  $handler->display->display_options['fields']['edit_node']['empty'] = 'Resource Not Found: [resource_uuid]';
  /* Contextual filter: Open Data Schema Map URL Validation: Api_endpoint */
  $handler->display->display_options['arguments']['api_endpoint']['id'] = 'api_endpoint';
  $handler->display->display_options['arguments']['api_endpoint']['table'] = 'odsm_url_validation';
  $handler->display->display_options['arguments']['api_endpoint']['field'] = 'api_endpoint';
  $handler->display->display_options['arguments']['api_endpoint']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['api_endpoint']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['api_endpoint']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['api_endpoint']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['api_endpoint']['limit'] = '0';
  /* Filter criterion: Open Data Schema Map URL Validation: Response_code */
  $handler->display->display_options['filters']['response_code']['id'] = 'response_code';
  $handler->display->display_options['filters']['response_code']['table'] = 'odsm_url_validation';
  $handler->display->display_options['filters']['response_code']['field'] = 'response_code';
  $handler->display->display_options['filters']['response_code']['exposed'] = TRUE;
  $handler->display->display_options['filters']['response_code']['expose']['operator_id'] = 'response_code_op';
  $handler->display->display_options['filters']['response_code']['expose']['label'] = 'Response_code';
  $handler->display->display_options['filters']['response_code']['expose']['operator'] = 'response_code_op';
  $handler->display->display_options['filters']['response_code']['expose']['identifier'] = 'response_code';
  $handler->display->display_options['filters']['response_code']['is_grouped'] = TRUE;
  $handler->display->display_options['filters']['response_code']['group_info']['label'] = 'Filter By Status';
  $handler->display->display_options['filters']['response_code']['group_info']['identifier'] = 'response_code';
  $handler->display->display_options['filters']['response_code']['group_info']['group_items'] = array(
    1 => array(
      'title' => 'Success',
      'operator' => 'regular_expression',
      'value' => array(
        'value' => '[200|206|302|304|401|403]',
        'min' => '',
        'max' => '',
      ),
    ),
    2 => array(
      'title' => 'Error',
      'operator' => 'regular_expression',
      'value' => array(
        'value' => '[^200|206|302|304|401|403]',
        'min' => '',
        'max' => '',
      ),
    ),
  );

  $export['url_validations'] = $view;

  return $export;
}
