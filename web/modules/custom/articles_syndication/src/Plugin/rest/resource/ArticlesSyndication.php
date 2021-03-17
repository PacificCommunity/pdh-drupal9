<?php

namespace Drupal\articles_syndication\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;;
use Drupal\user\Entity\User;
use \Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;

/**
 * @RestResource(
 *   id = "articles_syndication",
 *   label = @Translation("Articles syndication"),
 *   uri_paths = {
 *     "create" = "/syndication/article_syndicate",
 *   }
 * )
 */
class ArticlesSyndication extends ResourceBase {
  
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
      $container->get('logger.factory')->get('articles_syndication'),
      $container->get('current_user')
    );
  }
  
  /**
   * Responds to POST requests.
   */
  public function post(array $data) {
    $response = [];
    
    if (!$this->check_access($data['access_key'])){
      $response = [
        'code'=> 403,
        'message' => 'Access denied. Field access_key is incorrect.'
    ];
      $code = 403;
      return new ResourceResponse($response, $code);
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article');
    $nids = $query->execute();

    $syndicated_id = $data['original_id'];
    $node_update = null;

    foreach ($nids as $nid) {
      
      $node = Node::load($nid); 
      $field_syndicated_id = $node->get('field_syndicated_id')->value;

      if ($field_syndicated_id ==  $syndicated_id) {
        $node_update = $node;
      }
    }   

    if (!empty($node_update)) {
      $data = $this->syndication_article_update($node_update, $data);
    } else {
      $data = $this->syndication_article_create($data);
    }

    $response = $data;
    $code = 200;

    return new ResourceResponse($response, $code);
  }  
  
  private function check_access($hash) {
    if (!empty($hash)) {

      $config = \Drupal::getContainer()->get('config.factory')->getEditable('articles_syndication.settings');
      $spass = $config->get('syndicated_synpass');

      if (!password_verify($spass, $hash)) {
        \Drupal::logger('syndication_article')->error("Access denied. 'access_key' field is incorrect.");
        return false;
      } else {
        return true;
      }
    } else {
      \Drupal::logger('syndication_article')->error("Access denied. 'access_key' field is missing.");
      return false;
    }
    return false;
  }  
  
  /**
   * {@inheritdoc}
   */
  private function syndication_article_create(array $data){
    // Сhecks on required fields
    if (empty($data['title']) || empty($data['original_id'])) {
      // Logs an error
      \Drupal::logger('syndication_article')->error("Syndication failed. Required field is missing.");
      return [
       'code'=> 400,
       'message' => "Syndication failed. Required field is missing."   
      ];
    }
    
    $user_uid = 1;
    $syndicated_id = $data['original_id'];

    // Creates an array with parameters for node creation
    $values = [];
    $values['title'] = !empty($data['title']) ? $data['title'] : null;
    $values['body'] = !empty($data['body']) ? $data['body'] : null;
    $values['body_summary'] = !empty($data['body_summary']) ? $data['body_summary'] : null;
    $values['image'] = !empty($data['image']) ? $data['image'] : null;
    $values['tags']= !empty($data['tags']) ?  explode(', ', $data['tags']) : null;
    $values['uid'] = $this->syndication_article_author($data);

    $node = Node::create([
      'type' => 'article',
      'title' => $values['title'],
      'body' => [
          'value' => $values['body'],
          'summary' => $values['body_summary'],
      ], 
      'status' => 0,
      'promote' => 0,
      'comment' => 0,
    ]);

    $node->set("uid", $values['uid']);  
    $node->set("field_tags", $values['tags']);    
    $node->set("field_syndicated_id", $syndicated_id);  
    
    // Create file object from remote URL.
    if (!empty($values['image'])){
      $exp = explode('/', $values['image']);
      $fileName = end($exp);
      $data = file_get_contents($values['image']);
      $file = file_save_data($data, 'public://' . $fileName);

      $field_image = [
        'target_id' => $file->id(),
        'alt' => $fileName,
       'title' => $fileName
      ];

      $node->set("field_image", $field_image);
    }

    $node->save();

    $message = 'Node ' . $values['title'] . ' (' . $node->id() . ') was successfully created with data: ' . print_r($values, true);
    $params = [
      'subject' => t('Syndicated Article - created'),
      'body' => $message  
    ];    
    $this->syndication_mail($params);
    
    // Logs a notice
    \Drupal::logger('articles_syndication')->notice($message);    
    
    return [
        'code'=> 200,
        'message' => "Node ". $values['title'] . " was successfully created"
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  private function syndication_article_update($node, $data){
    
    // Creates an array with parameters for node creation
    $values = [];
    $values['title'] = !empty($data['title']) ? $data['title'] : null;
    $values['body'] = !empty($data['body']) ? $data['body'] : null;
    $values['body_summary'] = !empty($data['body_summary']) ? $data['body_summary'] : null;
    $values['image'] = !empty($data['image']) ? $data['image'] : null;
    $values['tags']= !empty($data['tags']) ?  explode(', ', $data['tags']) : null;
    $values['uid'] = $this->syndication_article_author($data);    
    
    $node->set('field_tags', $values['tags']);
    $node->set('title', $values['title']);  
    
    // Create file object from remote URL.
    if (!empty($values['image'])){
      $exp = explode('/', $values['image']);
      $fileName = end($exp);

      $data = file_get_contents($values['image']);
      $file = file_save_data($data, 'public://' . $fileName);

      $field_image = [
        'target_id' => $file->id(),
        'alt' => $fileName,
       'title' => $fileName
      ];
      
      $node->set("field_image", $field_image);
    }    
    $node->save();
    
    $message = 'Node ' . $values['title'] . ' (' . $node->id() . ') was successfully updated with data: ' . print_r($values, true);
    $params = [
      'subject' => t('Syndicated Article - updated'),
      'body' => $message  
    ];
    $this->syndication_mail($params);
    
    // Logs a notice
    \Drupal::logger('articles_syndication')->notice($message);
    
    return [
      'code'=> 200,
      'message' => "Node ". $values['title'] . " was successfully updated"
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  private function syndication_article_author() {
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('articles_syndication.settings');
    $author_uid = $config->get('syndicated_author_uid');
    $author_uid = null;
    if (empty($author_uid)) {
      $default_name = 'SPC';
      $default_email = 'spcsyndication@linkdigital.com.au';
      $author = user_load_by_name($default_name);
      if ($author) {
        $author_uid = $author->id();
      }
      else {
        $author_data = [
          'name' => $default_name,
          'mail' => $default_email,
          'pass' => user_password(),
          'status' => 1,
          'roles' => [],
        ];
        $storage = \Drupal::service('entity_type.manager')->getStorage('user');
        $author =  $storage->create($author_data);
        $author->save();
        $author_uid = $author->id();
      }
      
      $config->set('syndicated_author_uid', $author_uid);
    }
    return $author_uid;
  } 
  
  /**
   * {@inheritdoc}
   */
  private function syndication_mail($params) {

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('articles_syndication.settings');
    $email = $config->get('syndication_email');    

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'articles_syndication';
    $key = 'syndicated_article_created';
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $result = $mailManager->mail($module, $key, $email, $langcode, $params, null, true);
  }

}
