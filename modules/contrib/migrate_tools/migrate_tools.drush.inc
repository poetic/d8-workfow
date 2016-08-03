<?php

/**
 * @file
 * Command-line tools to aid performing and developing migrations.
 */

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Database\ConnectionNotDefinedException;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_tools\MigrateExecutable;
use Drupal\migrate_tools\DrushLogMigrateMessage;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\migrate\Entity\Migration;
use Drupal\node\Entity\Node;
use Drupal\migrate\Row;
use Drupal\custom_migrate\XMLObject;

/**
 * Implements hook_drush_command().
 */
function migrate_tools_drush_command() {
  $items['migrate-status'] = [
    'description' => 'List all migrations with current status.',
    'options' => [
      'group' => 'Name of the migration group to list',
      'names-only' => 'Only return names, not all the details (faster)',
    ],
    'arguments' => [
      'migration' => 'Restrict to a comma-separated list of migrations. Optional',
    ],
    'examples' => [
      'migrate-status' => 'Retrieve status for all migrations',
      'migrate-status --group=beer' => 'Retrieve status for all migrations in a given group',
      'migrate-status BeerTerm,BeerNode' => 'Retrieve status for specific migrations',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['ms'],
  ];

  $items['migrate-import'] = [
    'description' => 'Perform one or more migration processes.',
    'options' => [
      'all' => 'Process all migrations.',
      'group' => 'Name of the migration group to import',
      'limit' => 'Limit on the number of items to process in each migration',
      'feedback' => 'Frequency of progress messages, in items processed',
      'idlist' => 'Comma-separated list of IDs to import',
      'update' => ' In addition to processing unprocessed items from the source, update previously-imported items with the current data',
      'force' => 'Force an operation to run, even if all dependencies are not satisfied',
      'execute-dependencies' => 'Execute all dependent migrations first.',
    ],
    'arguments' => [
      'migration' => 'Name of migration(s) to import. Delimit multiple using commas.',
    ],
    'examples' => [
      'migrate-import --all' => 'Perform all migrations',
      'migrate-import --group=beer' => 'Import all migrations in the beer group',
      'migrate-import BeerTerm,BeerNode' => 'Import new terms and nodes',
      'migrate-import BeerUser --limit=2' => 'Import no more than 2 users',
      'migrate-import BeerUser --idlist=5' => 'Import the user record with source ID 5',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mi'],
  ];

  $items['migrate-rollback'] = array(
    'description' => 'Rollback one or more migrations.',
    'options' => array(
      'all' => 'Process all migrations.',
      'group' => 'Name of the migration group to rollback',
      'feedback' => 'Frequency of progress messages, in items processed',
    ),
    'arguments' => array(
      'migration' => 'Name of migration(s) to rollback. Delimit multiple using commas.',
    ),
    'examples' => array(
      'migrate-rollback --all' => 'Perform all migrations',
      'migrate-rollback --group=beer' => 'Rollback all migrations in the beer group',
      'migrate-rollback BeerTerm,BeerNode' => 'Rollback imported terms and nodes',
    ),
    'drupal dependencies' => array('migrate_tools'),
    'aliases' => array('mr'),
  );

  $items['migrate-stop'] = [
    'description' => 'Stop an active migration operation.',
    'arguments' => [
      'migration' => 'Name of migration to stop',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mst'],
  ];

  $items['migrate-reset-status'] = [
    'description' => 'Reset a active migration\'s status to idle.',
    'arguments' => [
      'migration' => 'Name of migration to reset',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mrs'],
  ];

  $items['migrate-messages'] = [
    'description' => 'View any messages associated with a migration.',
    'arguments' => [
      'migration' => 'Name of the migration',
    ],
    'options' => [
      'csv' => 'Export messages as a CSV'
    ],
    'examples' => [
      'migrate-messages MyNode' => 'Show all messages for the MyNode migration',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mmsg'],
  ];

  $items['migrate-fields-source'] = [
    'description' => 'List the fields available for mapping in a source.',
    'arguments' => [
      'migration' => 'Name of the migration',
    ],
    'examples' => [
      'migrate-fields-source my_node' => 'List fields for the source in the my_node migration',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mfs'],
  ];

  $items['migrate-update-status'] = [
    'description' => 'Compare the nodes in database with the nodes in the source and publish/unpublish',
    'arguments' => [
      'migration' => 'Name of the migration',
    ],
    'drupal dependencies' => ['migrate_tools'],
    'aliases' => ['mus'],
    'options' => array(
      'dry-run' => 'Do not delete, just report the records',
    ),
  ];

  return $items;
}

/**
 * @param string $migration_names
 */
function drush_migrate_tools_migrate_status($migration_names = '') {
  $group_name = drush_get_option('group');
  $names_only = drush_get_option('names-only');

  $migrations = drush_migrate_tools_migration_list($group_name, $migration_names);

  $table = [];
  // Take it one group at a time, listing the migrations within each group.
  foreach ($migrations as $group_id => $migration_list) {
    if ($names_only) {
      $table[] = [
        dt('Group: @name', array('@name' => $group_id))
      ];
    }
    else {
      $table[] = [
        dt('Group: @name', array('@name' => $group_id)),
        dt('Status'),
        dt('Total'),
        dt('Imported'),
        dt('Unprocessed'),
        dt('Last imported'),
      ];
    }
    foreach ($migration_list as $migration_id => $migration) {
      try {
        $map = $migration->getIdMap();
        $imported = $map->importedCount();
        $source_plugin = $migration->getSourcePlugin();
      }
      catch (Exception $e) {
        drush_log(dt('Failure retrieving information on @migration: @message',
          ['@migration' => $migration_id, '@message' => $e->getMessage()]));
        continue;
      }
      try {
        $source_rows = $source_plugin->count();
        // -1 indicates uncountable sources.
        if ($source_rows == -1) {
          $source_rows = dt('N/A');
          $unprocessed = dt('N/A');
        }
        else {
          $unprocessed = $source_rows - $map->processedCount();
        }
      }
      catch (Exception $e) {
        drush_print($e->getMessage());
        drush_log(dt('Could not retrieve source count from @migration: @message',
          ['@migration' => $migration_id, '@message' => $e->getMessage()]));
        $source_rows = dt('N/A');
        $unprocessed = dt('N/A');
      }

      if ($names_only) {
        $table[] = [$migration_id];
      }
      else {
        $status = $migration->getStatusLabel();
        $migrate_last_imported_store = \Drupal::keyValue('migrate_last_imported');
        $last_imported =  $migrate_last_imported_store->get($migration->id(), FALSE);
        if ($last_imported) {
          /** @var DateFormatter $date_formatter */
          $date_formatter = \Drupal::service('date.formatter');
          $last_imported = $date_formatter->format($last_imported / 1000,
            'custom', 'Y-m-d H:i:s');
        }
        else {
          $last_imported = '';
        }
        $table[] = [$migration_id, $status, $source_rows, $imported, $unprocessed, $last_imported];
      }
    }
  }
  drush_print_table($table);
}

/**
 * @param string $migration_names
 */
function drush_migrate_tools_migrate_import($migration_names = '') {
  $group_name = drush_get_option('group');
  $all = drush_get_option('all');
  $options = [];
  if (!$all && !$group_name && !$migration_names) {
    drush_set_error('MIGRATE_ERROR', dt('You must specify --all, --group, or one or more migration names separated by commas'));
    return;
  }

  foreach (['limit', 'feedback', 'idlist', 'update', 'force'] as $option) {
    if (drush_get_option($option)) {
      $options[$option] = drush_get_option($option);
    }
  }

  $migrations = drush_migrate_tools_migration_list($group_name, $migration_names);

  // Take it one group at a time, importing the migrations within each group.
  foreach ($migrations as $group_id => $migration_list) {
    
    array_walk($migration_list, '_drush_migrate_tools_execute_migration', $options);
  }
}

/**
 * Executes a single migration. If the --execute-dependencies option was given,
 * the migration's dependencies will also be executed first.
 *
 * @param \Drupal\migrate\Plugin\MigrationInterface $migration
 *  The migration to execute.
 * @param string $migration_id
 *  The migration ID (not used, just an artifact of array_walk()).
 * @param array $options
 *  Additional options for the migration.
 */
function _drush_migrate_tools_execute_migration(MigrationInterface $migration, $migration_id,  array $options = []) {
  $log = new DrushLogMigrateMessage();
  if (drush_get_option('execute-dependencies')) {
    if ($required_IDS = $migration->get('requirements')) {
      $manager = \Drupal::service('plugin.manager.config_entity_migration');
      $required_migrations = $manager->createInstances($required_IDS);
      $dependency_options = array_merge($options, ['is_dependency' => TRUE]);
      array_walk($required_migrations, __FUNCTION__, $dependency_options);
    }
  }
  if ($options['force']) {
    $migration->set('requirements', []);
  }
  if ($options['update']) {
    $migration->getIdMap()->prepareUpdate();
  }
  $executable = new MigrateExecutable($migration, $log, $options);
  //get ids of all
  //get ids of the ones that updated
  // drush_op() provides --simulate support

  drush_op(array($executable, 'import'));

}

/**
 * @param string $migration_names
 */
function drush_migrate_tools_migrate_rollback($migration_names = '') {
  $group_name = drush_get_option('group');
  $all = drush_get_option('all');
  $options = [];
  if (!$all && !$group_name && !$migration_names) {
    drush_set_error('MIGRATE_ERROR', dt('You must specify --all, --group, or one or more migration names separated by commas'));
    return;
  }

  if (drush_get_option('feedback')) {
    $options['feedback'] = drush_get_option('feedback');
  }

  $log = new DrushLogMigrateMessage();

  $migrations = drush_migrate_tools_migration_list($group_name, $migration_names);

  // Take it one group at a time, rolling back the migrations within each group.
  foreach ($migrations as $group_id => $migration_list) {
    // Roll back in reverse order.
    $migration_list = array_reverse($migration_list);
    foreach ($migration_list as $migration_id => $migration) {
      $executable = new MigrateExecutable($migration, $log, $options);
      // drush_op() provides --simulate support.
      drush_op(array($executable, 'rollback'));
    }
  }
}

/**
 * @param string $migration_id
 */
function drush_migrate_tools_migrate_stop($migration_id = '') {
  /** @var MigrationInterface $migration */
  $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);
  if ($migration) {
    $status = $migration->getStatus();
    switch ($status) {
      case MigrationInterface::STATUS_IDLE:
        drush_log(dt('Migration @id is idle', ['@id' => $migration_id]), 'warning');
        break;
      case MigrationInterface::STATUS_DISABLED:
        drush_log(dt('Migration @id is disabled', ['@id' => $migration_id]), 'warning');
        break;
      case MigrationInterface::STATUS_STOPPING:
        drush_log(dt('Migration @id is already stopping', ['@id' => $migration_id]), 'warning');
        break;
      default:
        $migration->interruptMigration(MigrationInterface::RESULT_STOPPED);
        drush_log(dt('Migration @id requested to stop', ['@id' => $migration_id]), 'success');
        break;
    }
  }
  else {
    drush_log(dt('Migration @id does not exist', ['@id' => $migration_id]), 'error');
  }
}

/**
 * @param string $migration_id
 */
function drush_migrate_tools_migrate_reset_status($migration_id = '') {
  /** @var MigrationInterface $migration */
  $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);
  if ($migration) {
    $status = $migration->getStatus();
    if ($status == MigrationInterface::STATUS_IDLE) {
      drush_log(dt('Migration @id is already Idle', ['@id' => $migration_id]), 'warning');
    }
    else {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);
      drush_log(dt('Migration @id reset to Idle', ['@id' => $migration_id]), 'status');
    }
  }
  else {
    drush_log(dt('Migration @id does not exist', ['@id' => $migration_id]), 'error');
  }
}

/**
 * @param string $migration_id
 */
function drush_migrate_tools_migrate_messages($migration_id) {
  /** @var MigrationInterface $migration */
  $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);
  if ($migration) {
    $map = $migration->getIdMap();
    $first = TRUE;
    $table = [];
    foreach ($map->getMessageIterator() as $row) {
      unset($row->msgid);
      if ($first) {
        // @todo: Ideally, replace sourceid* with source key names. Or, should
        // getMessageIterator() do that?
        foreach ($row as $column => $value) {
          $table[0][] = $column;
        }
        $first = FALSE;
      }
      $table[] = (array)$row;
    }
    if (empty($table)) {
      drush_log(dt('No messages for this migration'), 'status');
    }
    else {
      if (drush_get_option('csv')) {
        foreach ($table as $row) {
          fputcsv(STDOUT, $row);
        }
      }
      else {
        $widths = [];
        foreach ($table[0] as $header) {
          $widths[] = strlen($header) + 1;
        }
        drush_print_table($table, TRUE, $widths);
      }
    }
  }
  else {
    drush_log(dt('Migration @id does not exist', ['@id' => $migration_id]), 'error');
  }
}

/**
 * @param string $migration_id
 */
function drush_migrate_tools_migrate_fields_source($migration_id) {
  /** @var MigrationInterface $migration */
  $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);
  if ($migration) {
    $source = $migration->getSourcePlugin();
    $table = [];
    foreach ($source->fields() as $machine_name => $description) {
      $table[] = [strip_tags($description), $machine_name];
    }
    drush_print_table($table);
  }
  else {
    drush_log(dt('Migration @id does not exist', ['@id' => $migration_id]), 'error');
  }
}

/**
 * Retrieve a list of active migrations.
 *
 * @param string $group_id
 *  Group machine name - if present, return only migrations in this group.
 * @param string $migration_ids
 *  Comma-separated list of migrations - if present, return only these migrations.
 *
 * @return MigrationInterface[][]
 *   An array keyed by migration group, each value containing an array of migrations.
 */
function drush_migrate_tools_migration_list($group_id = '', $migration_ids = '') {
  if (!empty($migration_ids)) {
    $migration_ids = explode(',', Unicode::strtolower($migration_ids));
  }
  else {
    $migration_ids = [];
  }

  $manager = \Drupal::service('plugin.manager.config_entity_migration');
  $plugins = $manager->createInstances([]);
  $migrations = [];
  foreach ($plugins as $id => $migration) {
    $configured_group_id = $migration->get('migration_group');
    if (empty($configured_group_id)) {
      $configured_group_id = 'default';
    }
    if (empty($group_id) || $group_id == $configured_group_id) {
      if (empty($migration_ids) || in_array(Unicode::strtolower($id), $migration_ids)) {
        $migrations[$configured_group_id][$id] = $migration;
      }
    }
  }
  return $migrations;
}


/**
 * @param string $migration_id
 * Custom add-on: it unpublishes any nodes that has been removed from the source, and publishes any nodes that are in the source but are unpublished
 * drush mus MIGRATION-NAME
 */

function drush_migrate_tools_migrate_update_status($migration_id = '') {

  //Get the source XML with path and the base query from the migration configuration file
  $migration = entity_load('migration', $migration_id);
  $source = $migration->toArray();
  $source_path= $source['source']['path'];
  $sxe = new \SimpleXMLElement($source_path, NULL, TRUE);
  $nodes = $sxe->xpath($source['source']['base_query']);
  $base_query = $source['source']['base_query'];
  $explode = explode('/',$base_query);
  $base_query_last = array_pop($explode);
  $xml = '<root>';
    foreach($nodes as $node) {
      $xml .= $node->asXML();
    }
  $xml .= '</root>';

  $source_object = new XMLObject($xml);

  //Turn the object into an array
  $raw_source_array = json_decode(json_encode($source_object), true);

  //Get the value of the Key from the migration configuration file
  $source_keys = $source['source']['keys'];

  //Find the elements with the $search and put the values into an array
  $source_array = array();
  $initial_source_arrays = $raw_source_array[$base_query_last];
  // print_r($raw_source_array);
  foreach($initial_source_arrays as $initial_source_array){
    $initial_source_array = array_flat($initial_source_array);
    $single_source_key = '';
    foreach($initial_source_array as $key => $value){
      foreach($source_keys as $source_key){
        if ($source_key === $key){
          $single_source_key .= $value.'|';
        }
      }
    }
    $single_source_key = rtrim($single_source_key, "|");
    array_push($source_array, $single_source_key);
  }

  //Get the nids from the existing nodes to compare
  $db = \Drupal::database();
  
  $data = $db->select('migrate_map_' . $migration_id,'a')->fields('a')->execute();
  $ids = $data->fetchAllAssoc('source_ids_hash', $fetch = NULL);
  $content_type_source_array = array();
  $search = 'sourceid';
  $search_length = strlen($search);
  foreach($ids as $id){
    $single_content_key = '';
    foreach ($id as $key => $value) {
      if (substr($key, 0, $search_length) == $search) {
        $single_content_key .= $value.'|';
      }
    }
    $single_content_key = rtrim($single_content_key, "|");
    array_push($content_type_source_array, $single_content_key);
  }
  //Unpublish any items in this array
  $nodes_unpublish = array_diff($content_type_source_array, $source_array);
  
  foreach($nodes_unpublish as $node_unpublish){
    $item_array = explode('|',$node_unpublish);
    $key_count = count($item_array);
    foreach($ids as $id){
      $nid = $id->destid1;
      if($key_count == 1){
        if($node_unpublish == $id->sourceid1 && !$nid == null){
          $update_node = \Drupal\node\Entity\Node::load($nid);
          $update_node->set('status', 0);
          drush_log(dt('SUK'), 'status');
          $update_node->save();
        }
      }elseif($key_count == 2){
        if($item_array[0] == $id->sourceid1 && $item_array[1] == $id->sourceid2 && !$nid == null){
          $update_node = \Drupal\node\Entity\Node::load($nid);
          $update_node->set('status', 0);
          $update_node->save();
        }
      }
    }
  }

  //Publish any itesm in this array
  $nodes_publish = array_intersect($content_type_source_array, $source_array);

  foreach($nodes_publish as $node_publish){
    $item_array = explode('|',$node_publish);
    $key_count = count($item_array);
    foreach($ids as $id){
      $nid = $id->destid1;
      if($key_count == 1){
        if($node_publish == $id->sourceid1 && !$nid == null){
          $update_node = \Drupal\node\Entity\Node::load($nid);
          $update_node->set('status', 1);          
          $update_node->save();
        }
      }elseif($key_count == 2){
        if($item_array[0] == $id->sourceid1 && $item_array[1] == $id->sourceid2 && !$nid == null){
          $update_node = \Drupal\node\Entity\Node::load($nid);
          $update_node->set('status', 1);
          $update_node->save();
        }
      }
    }
  }drush_log(dt('Status Update Complete'), 'status');
}

function array_flat($array, $prefix = ''){
  $result = array();

  foreach ($array as $key => $value){
    $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;
    if (is_array($value)){
        $result = array_merge($result, array_flat($value, $new_key));
    }else{
      $result[$new_key] = $value;
    }
  }
  return $result;
}