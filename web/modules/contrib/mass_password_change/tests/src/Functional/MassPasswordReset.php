<?php

namespace Drupal\Tests\mass_password_change\Functional;

use Drupal\Core\Test\AssertMailTrait;

/**
 * Test the password reset function.
 *
 * @group mass_password_change
 */
class MassPasswordReset extends MassPasswordTestBase {

  use AssertMailTrait {
    getMails as drupalGetMails;
  }

  /**
   * Test Password reset function for Admin (uid=1) user.
   */
  public function testAdminUserMassPasswordReset() {
    $admin_user_weight = $this->getUserWeightFromAccountsArray($this->accounts, 1);
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_reset_action',
      "user_bulk_form[$admin_user_weight]" => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    $this->assertText('No access to execute Password reset the selected user(s) on the User admin.');
  }

  /**
   * Test Password reset function for current user.
   */
  public function testCurrentUserMassPasswordReset() {
    $current_user_weight = $this->getUserWeightFromAccountsArray($this->accounts, $this->adminUser->id());
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_reset_action',
      "user_bulk_form[$current_user_weight]" => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    $this->assertText(sprintf("No access to execute Password reset the selected user(s) on the User %s.", $this->adminUser->getAccountName()));
  }

  /**
   * Test Password reset function for other users.
   */
  public function testOtherUsersMassPasswordReset() {
    $accounts = $this->accounts;
    unset($accounts[1], $accounts[$this->adminUser->id()]);
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_reset_action',
    ];
    foreach ($accounts as $uid => $account) {
      $weight = $this->getUserWeightFromAccountsArray($this->accounts, $uid);
      $edit["user_bulk_form[$weight]"] = TRUE;
    }
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    // Check confirmation text.
    $this->assertText('Are you sure you want to reset password these user accounts?');
    // Check user names.
    foreach ($accounts as $account) {
      $this->assertText($account->getAccountName());
    }
    $this->drupalPostForm(NULL, [], 'Password reset');
    // Check password reset urls from email.
    $emails = $this->drupalGetMails();
    $reset_urls = [];
    while ($reset_url = $this->getResetUrl($emails)) {
      $reset_urls[$reset_url['uid']] = $reset_url;
    }
    $this->assertTrue(count($accounts) == count($reset_urls), 'Password reset email sending successful.');
    foreach ($reset_urls as $reset_url) {
      $this->assertTrue(array_key_exists($reset_url['uid'], $accounts), sprintf("Valid user password reset URL with User Id: %d", $reset_url['uid']));
    }
  }

  /**
   * Test Password reset function with blocked user.
   */
  public function testBlockedUserMassPasswordReset() {
    $blocked_account = $this->drupalCreateUser()->block();
    $blocked_account->save();
    $this->drupalGet('/admin/people');
    $accounts = $this->getUserObjectsFromAccountsPage();
    $weight = $this->getUserWeightFromAccountsArray($accounts, $blocked_account->id());
    $edit = [
      'action' => 'mass_password_reset_action',
      "user_bulk_form[$weight]" => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    $this->assertText(sprintf("No access to execute Password reset the selected user(s) on the User %s.", $blocked_account->getAccountName()));
  }

  /**
   * Retrieves password reset email and extracts the login link.
   */
  public function getResetUrl(&$emails = NULL) {
    if ($emails === NULL) {
      // Assume the most recent email.
      $emails = $this->drupalGetMails();
    }
    if (empty($emails)) {
      return FALSE;
    }
    $email = array_shift($emails);
    $urls = [];
    preg_match('#.+user/reset/(\d+)/.+#', $email['body'], $urls);
    return [
      'url' => $urls[0],
      'uid' => $urls[1],
    ];
  }

}
