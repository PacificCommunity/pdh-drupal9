<?php

namespace Drupal\spc_main\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @RestResource(
 *   id = "get_layout_resource",
 *   label = @Translation("Layout Resource"),
 *   uri_paths = {
 *     "canonical" = "/layout/resource/{name}"
 *   }
 * )
 */
class LayoutResource extends ResourceBase {
  
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('dummy'),
      $container->get('current_user')
    );
  }
  
  /**
   * Responds to GET requests.
   */
  public function get($name) {
    $response = [];
    
    switch ($name) {
      case 'menu_export':
        $response = $this->menu_renderable('main-menu');
        $code = 200;
        break;
      case 'footer_export':
        $response = $this->footer_renderable();
        $code = 200;
        break; 
      default :
        $response = 'No response where found';
        $code = 400;
        break;
    }
    
    return new ResourceResponse($response, $code);
  } 
  
  /**
   * {@inheritdoc}
   */ 
  public function menu_renderable($menu_name){
    
    $menu_tree = \Drupal::menuTree();
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $tree = $menu_tree->load($menu_name, $parameters);
    $menu = $menu_tree->build($tree);
    $markup = \Drupal::service('renderer')->renderRoot($menu);
    
    return ['#markup' => $markup];
  }
  
  /**
   * {@inheritdoc}
   */ 
  public function footer_renderable(){
    
    $footer_partners = $this->block_plugin_renderable('spc_footer_partners')['#markup'];
    $footer_menu = $this->menu_renderable('footer-menu')['#markup'];
    $markup = $footer_partners . $footer_menu;

    return ['#markup' => $markup];
  }
  
  /**
   * {@inheritdoc}
   */ 
  public function block_plugin_renderable($block_name) {
    
    $block_manager = \Drupal::service('plugin.manager.block');

    $config = [];
    $plugin_block = $block_manager->createInstance($block_name, $config);
    $markup = \Drupal::service('renderer')->renderPlain($plugin_block->build());

    return ['#markup' => $markup->__toString()];
  }
  
  /**
   * {@inheritdoc}
   */ 
  public function block_renderable($block_name) {
    
    $block = \Drupal\block\Entity\Block::load($block_name);
    $markup = \Drupal::entityManager()->getViewBuilder('block')->view($block);

    return ['#markup' => $markup];
  } 
  
}
