<?php

namespace Drupal\domain\Tests;

use Drupal\Core\Session\AccountInterface;

/**
 * Tests the access rules and redirects for inactive domains.
 *
 * @group domain
 */
class DomainInactiveTest extends DomainTestBase {

  /**
   * Test inactive domain.
   */
  public function testInactiveDomain() {
    // Create three new domains programmatically.
    $this->domainCreateTestDomains(3);
    $domains = \Drupal::service('domain.loader')->loadMultiple();

    // Grab the last domain for testing.
    $domain = end($domains);
    $this->drupalGet($domain->getPath());
    $this->assertTrue($domain->status(), 'Tested domain is set to active.');
    $this->assertTrue($domain->getPath() == $this->getUrl(), 'Loaded the active domain.');

    // Disable the domain and test for redirect.
    $domain->disable();
    $default = \Drupal::service('domain.loader')->loadDefaultDomain();
    // Must flush cache.
    drupal_flush_all_caches();
    $this->drupalGet($domain->getPath());

    $this->assertFalse($domain->status(), 'Tested domain is set to inactive.');
    $this->assertTrue($default->getPath() == $this->getUrl(), 'Redirected an inactive domain to the default domain.');

    // Try to access with the proper permission.
    user_role_grant_permissions(AccountInterface::ANONYMOUS_ROLE, array('access inactive domains'));
    $this->assertFalse($domain->status(), 'Tested domain is set to inactive.');
    // Must flush cache.
    drupal_flush_all_caches();
    $this->drupalGet($domain->getPath());
    $this->assertTrue($domain->getPath() == $this->getUrl(), 'Loaded the inactive domain with permission.');
  }

}
