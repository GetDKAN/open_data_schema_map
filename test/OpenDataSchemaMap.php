<?php
class OpenDataSchemaMap  extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
      $this->fixJsonEndpoint();
      dkan_default_content_base_install();
    }


  /**
   * Test all read api methods with access control.
   */
  public function testDkanDatasetAPIRead() {
    // Get all data.json succesful responses.
    $responses = $this->runQuerys('data_json_1_1');
    // Get all data.json sucessful responses.
    foreach ($responses as $r) {
      // There should be only one item.
      foreach ($r->dataset as $dataset) {
        // Test if title is set.
        $this->assertTrue(isset($dataset->title));
      }
    }

    // Get all site_read succesful responses.
    $responses = $this->runQuerys('ckan_site_read');
    // Test specifics to site_read for every succesful response.
    foreach ($responses as $r) {
      $this->runCommonTest($r, 'Return');
    }

    // Get all revision_list succesful responses.
    $responses = $this->runQuerys('ckan_revision_list');
    // Test specifics to revision_list for every succesful response.
    foreach ($responses as $r) {
      $this->runCommonTest($r, 'Return a list of the IDs');
    }

    // Get all package_list succesful responses.
    $responses = $this->runQuerys('ckan_package_list');
    // Test specifics to package_list for every succesful response.
    foreach ($responses as $r) {
      $this->runCommonTest($r, 'Return a list of the names');
      $uuids = $r->result;
    }
    foreach ($uuids as $uuid) {
      // Get all package_revision_list succesful responses.
      $responses = $this->runQuerys('ckan_package_revision_list', $uuid);
      foreach ($responses as $r) {
        $this->runCommonTest($r, 'Return a dataset (package)');
        foreach ($r->result as $package) {
          $this->assertTrue($package->timestamp);
          $this->assertTrue($package->id);
        }
      }

      // Get all package_show succesful responses.
      $responses = $this->runQuerys('ckan_package_show', $uuid);
      foreach ($responses as $r) {
        $this->runCommonTest($r, 'Return the metadata of a dataset');
        $this->runPackageTests($r->result);
      }
    }

    // Get all current_package_list_with_resources succesful responses.
    $responses = $this->runQuerys('ckan_current_package_list_with_resources');
    foreach ($responses as $r) {
      $this->runCommonTest($r, 'Return a list of the site\'s datasets');
      $this->runPackageTests($r->result);
    }

    // Get all group_list succesful responses.
    $responses = $this->runQuerys('ckan_group_list');
    $uuids = array();
    foreach ($responses as $r) {
      $this->runCommonTest($r, 'Return a list of the names of the site\'s groups');
      $uuids = $r->result;
    }

    foreach ($uuids as $uuid) {
      // Get all group_package_show succesful responses.
      $responses = $this->runQuerys('ckan_group_package_show', $uuid);
      foreach ($responses as $r) {
        $this->runCommonTest($r, 'Return the datasets (packages) of a group');
      }
    }
  }

  /**
   * Run common test to an array of package.
   *
   * @param object $result
   *   A dkan_dataset_api result object.
   * @param string $text
   *   A string to match against the returned help string.
   */
  protected function runCommonTest($result, $text) {
    if (isset($result->result)  && count($result->result)) {
      $this->assertTrue($result->result);
      $this->assertTrue($result->success);
    }
    $this->assertTrue(strpos($result->help, $text) !== FALSE);
  }

  /**
   * Run common test to an array of package.
   *
   * @param array $packages
   *   An array of json datasets.
   */
  protected function runPackageTests($packages) {
    if (is_array($packages)) {
      // Loop every dataset.
      foreach ($packages as $package) {
        $this->runPackageTest($package);
      }
    }
    else {
      $this->runPackageTest($packages);
    }
  }

  /**
   * Run common test to a package item.
   *
   * @param object $package
   *   A package object.
   */
  protected function runPackageTest($package) {
    $this->assertTrue($package->metadata_created);
    $this->assertTrue($package->metadata_modified);
    $this->assertTrue($package->id);
    $this->assertTrue($package->resources);

    // Loop every resource.
    foreach ($package->resources as $resource) {
      $this->assertTrue($resource->name);
      $this->assertTrue($resource->id);
      $this->assertTrue(property_exists($resource, 'revision_id'));
      $this->assertTrue($resource->created);
      // Using property exists until find correct token for this field.
      $this->assertTrue(property_exists($resource, 'state'));
    }
  }

  /**
   * Runs querys for every hook_menu_item related to $slug.
   *
   * @param string $slug
   *   identifier for a specific api endpoint
   * @param string $uuid
   *   unique identifier for a specific group, resource or dataset query
   */
  protected function runQuerys($slug, $uuid = FALSE) {
    $uris = $this->getHookMenuItems($slug);
    foreach ($uris as $key => $uri) {
      $uris[$key] = array('uri' => $uri, 'options' => array());
      if ($uuid) {
        if (strpos($uri, '%') !== FALSE) {
          $uris[$key]['uri'] = str_replace('%', $uuid, $uri);
        }
        else {
          $uris[$key]['options'] = array('query' => array('id' => $uuid), 'absolute' => TRUE);
        }
      }
    }
    $succesful = array();

    foreach ($uris as $uri) {
      $r = json_decode($this->drupalGet($uri['uri'], $uri['options']));
      $h = $this->drupalGetHeaders();
      $this->assertTrue(strpos($h[':status'], '200') !== FALSE);
      $succesful[] = $r;
    }

    // Return succesful querys for further assertions.
    return $succesful;
  }

  /**
   * Helper that gets defined hook_menu items related to a specific callback.
   *
   * @param string $callback
   *   a string representing a drupal callback.
   *
   * @return array
   *   an array of related callbacks.
   */
  protected function getHookMenuItems($callback) {
    $records = open_data_schema_map_api_load_all();
    $endpoints = array();
    foreach ($records as $record) {
      $endpoints[$record->machine_name] = $record->endpoint;
    }
    return array($endpoints[$callback]);
  }

    /**
     * Change /data.json path to /json during tests.
     */
    protected function fixJsonEndpoint() {
      $data_json = open_data_schema_map_api_load('data_json_1_1');
      $data_json->endpoint = 'json';
      drupal_write_record('open_data_schema_map', $data_json, 'id');
      drupal_static_reset('open_data_schema_map_api_load_all');
      menu_rebuild();
    }
    public function testDataJsonRollback() {
      $this->rollback('dkan_migrate_base_example_data_json11');
    }

}
