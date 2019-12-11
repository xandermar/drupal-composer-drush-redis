<?php

namespace Drupal\login_redirect_per_role\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Drupal\login_redirect_per_role\LoginRedirectPerRoleInterface;
use Drupal\user\Controller\UserController;
use Drupal\user\UserDataInterface;
use Drupal\user\UserStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for user routes.
 */
class LoginRedirectPerRoleUser extends UserController {

  /**
   * Login And Logout Redirect Per Role helper service.
   *
   * @var \Drupal\login_redirect_per_role\LoginRedirectPerRoleInterface
   */
  protected $loginRedirectPerRole;

  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\login_redirect_per_role\LoginRedirectPerRoleInterface $login_redirect_per_role
   *   Login And Logout Redirect Per Role helper service.
   */
  public function __construct(DateFormatterInterface $date_formatter, UserStorageInterface $user_storage, UserDataInterface $user_data, LoggerInterface $logger, LoginRedirectPerRoleInterface $login_redirect_per_role) {
    parent::__construct($date_formatter, $user_storage, $user_data, $logger);

    $this->loginRedirectPerRole = $login_redirect_per_role;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('user.data'),
      $container->get('logger.factory')->get('user'),
      $container->get('login_redirect_per_role.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function logout() {

    $url = $this->loginRedirectPerRole->getLogoutRedirectUrl();
    $return = parent::logout();

    if ($url instanceof Url) {
      $url->setAbsolute();
      $return = new RedirectResponse($url->toString());
    }

    return $return;
  }

}
