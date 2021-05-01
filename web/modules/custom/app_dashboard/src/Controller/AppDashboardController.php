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
   * Get an array all nodes by their workflow state.
   */
  public function getWorkflowCounts() {
    // Quick and dirty static query.
    $database = \Drupal::database();
    $query = $database->query("
      SELECT content_moderation_state.moderation_state AS state, count(*) as count
      FROM node_field_data node_field_data
      LEFT JOIN content_moderation_state_field_revision content_moderation_state
        ON node_field_data.vid = content_moderation_state.content_entity_revision_id
        AND (
          content_moderation_state.content_entity_type_id = 'node'
          AND content_moderation_state.content_entity_id = node_field_data.nid
        )
      WHERE (node_field_data.type IN ('application'))
        AND (content_moderation_state.workflow = 'application_workflow')
      GROUP BY content_moderation_state.moderation_state
    ");
    $result = $query->fetchAllKeyed();
    return $result;
  }

  /**
   * Page Contents.
   *
   * @return array
   *   Render array passed to app_dashboard template.
   */
  public function contents() {
    $counts = $this->getWorkflowCounts();
    return [
      '#theme' => 'app_dashboard',
      '#submitted' => $counts['draft'] ?? 0,
      '#interview' => $counts['app_requested_interview'] ?? 0,
      '#offered' => $counts['app_offered_sent'] ?? 0,
      '#rejected' => $counts['app_rejected'] ?? 0,
      '#prospect' => $counts['app_future_prospect'] ?? 0,
      '#hired' => $counts['app_hired'] ?? 0,
    ];
  }

}
