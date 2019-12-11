<?php

namespace Drupal\login_redirect_per_role;

/**
 * Interface defining Login And Logout Redirect Per Role helper service.
 */
interface LoginRedirectPerRoleInterface {

  /**
   * Checks is login redirect action applicable on current page.
   *
   * @return bool
   *   Result of check.
   */
  public function isApplicableOnCurrentPage();

  /**
   * Return URL to redirect on user login.
   *
   * @return \Drupal\Core\Url|null
   *   URL to redirect to on success or NULL otherwise.
   */
  public function getLoginRedirectUrl();

  /**
   * Return URL to redirect on user logout.
   *
   * @return \Drupal\Core\Url|null
   *   URL to redirect to on success or NULL otherwise.
   */
  public function getLogoutRedirectUrl();

  /**
   * Return logout configuration.
   *
   * @return array
   *   Logout configuration on success or an empty array otherwise.
   */
  public function getLogoutConfig();

  /**
   * Return login configuration.
   *
   * @return array
   *   Login configuration on success or an empty array otherwise.
   */
  public function getLoginConfig();

}
