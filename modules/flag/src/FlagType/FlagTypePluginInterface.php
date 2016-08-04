<?php

namespace Drupal\flag\FlagType;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Provides an interface for all flag type plugins.
 */
interface FlagTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {
}
