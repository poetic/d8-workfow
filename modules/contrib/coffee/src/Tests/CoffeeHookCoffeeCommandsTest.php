<?php

/**
 * @file
 * Contains \Drupal\coffee\Tests\CoffeeHookCoffeeCommandsTest.
 */

namespace Drupal\coffee\Tests;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;

/**
 * Tests hook_coffee_commands().
 *
 * @group coffee
 */
class CoffeeHookCoffeeCommandsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['coffee', 'coffee_test', 'node'];

  /**
   * The user for tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $coffeeUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->coffeeUser = $this->drupalCreateUser(['access coffee']);
  }

  /**
   * Tests hook_coffee_commands().
   */
  public function testHookCoffeeCommands() {
    $expected = [
      'value' => Url::fromRoute('<front>')->toString(),
      'label' => t('Coffee hook fired!'),
      'command' => ':test',
    ];

    $expected_coffee = [
      'value' => Url::fromRoute('<front>')->toString(),
      'label' => t('Go to front page'),
      'command' => ':front',
    ];

    $commands = \Drupal::moduleHandler()->invokeAll('coffee_commands');
    $this->assertTrue(in_array($expected, $commands), 'coffee_test_coffee_commands() was executed properly invoking the hook_coffe_commands() manually.');
    $this->assertTrue(in_array($expected_coffee, $commands), 'coffee_coffee_commands() was executed properly invoking the hook_coffe_commands() manually.');

    $this->drupalLogin($this->coffeeUser);
    $commands = $this->drupalGetJSON('admin/coffee/get-data');
    $this->assertResponse(200);
    $this->assertTrue(in_array($expected, $commands), 'coffee_test_coffee_commands() was executed invoking hook_coffe_commands() in CoffeeController.');
    $this->assertTrue(in_array($expected_coffee, $commands), 'coffee_coffee_commands() was executed invoking hook_coffe_commands() in CoffeeController.');
  }

}
