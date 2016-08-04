<?php

/**
 * @file
 * Post update functions for Flag.
 */

/**
 * Update the dependency information in views that depend on flag.
 */
function views_post_update_flag_relationship_dependencies() {
  // Load all views.
  $views = \Drupal::entityManager()->getStorage('view')->loadMultiple();

  /* @var \Drupal\views\Entity\View[] $views */
  foreach ($views as $view) {
    // Views that use the flag_relationship plugin will depend on the Flag
    // module already.
    if (in_array('flag', $view->getDependencies()['module'], TRUE)) {
      $old_dependencies = $view->getDependencies();
      // If we've changed the dependencies, for example, to add a dependency on
      // the flag used in the relationship, then re-save the view.
      if ($old_dependencies !== $view->calculateDependencies()->getDependencies()) {
        $view->save();
      }
    }
  }
}
