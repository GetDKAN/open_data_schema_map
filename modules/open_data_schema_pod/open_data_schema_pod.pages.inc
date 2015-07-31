<?php

/**
 * @file
 * Pages for ODSM POD.
 */

/**
 * Callback for validation page.
 */
function open_data_schema_pod_validation_page() {
  // Run on cron.
  // Count.
  $render = array();
  $cache_form = array();
  $results = open_data_schema_pod_process_validate();
  if (isset($results['cache_date'])) {
    $cache_form = drupal_get_form('open_data_schema_pod_validate_cache_clear', $results['cache_date']);
  }
  $total = array(array($results['count'], count($results['error_datasets']), $results['total_errors']));

  $total_table = theme('table', array('header' => array(t('Total Datasets'), t('Datasets with Errors'), t('Errors')), 'rows' => $total));
  $render['total'] = array(
    '#type'=> 'markup',
    '#markup' => $total_table,
  );
  if ($results['errors']) {
    $headers = array('id', 'property', 'error', 'link');
      $table = theme('table', array('header' => $headers, 'rows' => $results['errors']));
      $render['fieldset'] = array(
        '#type' => 'fieldset',
        '#title' => 'errors',
        '#attributes' => array('class' => array('collapsible', 'collapsed')),
        'content' => array(
          '#markup' => $table,
        ),
        '#attached' => array('js' => array('misc/collapse.js', 'misc/form.js')),
      );
  }

  return drupal_render($cache_form) . drupal_render($render);
}
/**
 * Returns the render array for the form.
 */
function open_data_schema_pod_validate_cache_clear($form, $form_state, $created) {
  $form['cache'] = array(
    '#type' => 'markup',
    '#markup' => t('Result cached %date.', array('%date' => date('F j, Y, g:i a', $created))),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Clear cache'),
  );
  return $form;
}

/**
 * Add a submit handler/function to the form.
 */
function open_data_schema_pod_validate_cache_clear_submit($form, &$form_state) {
  cache_clear_all('pod_validate_result', 'cache');
  drupal_set_message(t('The POD cache has been cleared.'));
}

/**
 *
 */
function open_data_schema_pod_validation_process_errors($rows) {
  foreach ($rows as $key => $row) {
    if (!isset($ids[$row['id']])) {
      $nid = entity_get_id_by_uuid('node', array($row['id']));
      $nid = $nid[$row['id']];
      $node = node_load($nid);
      $ids[$row['id']] = $node;
    }
    else {
      $node = $ids[$row['id']];
    }
    $rows[$key]['title'] = l($node->title,'node/' . $node->nid);
  }
  return $rows;
}

