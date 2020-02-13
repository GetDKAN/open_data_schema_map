<?php
/**
 * @file
 * ODSM Admin form functions.
 */

/**
 * ODSM Admin Settings Form.
 *
 * @param array $form_state
 *   Form state array
 *
 * @return array
 *   Form array
 */
function open_data_schema_map_admin_settings_form($form_state) {
  $available_filters = _open_data_schema_map_get_available_filters('name');
  $form['datasets_per_page'] = array(
    '#type' => 'textfield',
    '#size' => 5,
    '#maxlength' => 5,
    '#default_value' => variable_get('datasets_per_page', ''),
    '#title' => t('Amount of datasets per page'),
    '#description' => t('This will be used as the amount of datasets printed per page in catalog.xml. If not set, all the datasets will be shown in the page.'),
  );
  $form['data_json'] = array(
    '#type' => 'fieldset',
    '#title' => t('Data Federation Filters'),
    '#description' => t('Use this form to exclude specific groups from federated data. Currently, these filters are available to schemas:') . ' ' . implode(', ', $available_filters),
  );
  $group_options = array();
  $result = db_query("SELECT node.title AS node_title, node.nid AS nid
    FROM {node} node
    WHERE ((node.status = '1') AND (node.type IN  ('group')) )
    ORDER BY node_title ASC"
  );
  if ($result) {
    foreach ($result as $record) {
      $group_options[$record->nid] = $record->node_title;
    }
  }
  $form['data_json']['odsm_settings_groups'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Groups to include'),
    '#default_value' => variable_get('odsm_settings_groups', array()),
    '#options' => $group_options,
    '#description' => t('If none of the groups are selected, all groups will be included in the data.json. If any of the groups are checked, only datasets belonging to those groups will be included.'),
  );
  $form['data_json']['odsm_settings_no_publishers'] = array(
    '#type' => 'checkbox',
    '#title' => t('Include datasets with no listed publisher in the data.json.'),
    '#description' => t('If this option is selected, datasets with no listed publisher will be given the default publisher name.'),
    '#default_value' => variable_get('odsm_settings_no_publishers', 1),
  );

  return system_settings_form($form);
}

/**
 * Validate function.
 */
function open_data_schema_map_admin_settings_form_validate($form, &$form_state) {
  $max_num = $form_state['values']['datasets_per_page'];

  if (empty($max_num)) {
    if ($max_num == "0") {
      form_set_error('datasets_per_page', t('Amount of datasets per page cannot be 0.'));
    }
  } else {
    if (!is_numeric($max_num)) {
      form_set_error('datasets_per_page', t('You must enter a number for the amount of datasets per page.'));
    }
    else {
      if ($max_num < 0) {
        form_set_error('datasets_per_page', t('Amount of datasets per page must be positive.'));
      }
    }
  }
}
