<?php
/**
* @file Contains the code to generate the custom drush commands.
*/
/**
* Implements hook_drush_command().
*/
function custom_drush_command_drush_command() {
 $items = array();
 $items['feeds_update'] = [
   'description' => 'Run the custom feeds module',
   'drupal dependencies' => ['custom_drush_command'],
   'aliases' => ['feeds_update'],
 ];
 return $items;
}
/**
* Call back function drush_custom_drush_command_say_hello()
* The call back function name in the  following format
*   drush_{module_name}_{item_id_for_command}()
*/
function drush_custom_drush_command_feeds_update() {
 //drush_print('Feeds Update will run here!');
  custom_drush_command_test();
}
