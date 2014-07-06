<?php

/**
 * @file
 * Pages and forms for ODSM.
 */

/**
 * Callback for ODSM overiew page.
 */
function open_data_schema_map_page_overview() {
  $form = array();
  $apis = open_data_schema_map_api_table();
  $list = array();
  foreach ($apis as $num => $data) {
    $data = (array) $data;
    $data['endpoint'] = isset($data['endpoint']) ? l($data['endpoint'], $data['endpoint']) : '';
    $data['delete'] = l(t('delete'), 'admin/config/services/odsm/delete/' . $data['machine_name']);
    unset($data['machine_name']);
    $list[] = $data;
  }
  $header = array();
  $header[] = t('title');
  $header[] = t('Schema');
  $header[] = t('Entity');
  $header[] = t('Bundle');
  $header[] = t('Endpoint');
  $header[] = t('Edit');
  $header[] = t('Delete');
  $rows = $list;
  return array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => array('class' => array('search-api-overview')),
    '#empty' => t('There are no search servers or indexes defined yet.'),
  );
}

/**
 * Callback for delete page.
 */
function open_data_schema_map_delete(array $form, array &$form_state, $api = NULL) {
  $form = confirm_form($form, t('Delete this API?'), 'admin/config/services/odsm', '', t('Delete'), t('Cancel'), 'administer open data schema mapper.');
  $form['#api'] = $api;
  return $form;
}

/**
 * Submit function for deleting an API.
 */
function open_data_schema_map_delete_submit(array $form, array &$form_state) {
  $api = $form['#api'];
  $id = $api->id;
  db_delete('open_data_schema_map')
  ->condition('id', $id)
    ->execute();
  drupal_set_message(t('API %name has been deleted', array('%name' => $api->name)));
  drupal_goto('admin/config/services/odsm');
}

/**
 * Callback for primary menu page.
 */
function open_data_schema_map_manage(array $form, array &$form_state, $api = NULL) {
  $schemas = open_data_schema_map_register();
  if (!$schemas) {
    $form['empty'] = array(
      '#type' => 'item',
      '#title' => 'No Available Schemas',
      '#markup' => t('You must enable an Open Data schema to add an endpoint.'),
    );
    return $form;
  }
  $list = array();
  foreach ($schemas as $name => $data) {
    $list[$name] = $data['title'];
  }
  $markup = t('No APIs created.');
  if ($list) {
    $markup = t('Choose from the available APIs:') . theme('item_list', $list);
  }
  $entity_types = entity_get_info();
  foreach ($entity_types as $entity_name => $entity_info) {
    $entity_list[$entity_name] = $entity_info['label'];
  }
  $selected_entity = '';
  $selected_schema = '';
  if (isset($form_state['triggering_element']['#name']) && $name = $form_state['triggering_element']['#name']) {
    $selected_entity = $form_state['triggering_element']['#value'];
  }
  elseif ($api) {
    $selected_entity = $api->type;
  }
  $form['name'] = array(
    '#title' => 'Title',
    '#type' => 'textfield',
    '#description' => t('Add an administrative title.'),
    '#required' => TRUE,
  );
  $form['machine_name'] = array(
    '#type' => 'machine_name',
    '#maxlength' => 50,
    '#machine_name' => array(
      'exists' => 'open_data_schema_map_api_exist',
    ),
  );
  $form['type'] = array(
    '#title' => 'Entity Type',
    '#type' => 'select',
    '#description' => t('Entity type.'),
    '#options' => $entity_list,
    '#required' => TRUE,
  );
  if ($api) {
    $form['type']['#disabled'] = TRUE;
  }
  else {
    $form['type']['#ajax'] = array(
      'callback' => 'open_data_schema_map_bundle_ajax_callback',
      'wrapper' => 'open-data-schema-map-bundle-options',
    );
  }
  $bundle_list = array();
  if ($selected_entity) {
    foreach ($entity_types[$selected_entity]['bundles'] as $bundle_name => $bundle_data) {
      $bundle_list[$bundle_name] = $bundle_data['label'];
    }
    $form['bundle'] = array(
      '#title' => 'Bundle',
      '#type' => 'select',
      '#description' => t('Entity type bundle.'),
      '#options' => $bundle_list,
      '#required' => TRUE,
    );
    if ($api) {
      $form['bundle']['#disabled'] = TRUE;
    }
  }
  else {
    $form['bundle'] = array(
      '#title' => 'Bundle',
      '#type' => 'item',
      '#markup' => t('Select an entity type'),
      '#required' => TRUE,
    );
  }
  $form['bundle']['#prefix'] = '<div id="open-data-schema-map-bundle-options">';
  $form['bundle']['#suffix'] = '</div>';
  $form['endpoint'] = array(
    '#title' => 'Endpoint',
    '#type' => 'textfield',
    '#description' => t('Add and enpoint ie "api/3/action/package_show".'),
    '#required' => TRUE,
  );
  $form['api_schema'] = array(
    '#title' => 'Schema',
    '#type' => 'select',
    '#description' => t('Select from the available schemas.'),
    '#options' => $list,
    '#required' => TRUE,
  );
  // Only add Schema if we've already saved the initial config.
  if ($api) {
    $selected_schema = $api->api_schema;
    $schema = open_data_schema_map_schema_load($selected_schema);

    $options_callback = open_data_schema_map_schema_options_callback($schema['$schema']);
    // Arguments!
    $form['arg_id_options'] = array(
      '#type' => 'hidden',
      '#value' => array('' => t('-- none --')) + $options_callback($schema),
    );
    $form['arguments'] = array(
      '#title' => 'Arguments',
      '#type' => 'fieldset',
      '#description' => t('Add an argument to the API. Must be one of the fields from the bundle.'),
      '#collapsible' => TRUE,
    );
    $form['args']['submit'] = array(
      '#type' => 'button',
      '#value' => t('Add another argument'),
      '#ajax' => array(
        'callback' => 'open_data_schema_mapper_args_ajax_callback',
        'wrapper' => 'edit-arguments',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );
    $args = $api->arguments;
    $num_args = count($args);
    if (isset($form_state['clicked_button']) && $form_state['clicked_button']['#value'] == 'Add another argument') {
      $num_args++;
    }
    $form['arguments'] = $form['arguments'] + open_data_schema_map_args_form($num_args, $form['arg_id_options']['#value'], $args);

    $form['api_schema']['#disabled'] = TRUE;
    $title = isset($schema['title']) ? $schema['title'] : t('Mapping');
    $form['mapping'] = array(
      '#title' => $title,
      '#description' => $schema['description'] ? $schema['description'] : t('Fields for selected schema.'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#required' => TRUE,
    );
    if (module_exists('token')) {
      $form['mapping']['token_help'] = array(
        '#title' => t('Replacement patterns'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#description' => t('Prefer raw-text replacements for text to avoid problems with HTML entities!'),
      );
      $form['mapping']['token_help']['tokens'] = array(
        '#theme' => 'token_tree',
        '#token_types' => array('node'),
      );
      $form['mapping']['token_help']['help'] = array(
        '#type' => 'item',
        '#markup' => t('Insert tokens or static values into the text fields below. Use "|" or "&" to indicate "either" or "and" between
        token values. For example, "[node:uuid] | [node:nid]" would print the nid if it is available and if not the nid.'),
      );
    }
    $form['mapping'] = $form['mapping'] + open_data_schema_map_schema_map_form($schema, $api);

    // Load defaults.
    $form['name']['#default_value'] = $api->name;
    $form['machine_name']['#default_value'] = $api->machine_name;
    $form['type']['#default_value'] = $api->type;
    $form['bundle']['#default_value'] = $api->bundle;
    $form['endpoint']['#default_value'] = $api->endpoint;
    $form['api_schema']['#default_value'] = $api->api_schema;
    $form['arguments']['#default_value'] = $api->arguments;

    $form['id'] = array(
      '#type' => 'hidden',
      '#value' => $api->id,
    );

  }
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create API'),
  );
  $form['#tree'] = TRUE;
  return $form;
}

/**
 * Creates form for schema fields.
 */
function open_data_schema_map_schema_map_form($schema, $api) {
  $function = open_data_schema_map_schema_types_callback($schema['$schema']);
  $defaults = '';
  if (isset($api->mapping)) {
    $defaults = $api->mapping;
  }
  $form = $function($schema, $api, $defaults);
  return $form;
}

/**
 * Form AJAX handler.
 */
function open_data_schema_map_bundle_ajax_callback(array $form, array &$form_state) {
  return $form['bundle'];
}

/**
 * Form AJAX handler.
 */
function open_data_schema_mapper_args_ajax_callback(array $form, array &$form_state) {
  return $form['arguments'];
}

/**
 * Submit function for api map.
 */
function open_data_schema_map_manage_submit($form, $form_state) {
  $db_schema = drupal_get_schema('open_data_schema_map');
  $values = $form_state['values'];
  if (isset($values['arguments'])) {
    // Remove empty arguments.
    foreach ($values['arguments'] as $num => $arg) {
      if (!$arg['field']) {
        unset($values['arguments'][$num]);
      }
    }
  }
  $record = array();
  // Prepare values for insertion.
  foreach ($db_schema['fields'] as $name => $data) {
    if (isset($values[$name])) {
      $record[$name] = $values[$name];
    }
  }
  // Decide if we are updating or inserting.
  if (isset($record['id']) && $record['id']) {
    $update = array('id');
  }
  else {
    $update = array();
  }
  drupal_static_reset('open_data_schema_map_api_load_all');
  drupal_write_record('open_data_schema_map', $record, $update);
  drupal_goto('admin/config/services/odsm/edit/' . $values['machine_name']);
}

/**
 * Creates endpoint.
 */
function open_data_schema_map_endpoint($api) {

  $result = array();
  if ($api->type && $api->bundle) {
    $limit = 25;
    $offset = 0;
    $queries = drupal_get_query_parameters();
    $args = array();

    if ($api->arguments) {
      foreach ($api->arguments as $num => $arg) {
        $args[$num]['token'] = $map[$arg['field']];
        foreach ($queries as $field => $value) {
          if ($arg['field'] == $field) {
            $args[$num]['query'] = $value;
          }
        }
      }
    }

    $ids = open_data_schema_map_output_entities($api->type, $api->bundle, $limit, $offset, $args);
    if (!$ids) {
      $result['success'] = FALSE;
      return drupal_json_output($result);
    }
    $result['success'] = TRUE;
    foreach ($ids as $key => $id) {
      $entity = entity_load($api->type, array($id));
      foreach ($api->mapping as $field => $token) {
        if ($token && !is_array($token)) {
          $result['result'][$key][$field] = token_replace($token, array($api->type => $entity[$id]), array('clear' => TRUE));
        }
        elseif (is_array($token)) {
          // Here we are looking up any entity reference fields and looping
          // through them.
          if ($token['odsm_entity_reference']) {
            $ref_field = $token['odsm_entity_reference'];
            unset($token['odsm_entity_reference']);
            $field_language = field_language($api->type, $entity[$id], $ref_field);
            $values = isset($entity[$id]->{$ref_field}[$field_language]) ? $entity[$id]->{$ref_field}[$field_language] : NULL;
            if ($values) {
              foreach ($values as $num => $item) {
                $subvalue = array();
                foreach ($token as $subfield => $subtoken) {
                  $subtoken = str_replace('Nth', $num, $subtoken) ? str_replace('Nth', $num, $subtoken) : $subtoken;
                  if ($subtoken) {
                    $subvalue[$subfield] = token_replace($subtoken, array($api->type => $entity[$id]), array('clear' => TRUE));
                  }
                }
                $result['result'][$key][$field][] = $subvalue;
              }
            }
          }
        }
      }
    }
  }
  drupal_alter('open_data_schema_map_results', $api['machine_name'], $api['schema'], $results);
  return drupal_json_output($result);
}
