<?php

namespace Drupal\Tests\mass_password_change\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test base class for mass_password_change module.
 *
 * @group mass_password_change
 */
class MassPasswordTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  static public $modules = ['mass_password_change'];

  /**
   * The user for the test.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Array with generated accounts.
   *
   * @var \Drupal\user\UserInterface[]
   */
  protected $accounts;

  /**
   * Set up a privileged user.
   */
  public function setUp() {
    parent::setUp();
    // Create roles.
    $roles = [];
    for ($i = 0; $i < 3; $i++) {
      $role = $this->drupalCreateRole([]);
      $roles[$role] = $role;
    }
    // Create users.
    for ($i = 0; $i < 8; $i++) {
      if ($i % 5 == 0) {
        $account = $this->drupalCreateUser([], NULL, TRUE);
      }
      else {
        $account = $this->drupalCreateUser();
      }
      $account->roles[] = array_rand($roles);
      $account->save();
    }
    // Create and log in our privileged user.
    $this->adminUser = $this->drupalCreateUser([
      'administer users',
    ]);
    $this->drupalLogin($this->adminUser);
    // Get accounts from user listing page.
    $this->drupalGet('/admin/people');
    $this->accounts = $this->getUserObjectsFromAccountsPage();
  }

  /**
   * Get User objects from current Accounts (/admin/people) page.
   *
   * @return \Drupal\user\UserInterface[]
   *   Accounts array.
   */
  public function getUserObjectsFromAccountsPage() {
    $accounts = [];
    $domElements = $this->getSession()->getPage()->findAll('css', '.views-field-name a.username');
    foreach ($domElements as $domElement) {
      /** @var \Drupal\user\UserInterface $account */
      $account = user_load_by_name($domElement->getText());
      $accounts[$account->id()] = $account;
    }
    return $accounts;
  }

  /**
   * Get weight from accounts array.
   *
   * @param \Drupal\user\UserInterface[] $accounts
   *   User objects.
   * @param int $uid
   *   Searched user Id.
   *
   * @return int|bool
   *   return FALSE, if user account with selected uid not found,
   *   otherwise return account object weight.
   */
  public function getUserWeightFromAccountsArray(array $accounts, $uid) {
    $weight = 0;
    foreach ($accounts as $account) {
      if ($account->id() == $uid) {
        return $weight;
      }
      $weight++;
    }
    return FALSE;
  }

}
