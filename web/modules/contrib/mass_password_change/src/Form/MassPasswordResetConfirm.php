<?php

namespace Drupal\mass_password_change\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for mass password reset action.
 */
class MassPasswordResetConfirm extends ConfirmFormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new MassPasswordResetConfirm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   * @param \Drupal\user\UserStorageInterface $userStorage
   *   The user storage.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, UserStorageInterface $userStorage) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->userStorage = $userStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mass_password_change.password_reset_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to reset password these user accounts?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.user.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Password reset');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve the accounts to be canceled from the temp store.
    /* @var \Drupal\user\Entity\User[] $accounts */
    $accounts = $this->tempStoreFactory
      ->get('mass_password_change')
      ->get('password_reset');
    if (!$accounts) {
      return $this->redirect('entity.user.collection');
    }
    $list = [];
    foreach ($accounts as $account) {
      $uid = $account->id();
      $list[$uid] = $account->getAccountName();
      $form['accounts'][$uid] = [
        '#type' => 'hidden',
        '#value' => $uid,
      ];
    }
    $form['accounts']['#tree'] = TRUE;
    $form['account']['names'] = [
      '#theme' => 'item_list',
      '#items' => $list,
    ];
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get('mass_password_change')->delete('mass_password_change');
    if ($form_state->getValue('confirm')) {
      foreach ($form_state->getValue('accounts') as $uid) {
        /* @var \Drupal\user\Entity\User $account */
        $account = $this->userStorage->load($uid);
        _user_mail_notify('password_reset', $account);
      }
    }
    $form_state->setRedirect('entity.user.collection');
  }

}
