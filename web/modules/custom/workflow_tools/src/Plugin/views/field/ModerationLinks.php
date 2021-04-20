<?php

namespace Drupal\workflow_tools\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\workflow_tools\WorkflowManager;

/**
 * TBD.
 *
 * @ViewsField("moderation_links")
 */
class ModerationLinks extends FieldPluginBase {

  /**
   * The workflow manager service.
   *
   * @var \Drupal\workflow_tools\WorkflowManager
   */
  protected $workflow;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('workflow_tools.manager')
    );
  }

  /**
   * Constructs a views plugin field object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WorkflowManager $workflow_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->workflow = $workflow_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // No need to alter the query as the application entity will be passed to
    // the render method with all of the data that we need.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return [
      '#theme' => 'moderation_links',
      '#form' => $this->workflow->getTransitionForm($values->_entity)
    ];
  }

}
