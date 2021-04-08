<?php

namespace Drupal\app_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppDashboardController.
 */
class AppDashboardController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Contents.
   *
   * @return string
   *   Return Hello string.
   */
  public function contents() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: contents')
    ];
  }

}
