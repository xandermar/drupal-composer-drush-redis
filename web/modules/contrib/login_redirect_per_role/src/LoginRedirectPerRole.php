<?php

namespace Drupal\login_redirect_per_role;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Login And Logout Redirect Per Role helper service.
 */
class LoginRedirectPerRole implements LoginRedirectPerRoleInterface {

  /**
   * The currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The login_redirect_per_role.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a new Login And Logout Redirect Per Role service object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The currently active route match object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current active user.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(RouteMatchInterface $current_route_match, RequestStack $request_stack, ConfigFactoryInterface $config_factory, AccountProxyInterface $current_user, Token $token) {
    $this->currentRouteMatch = $current_route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->config = $config_factory->get('login_redirect_per_role.settings');
    $this->currentUser = $current_user;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicableOnCurrentPage() {
    switch ($this->currentRouteMatch->getRouteName()) {

      case 'user.reset':
      case 'user.reset.login':
      case 'user.reset.form':
        return FALSE;

      default:
        return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginRedirectUrl() {

    if (!$this->isApplicableOnCurrentPage()) {
      return NULL;
    }

    return $this->getRedirectUrl('login');
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutRedirectUrl() {
    return $this->getRedirectUrl('logout');
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutConfig() {
    return $this->getConfig('logout');
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginConfig() {
    return $this->getConfig('login');
  }

  /**
   * Return redirect URL related to requested key and current user.
   *
   * @param string $key
   *   Configuration key (login or logout).
   *
   * @return \Drupal\Core\Url|null
   *   Redirect URL related to requested key and current user.
   */
  protected function getRedirectUrl($key) {

    $url = NULL;
    $config = $this->getConfig($key);

    if (!$config) {
      return $url;
    }

    $user_roles = $this->currentUser->getRoles();
    $destination = $this->currentRequest->query->get('destination');

    foreach ($config as $role_id => $settings) {

      // Do action only if user have a role and
      // "Redirect URL" is set for this role.
      if (in_array($role_id, $user_roles) && $settings['redirect_url']) {

        // Prevent redirect if destination usage is allowed.
        if ($settings['allow_destination'] && $destination) {
          break;
        }

        $url = Url::fromUserInput($this->token->replace($settings['redirect_url']));
        break;
      }
    }

    return $url;
  }

  /**
   * Return requested configuration items (login or logout) ordered by weight.
   *
   * @param string $key
   *   Configuration key (login or logout).
   *
   * @return array
   *   Requested configuration items (login or logout) ordered by weight.
   */
  protected function getConfig($key) {
    $config = $this->config->get($key);

    if ($config) {
      uasort($config, [SortArray::class, 'sortByWeightElement']);

      return $config;
    }

    return [];
  }

}
