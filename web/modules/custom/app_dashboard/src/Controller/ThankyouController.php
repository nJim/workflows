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
      '#markup' => $this->t('Our search committee is collecting applications through April. Please reach out if you have any questions.')
    ];
  }

}
