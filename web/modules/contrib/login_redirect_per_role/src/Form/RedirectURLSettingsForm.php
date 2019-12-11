<?php

namespace Drupal\login_redirect_per_role\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Element;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RedirectURLSettingsForm.
 */
class RedirectURLSettingsForm extends ConfigFormBase {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PathValidatorInterface $path_validator, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);

    $this->pathValidator = $path_validator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.validator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'login_redirect_per_role.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redirect_url_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('login_redirect_per_role.settings');
    $actions = $this->getAvailableActions();
    $roles = $this->getAvailableUserRoleNames();

    foreach ($actions as $action_id => $action_label) {
      $form[$action_id . '_label'] = [
        '#type' => 'item',
        '#title' => $action_label,
        '#prefix' => '<br>',
      ];

      $form[$action_id] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Role'),
          $this->t('Redirect URL'),
          $this->t('Allow destination'),
          $this->t('Weight'),
        ],
        '#caption' => $this->t("If you don't need @action functionality - leave Redirect URLs empty.", ['@action' => $action_label]),
        '#empty' => $this->t('Sorry, There are no items!'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'table-sort-weight',
          ],
        ],
      ];

      foreach ($roles as $role_id => $role_name) {
        $row = $config->get($action_id . '.' . $role_id);

        $form[$action_id][$role_id]['#attributes']['class'][] = 'draggable';
        $form[$action_id][$role_id]['#weight'] = isset($row['weight']) ? $row['weight'] : 0;

        $form[$action_id][$role_id]['role'] = [
          '#markup' => $role_name,
        ];
        $form[$action_id][$role_id]['redirect_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Redirect URL'),
          '#title_display' => 'invisible',
          '#default_value' => isset($row['redirect_url']) ? $row['redirect_url'] : '',
        ];
        $form[$action_id][$role_id]['allow_destination'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow destination'),
          '#title_display' => 'invisible',
          '#default_value' => (bool) $row['allow_destination'],
        ];
        $form[$action_id][$role_id]['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @role', ['@role' => $role_name]),
          '#title_display' => 'invisible',
          '#default_value' => $form[$action_id][$role_id]['#weight'],
          '#attributes' => ['class' => ['table-sort-weight']],
        ];
      }

      Element::children($form[$action_id], TRUE);
    }

    $form['hint'] = [
      '#type' => 'item',
      '#description' => $this->t('Roles order in list is their priorities: higher in list - higher priority.<br>For example: You set roles ordering as:<br>+ Admin<br>+ Manager<br>+ Authenticated<br>it means that when some user log in (or log out) module will check:<br><em>Does this user have Admin role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty:</li></ul><em>Does this user have Manager role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty:</li></ul><em>Does this user have Authenticated role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty - use default Drupal action</li></ul>'),
      '#prefix' => '<br>',
      '#suffix' => '<br>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $roles = $this->getAvailableUserRoleNames();
    $actions = $this->getAvailableActions();

    foreach ($actions as $action_id => $action_label) {
      foreach ($form_state->getValue($action_id) as $role_id => $settings) {

        if (!empty($settings['redirect_url']) && !$this->pathValidator->isValid($settings['redirect_url'])) {
          $form_state->setErrorByName(
            $action_id . '][' . $role_id . '][redirect_url',
            $this->t(
              '<strong>@action:</strong> Redirect URL for "@role" role is invalid or you do not have access to it.',
              ['@action' => $action_label, '@role' => $roles[$role_id]]
            )
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('login_redirect_per_role.settings');

    $actions = $this->getAvailableActions();
    foreach ($actions as $action_id => $action_label) {
      $config->set($action_id, $form_state->getValue($action_id));
    }

    $config->save();
  }

  /**
   * Return available user role names keyed by role id.
   *
   * @return array
   *   Available user role names.
   */
  protected function getAvailableUserRoleNames() {
    $names = [];

    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();

    if (isset($roles[RoleInterface::ANONYMOUS_ID])) {
      unset($roles[RoleInterface::ANONYMOUS_ID]);
    }

    foreach ($roles as $role) {
      if ($role instanceof RoleInterface) {
        $names[$role->id()] = $role->label();
      }
    }

    return $names;
  }

  /**
   * Return available actions.
   *
   * @return array
   *   Available actions.
   */
  protected function getAvailableActions() {
    return [
      'login' => $this->t('Login redirect'),
      'logout' => $this->t('Logout redirect'),
    ];
  }

}
