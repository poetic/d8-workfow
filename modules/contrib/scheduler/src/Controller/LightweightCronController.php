<?php

/**
 * @file
 * Contains \Drupal\scheduler\Controller\LightweightCronController.
 */

namespace Drupal\scheduler\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LightweightCronController.
 *
 * @package Drupal\scheduler\Controller
 */
class LightweightCronController extends ControllerBase {

  /**
   * Index.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   RedirectResponse.
   */
  public function index() {
    \Drupal::service('scheduler.manager')->runCron();

    return new Response('', 204);
  }

  /**
   * Checks access.
   *
   * @param string $cron_key
   *   The cron key.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access($cron_key) {

    $valid_cron_key = \Drupal::config('scheduler.settings')
      ->get('lightweight_cron_access_key');
    return AccessResult::allowedIf($valid_cron_key == $cron_key);
  }

}
