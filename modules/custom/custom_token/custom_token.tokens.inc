<?php
/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
/**
 * Implements hook_token_info().
 */

function custom_token_token_info() {

  $info['types']['brand'] = array(
    'name' => t('Brand'), 
    'description' => t('Tokens related to Brands'), 
    'needs-data' => 'brand',
  );
  $info['tokens']['brand']['name'] = array(
    'name' => t('Name'), 
    'description' => t("The name of the brands."),
  );
  $info['tokens']['brand']['product'] = array(
    'product' => t('Product'), 
    'description' => t("The name of the brand to be used with product urls."),
  );
  $info['tokens']['brand']['url'] = array(
    'url' => t('URL'), 
    'description' => t("The base url of the site."),
  );
  return $info;
}
/**
 * Implements hook_tokens().
 */
function custom_token_tokens($type, $tokens, array $data = array(), array $options = array()) {

  $token_service = \Drupal::token();

  $replacements = array();
  $domain = \Drupal::service('domain.negotiator')->getActiveDomain()->isDefault();
  if($domain){
    $brand = 'Imperial Sugar';
    $product = 'imperial-sugar';
    $url = 'imperialsugar';
  }else{
    $brand = 'Dixie Crystals';
    $product = 'dixie-crystals';
    $url = 'dixiecrystals';
  }
  if ($type == 'brand') {
    $domain = $data['brand'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'name':
          $replacements[$original] = $brand;
          break;
        case 'product':
          $replacements[$original] = $product;
          break;  
        case 'url':
        $replacements[$original] = $url;
        break;  
      }
    }
  }
  return $replacements;

}