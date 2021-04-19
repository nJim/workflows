<?php

namespace Drupal\workflow_tools;

use Drupal\content_moderation\Entity\ContentModerationState;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\StateTransitionValidationInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Helper function for executing custom editorial workflows.
 */
class WorkflowManager {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The moderation state transition validation service.
   *
   * @var \Drupal\content_moderation\StateTransitionValidation
   */
  protected $validation;

  /**
   * Constructs a new WorkflowAccess object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $factory
   *   The logger factory interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_info
   *   The moderation information service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\content_moderation\StateTransitionValidationInterface $validation
   *   The moderation state transition validation service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $factory,
    EntityTypeManagerInterface $entity_type_manager,
    ModerationInformationInterface $moderation_info,
    FormBuilderInterface $form_builder,
    StateTransitionValidationInterface $validation
  ) {
    $this->logger = $factory->get('kp_core');
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationInformation = $moderation_info;
    $this->formBuilder = $form_builder;
    $this->validation = $validation;
  }

  /**
   * Get a the label of the current workflow moderation state.
   *
   * The node object stored a machine name for the current workflow state. This
   * helper method will look up the cooresponding human-readable label.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return string
   *   The name of the current workflow state.
   */
  public function getModerationStateLabel(NodeInterface $node) {
    $state_name = $node->get('moderation_state')->getString();
    $state = ContentModerationState::loadFromModeratedEntity($node);
    if ($state) {
      $workflow = $state->get('workflow')->entity;
      return $workflow->get('type_settings')['states'][$state_name]['label'];
    }
    return '';
  }

  /**
   * Loads the transition form to aid users through the workflow states.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return array
   *   The renderable form with buttons related to the current workflow state.
   */
  public function getTransitionForm(NodeInterface $node) {
    return $this->formBuilder->getForm(
      'Drupal\workflow_tools\Form\TransitionForm',
      $node
    );
  }

  /**
   * Gets a list of transitions that are legal for this user on this entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to be transitioned.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account that wants to perform a transition.
   *
   * @return \Drupal\workflows\Transition[]
   *   The list of transitions that are legal for this user on this entity.
   */
  public function getTransitions(ContentEntityInterface $entity, AccountInterface $account) {
    $transitions = $this->validation->getValidTransitions($entity, $account);

    // Exclude self-transitions.
    /** @var \Drupal\content_moderation\Entity\ContentModerationStateInterface $current_state */
    $current_state = $this->getModerationState($entity);

    /** @var \Drupal\workflows\TransitionInterface[] $transitions */
    $transitions = array_filter($transitions, function ($transition) use ($current_state) {
      return $transition->to()->id() != $current_state->id();
    });

    return $transitions;
  }

  /**
   * Get a list of all workflow states.
   *
   * @param string $bundle
   *   The name of a node bundle.
   *
   * @return \Drupal\content_moderation\ContentModerationState[]
   *   A list of moderation states for this bundle.
   */
  public function getModerationStates($bundle) {
    return $this->moderationInformation
      ->getWorkflowForEntityTypeAndBundle('node', $bundle)
      ->getTypePlugin()
      ->getStates();
  }

  /**
   * Gets the Moderation State of a given Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return \Drupal\workflows\StateInterface
   *   The moderation state for the given entity.
   */
  protected function getModerationState(ContentEntityInterface $entity) {
    $state_id = $this->getModerationStateId($entity);
    $workflow = $this->moderationInformation->getWorkFlowForEntity($entity);
    return $workflow->getTypePlugin()->getState($state_id);
  }

  /**
   * Gets the Moderation State ID of a given Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return string
   *   The id of the current workflow state.
   */
  public function getModerationStateId(ContentEntityInterface $entity) {
    return $entity->moderation_state->get(0)->getValue()['value'];
  }

}
