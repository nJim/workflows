<?php

namespace Drupal\app_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ThankyouController.
 */
class ThankyouController extends ControllerBase {

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
