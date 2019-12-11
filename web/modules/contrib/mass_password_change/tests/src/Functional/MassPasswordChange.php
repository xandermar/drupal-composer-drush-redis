<?php

namespace Drupal\Tests\mass_password_change\Functional;

/**
 * Test the change password function.
 *
 * @group mass_password_change
 */
class MassPasswordChange extends MassPasswordTestBase {

  /**
   * Test Password change function for Admin (uid=1) user.
   */
  public function testAdminUserMassPasswordChange() {
    $admin_user_weight = $this->getUserWeightFromAccountsArray($this->accounts, 1);
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_change_action',
      "user_bulk_form[$admin_user_weight]" => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    $this->assertText('No access to execute Change password the selected user(s) on the User admin.');
  }

  /**
   * Test Password change function for current user.
   */
  public function testCurrentUserMassPasswordChange() {
    $current_user_weight = $this->getUserWeightFromAccountsArray($this->accounts, $this->adminUser->id());
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_change_action',
      "user_bulk_form[$current_user_weight]" => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    $this->assertText(sprintf("No access to execute Change password the selected user(s) on the User %s.", $this->adminUser->getAccountName()));
  }

  /**
   * Test Change password function for other users.
   */
  public function testOtherUsersMassPasswordChange() {
    $accounts = $this->accounts;
    unset($accounts[1], $accounts[$this->adminUser->id()]);
    $this->drupalGet('/admin/people');
    $edit = [
      'action' => 'mass_password_change_action',
    ];
    foreach ($accounts as $uid => $account) {
      $weight = $this->getUserWeightFromAccountsArray($this->accounts, $uid);
      $edit["user_bulk_form[$weight]"] = TRUE;
    }
    $this->drupalPostForm(NULL, $edit, 'Apply to selected items');
    // Check confirmation text.
    $this->assertText('Are you sure you want to change password these user accounts?');
    // Check user names.
    foreach ($accounts as $account) {
      $this->assertText($account->getAccountName());
    }
    // Generate new password and submit confirmation form.
    $new_password = $this->randomString();
    $edit = [
      'password[pass1]' => $new_password,
      'password[pass2]' => $new_password,
    ];
    $this->drupalPostForm(NULL, $edit, 'Change password');
    // Check new passwords.
    foreach ($accounts as $account) {
      $this->drupalLogout();
      $this->drupalGet('/user/login');
      $edit = [
        'name' => $account->getAccountName(),
        'pass' => $new_password,
      ];
      $this->drupalPostForm(NULL, $edit, 'Log in');
      $this->assertUrl('/user/' . $account->id());
      $this->assertText($account->getAccountName());
    }
  }

}
