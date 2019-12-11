<?php

namespace Drupal\accordion_menus\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a accordion Menu block.
 *
 * @Block(
 *   id = "accordion_menus_block",
 *   admin_label = @Translation("Accordion Menus"),
 *   category = @Translation("Accordion Menus"),
 *   deriver = "Drupal\accordion_menus\Plugin\Derivative\AccordionMenusBlock"
 * )
 */
class AccordionMenusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SystemMenuBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   * @param \Drupal\Core\Plugin\ConfigFactoryInterface $config_factory
   *   The configuration service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_tree, MenuActiveTrailInterface $menu_active_trail = NULL, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
    if ($menu_active_trail === NULL) {
      @trigger_error('The menu.active_trail service must be passed to SystemMenuBlock::__construct(), it is required before Drupal 9.0.0. See https://www.drupal.org/node/2669550.', E_USER_DEPRECATED);
      $menu_active_trail = \Drupal::service('menu.active_trail');
    }
    $this->menuActiveTrail = $menu_active_trail;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('menu.active_trail'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];
    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(0)->onlyEnabledLinks();

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $tree = $this->menuTree->load($menu_name, $parameters);
    $tree = $this->menuTree->transform($tree, $manipulators);

    // Get accordion configuration.
    $config = $this->configFactory->getEditable('accordion_menus.settings');
    $closed_by_default = array_filter($config->get('accordion_menus_default_closed'));
    $no_submenu = $config->get('accordion_menus_no_submenus');
    $without_submenu = in_array($menu_name, $no_submenu, TRUE) ? TRUE : FALSE;

    foreach ($tree as $key => $item) {
      $link = $item->link;

      // Only render accessible links.
      if ($this->isAccordionMenusLinkInaccessible($item)) {
        continue;
      }

      if ($item->subtree) {
        $items[$key] = [
          'content' => $this->generateSubMenuTree($item->subtree),
          'title' => $link->getTitle(),
        ];
      }
      elseif ($without_submenu) {
        $items[$key] = [
          'content' => [
            '#theme' => 'item_list',
            '#items' => [Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())],
          ],
          'title' => $link->getTitle(),
        ];
      }
    }

    return [
      '#theme' => 'accordian_menus_block',
      '#elements' => ['menu_name' => $menu_name, 'items' => $items],
      '#attached' => [
        'library' => ['accordion_menus/accordion_menus_widget'],
        'drupalSettings' => ['accordion_menus' => ['accordion_closed' => $closed_by_default]],
      ],
    ];
  }

  /**
   * Generate submenu output.
   */
  public function generateSubMenuTree($sub_menus) {
    $items = [];
    foreach ($sub_menus as $sub_item) {
      // Only render accessible links.
      if ($this->isAccordionMenusLinkInaccessible($sub_item)) {
        continue;
      }

      $items[] = Link::fromTextAndUrl($sub_item->link->getTitle(), $sub_item->link->getUrlObject());
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
  }

  /**
   * Validate of the menu item accessibility.
   *
   * @param array $item
   *   Menu item object.
   */
  public function isAccordionMenusLinkInaccessible($item) {
    if (!$item->link->isEnabled()
      || ($item->access !== NULL && !$item->access instanceof AccessResultInterface)
      || ($item->access instanceof AccessResultInterface && !$item->access->isAllowed())) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Even when the menu block renders to the empty string for a user, we want
    // the cache tag for this menu to be set: whenever the menu is changed, this
    // menu block must also be re-rendered for that user, because maybe a menu
    // link that is accessible for that user has been added.
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:system.menu.' . $this->getDerivativeId();
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // ::build() uses MenuLinkTreeInterface::getCurrentRouteMenuTreeParameters()
    // to generate menu tree parameters, and those take the active menu trail
    // into account. Therefore, we must vary the rendered menu by the active
    // trail of the rendered menu.
    // Additional cache contexts, e.g. those that determine link text or
    // accessibility of a menu, will be bubbled automatically.
    $menu_name = $this->getDerivativeId();
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . $menu_name]);
  }

}
