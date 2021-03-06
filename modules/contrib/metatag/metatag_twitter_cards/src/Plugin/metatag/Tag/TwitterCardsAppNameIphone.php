<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppNameIphone.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app name for iphone metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_name_iphone",
 *   label = @Translation("iPhone app name"),
 *   description = @Translation("The name of the iPhone app."),
 *   name = "twitter:app:name:iphone",
 *   group = "twitter_cards",
 *   weight = 301,
 *   image = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppNameIphone extends MetaPropertyBase {
}
