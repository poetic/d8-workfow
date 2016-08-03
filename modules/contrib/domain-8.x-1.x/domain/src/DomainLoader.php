<?php

namespace Drupal\domain;

use Drupal\Core\Config\TypedConfigManagerInterface;

/**
 * Loads Domain records.
 */
class DomainLoader implements DomainLoaderInterface {

  /**
   * The typed config handler.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfig;

  /**
   * Constructs a DomainLoader object.
   *
   * Trying to inject the storage manager throws an exception.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed config handler.
   *
   * @see getStorage()
   */
  public function __construct(TypedConfigManagerInterface $typed_config) {
    $this->typedConfig = $typed_config;
  }

  /**
   * {@inheritdoc}
   */
  public function loadSchema() {
    $fields = $this->typedConfig->getDefinition('domain.record.*');
    return isset($fields['mapping']) ? $fields['mapping'] : array();
  }

  /**
   * {@inheritdoc}
   */
  public function load($id, $reset = FALSE) {
    $controller = $this->getStorage();
    if ($reset) {
      $controller->resetCache(array($id));
    }
    return $controller->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadDefaultId() {
    $result = $this->loadDefaultDomain();
    if (!empty($result)) {
      return $result->id();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadDefaultDomain() {
    $result = $this->getStorage()->loadByProperties(array('is_default' => TRUE));
    if (!empty($result)) {
      return current($result);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple($ids = NULL, $reset = FALSE) {
    $controller = $this->getStorage();
    if ($reset) {
      $controller->resetCache($ids);
    }
    return $controller->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleSorted($ids = NULL) {
    $domains = $this->loadMultiple();
    uasort($domains, array($this, 'sort'));
    return $domains;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByHostname($hostname) {
    $result = $this->getStorage()->loadByProperties(array('hostname' => $hostname));
    if (empty($result)) {
      return NULL;
    }
    return current($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadOptionsList() {
    $list = array();
    foreach ($this->loadMultipleSorted() as $id => $domain) {
      $list[$id] = $domain->label();
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function sort(DomainInterface $a, DomainInterface $b) {
    return $a->getWeight() > $b->getWeight();
  }

  /**
   * Loads the storage controller.
   *
   * We use the loader very early in the request cycle. As a result, if we try
   * to inject the storage container, we hit a circular dependency. Using this
   * method at least keeps our code easier to update.
   */
  protected function getStorage() {
    $storage = \Drupal::entityTypeManager()->getStorage('domain');
    return $storage;
  }

}
