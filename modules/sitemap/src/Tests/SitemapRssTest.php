<?php

/**
 * @file
 * Contains \Drupal\sitemap\Tests\SitemapRssTest.
 */

namespace Drupal\sitemap\Tests;

use Drupal\Component\Utility\Unicode;

/**
 * Tests the display of RSS links based on sitemap settings.
 *
 * @group sitemap
 */
class SitemapRssTest extends SitemapTestBase {

  /**
   * Tests RSS feed for front page.
   */
  public function testRssFeedForFrontPage() {
    // Assert default RSS feed for front page.
    $this->drupalGet('/sitemap');
    $this->assertLinkByHref('/rss.xml');

    // Change RSS feed for front page.
    $href = Unicode::strtolower($this->randomMachineName());
    $edit = array(
      'rss_front' => $href,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that RSS feed for front page has been changed.
    $this->drupalGet('/sitemap');
    $this->assertLinkByHref('/' . $href);
  }

  /**
   * Tests included RSS links.
   */
  public function testIncludeRssLinks() {
    $terms = $this->createTerms($this->vocabulary);
    $feed = '/taxonomy/term/@term/feed';
    $tags = array();

    // Get tags from terms.
    foreach ($terms as $term) {
      $tags[] = $term->label();
    }

    // Create dummy node.
    $title = $this->randomString();
    $edit = array(
      'title[0][value]' => $title,
      $this->field_tags_name . '[target_id]' => implode(',', $tags),
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));

    // Assert that RSS link for front page is included in the sitemap.
    $this->drupalGet('/sitemap');
    $this->assertLinkByHref('/rss.xml');

    // Assert that RSS links are included in the sitemap.
    foreach ($terms as $term) {
      $this->assertLinkByHref(format_string($feed, array('@term' => $term->id())));
    }

    // Change the settings to place RSS links on the left.
    $edit = array(
      'show_rss_links' => 2,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that the 'sitemap-rss-left' class is found.
    $this->drupalGet('/sitemap');
    $this->assertRaw('sitemap-rss-left', 'Class .sitemap-rss-left found.');

    // Change module not to include RSS links.
    $edit = array(
      'show_rss_links' => 0,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that RSS link for front page is not included in the sitemap.
    $this->drupalGet('/sitemap');
    $this->assertNoLinkByHref('/rss.xml');

    // Assert that RSS links are not included in the sitemap.
    foreach ($terms as $term) {
      $this->assertNoLinkByHref(format_string($feed, array('@term' => $term->id())));
    }
  }

  /**
   * Tests RSS feed depth.
   */
  public function testRssFeedDepth() {
    $terms = $this->createTerms($this->vocabulary);
    $feed = '/taxonomy/term/@term/feed';
    $tags = array();

    // Get tags from terms.
    foreach ($terms as $term) {
      $tags[] = $term->label();
    }

    // Assert that all RSS links are not included in the sitemap.
    $this->drupalGet('sitemap');
    foreach ($terms as $term) {
      $this->assertNoLinkByHref(format_string($feed, array('@term' => $term->id())));
    }

    // Create dummy node.
    $title = $this->randomString();
    $edit = array(
      'title[0][value]' => $title,
      $this->field_tags_name . '[target_id]' => implode(',', $tags),
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));

    // Change RSS feed depth to -1.
    $edit = array(
      'rss_taxonomy' => -1,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that all RSS links are included in the sitemap.
    $this->drupalGet('sitemap');
    foreach ($terms as $term) {
      $this->assertLinkByHref(format_string($feed, array('@term' => $term->id())));
    }

    // Change RSS feed depth to 0.
    $edit = array(
      'rss_taxonomy' => 0,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that RSS links are not included in the sitemap.
    $this->drupalGet('sitemap');
    foreach ($terms as $term) {
      $this->assertNoLinkByHref(format_string($feed, array('@term' => $term->id())));
    }

    // Change RSS feed depth to 1.
    $edit = array(
      'rss_taxonomy' => 1,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that only RSS feed link for term 1 is included in the sitemap.
    $this->drupalGet('sitemap');
    $this->assertLinkByHref(format_string($feed, array('@term' => $terms[0]->id())));
    $this->assertNoLinkByHref(format_string($feed, array('@term' => $terms[1]->id())));
    $this->assertNoLinkByHref(format_string($feed, array('@term' => $terms[2]->id())));

    // Change RSS feed depth to 2.
    $edit = array(
      'rss_taxonomy' => 2,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that RSS feed link for term 1 and term 2 is included in the site
    // map.
    $this->drupalGet('sitemap');
    $this->assertLinkByHref(format_string($feed, array('@term' => $terms[0]->id())));
    $this->assertLinkByHref(format_string($feed, array('@term' => $terms[1]->id())));
    $this->assertNoLinkByHref(format_string($feed, array('@term' => $terms[2]->id())));

    // Change RSS feed depth to 3.
    $edit = array(
      'rss_taxonomy' => 3,
    );
    $this->drupalPostForm('admin/config/search/sitemap', $edit, t('Save configuration'));

    // Assert that all RSS links are included in the sitemap.
    $this->drupalGet('sitemap');
    foreach ($terms as $term) {
      $this->assertLinkByHref(format_string($feed, array('@term' => $term->id())));
    }
  }

}
