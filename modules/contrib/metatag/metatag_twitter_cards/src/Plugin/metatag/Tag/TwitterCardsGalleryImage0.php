<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsGalleryImage0.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards gallery image0 metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_gallery_image0",
 *   label = @Translation("1st gallery image"),
 *   description = @Translation("A URL to the image representing the first photo in your gallery. This will be able to extract the URL from an image field."),
 *   name = "twitter:gallery:image0",
 *   group = "twitter_cards",
 *   weight = 200,
 *   image = TRUE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsGalleryImage0 extends MetaPropertyBase {
}
