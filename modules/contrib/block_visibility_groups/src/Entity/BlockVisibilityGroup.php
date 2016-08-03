<?php

/**
 * @file
 * Contains Drupal\block_visibility_groups\Entity\BlockVisibilityGroup.
 */

namespace Drupal\block_visibility_groups\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\block_visibility_groups\BlockVisibilityGroupInterface;

/**
 * Defines the Block Visibility Group entity.
 *
 * @ConfigEntityType(
 *   id = "block_visibility_group",
 *   label = @Translation("Block Visibility Group"),
 *   handlers = {
 *     "list_builder" = "Drupal\block_visibility_groups\Controller\BlockVisibilityGroupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\block_visibility_groups\Form\BlockVisibilityGroupForm",
 *       "edit" = "Drupal\block_visibility_groups\Form\BlockVisibilityGroupForm",
 *       "delete" = "Drupal\block_visibility_groups\Form\BlockVisibilityGroupDeleteForm"
 *     }
 *   },
 *   config_prefix = "block_visibility_group",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "logic",
 *     "conditions",
 *     "allow_other_conditions",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/block/block-visibility-group/{block_visibility_group}",
 *     "edit-form" = "/admin/structure/block/block-visibility-group/{block_visibility_group}/edit",
 *     "delete-form" = "/admin/structure/block/block-visibility-group/{block_visibility_group}/delete",
 *     "collection" =  "/admin/structure/block/block-visibility-group"
 *   }
 * )
 */
class BlockVisibilityGroup extends ConfigEntityBase implements BlockVisibilityGroupInterface {
  /**
   * The Block Visibility Group ID.
   *
   * @var string
   */
  protected $id;
  /**
   *
   * @var boolean
   */
  protected $allow_other_conditions;

  /**
   * @return boolean
   */
  public function isAllowOtherConditions() {
    return $this->allow_other_conditions;
  }

  /**
   * @param boolean $allow_other_conditions
   */
  public function setAllowOtherConditions($allow_other_conditions) {
    $this->allow_other_conditions = $allow_other_conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'conditions' => $this->getConditions(),
    ];
  }
  /**
   * The Block Visibility Group label.
   *
   * @var string
   */
  protected $label;

  /**
   * The configuration of conditions.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Tracks the logic used to compute, either 'and' or 'or'.
   *
   * @var string
   */
  protected $logic = 'and';

  /**
   * @return string
   */
  public function getLogic() {
    return $this->logic;
  }

  /**
   * @param string $logic
   */
  public function setLogic($logic) {
    $this->logic = $logic;
  }

  /**
   * The plugin collection that holds the conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $conditionCollection;

  /**
   * Returns the conditions.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getConditions() {
    if (!$this->conditionCollection) {
      $this->conditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('conditions'));
    }
    return $this->conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }
  /**
   * {@inheritdoc}
   */
  public function addCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);
    return $this;
  }

  public function getCacheTags() {
    $tags = parent::getCacheTags();
    return Cache::mergeTags($tags, ['block_visibility_group:' . $this->id]);
  }


}
