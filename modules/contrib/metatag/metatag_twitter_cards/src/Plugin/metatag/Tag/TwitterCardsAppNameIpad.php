<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppNameIpad.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app name for ipad metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_name_ipad",
 *   label = @Translation("iPad app name"),
 *   description = @Translation("The name of the iPad app."),
 *   name = "twitter:app:name:ipad",
 *   group = "twitter_cards",
 *   weight = 303,
 *   image = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppNameIpad extends MetaPropertyBase {
}
