<?php

namespace Drupal\mass_password_change\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Password reset action.
 *
 * @Action(
 *   id = "mass_password_reset_action",
 *   label = @Translation("Password reset"),
 *   type = "user",
 *   confirm_form_route_name = "mass_password_change.password_reset_confirm",
 * )
 */
class MassPasswordReset extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Private TempStore Factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * MassPasswordReset constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   Private TempStore Factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $tempStoreFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($user, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $user */
    // Prevent uid=1 user or disallow current user, blocked user.
    return ($user->id() != 1) && ($user->id() != $account->id()) && !$user->isBlocked();
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $accounts) {
    /** @var \Drupal\user\UserInterface[] $accounts */
    $this->tempStoreFactory->get('mass_password_change')->set('password_reset', $accounts);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL) {
    /** @var \Drupal\user\UserInterface $account */
    $this->executeMultiple([$account]);
  }

}
