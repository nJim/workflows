<?php

namespace Drupal\workflow_tools\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\StateTransitionValidationInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\workflow_tools\WorkflowManager;

/**
 * A tool to help users move content through the moderation workflow.
 */
class TransitionForm extends FormBase {

  /**
   * The workflow manager service.
   *
   * @var \Drupal\workflow_tools\WorkflowManager
   */
  protected $workflow;

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * The moderation state transition validation service.
   *
   * @var \Drupal\content_moderation\StateTransitionValidation
   */
  protected $validation;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * KpTransitionForm constructor.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_info
   *   The moderation information service.
   * @param \Drupal\content_moderation\StateTransitionValidationInterface $validation
   *   The moderation state transition validation service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\kp_workflow\WorkflowManager $workflow
   *   The workflow manager service.
   */
  public function __construct(
    ModerationInformationInterface $moderation_info,
    StateTransitionValidationInterface $validation,
    EntityTypeManagerInterface $entity_type_manager,
    TimeInterface $time,
    WorkflowManager $workflow
  ) {
    $this->moderationInformation = $moderation_info;
    $this->validation = $validation;
    $this->entityTypeManager = $entity_type_manager;
    $this->time = $time;
    $this->workflow = $workflow;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_moderation.moderation_information'),
      $container->get('content_moderation.state_transition_validation'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
      $container->get('workflow_tools.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workflow_transition_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContentEntityInterface $entity = NULL) {
    // Get the current user.
    $user = $this->currentUser();

    // Get a list of all valid workflow transitions for this user.
    /** @var \Drupal\content_moderation\Entity\ContentModerationStateInterface[] $transitions */
    $transitions = $this->workflow->getTransitions($entity, $user);

    // Buttons help the user move through different parts of the editorial
    // process or act as shortcuts to content management actions.
    $form['buttons'] = [];

    // Add a button to the form for each transition. These buttons will move
    // content through the workflow and allow users to log comments. Example
    // transitions are: 'Send for Review', 'Request Changes', or 'Publish'.
    foreach ($transitions as $transition) {
      $form['buttons'][$transition->id()] = [
        '#type' => 'submit',
        '#id' => $transition->id(),
        '#value' => $transition->label(),
      ];
    }

    // Persist the entity so we can access it in the submit handler.
    $form_state->set('entity', $entity);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state->get('entity');
    $user = $this->currentUser();

    /** @var \Drupal\content_moderation\Entity\ContentModerationStateInterface[] $transitions */
    $transitions = $this->workflow->getTransitions($entity, $user);
    $element = $form_state->getTriggeringElement();

    // The log message is specified in the form. If the log is empty and the
    // content is changing workflow states, then provide a default message.
    $state = $transitions[$element['#id']]->to();
    $log = $this->t('Moved to "@state".', ['@state' => $state->label()]);

    // Create a new revisions with the updated moderation state.
    /** @var \Drupal\content_moderation\ContentModerationState $state */
    $state = $transitions[$element['#id']]->to();
    $revision = $this->prepareNewRevision($entity, $log);
    $revision->set('moderation_state', $state->id());
    $revision->save();
  }

  /**
   * Prepares a new revision of a given entity, if applicable.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   A revision log message to set.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The moderation state for the given entity.
   */
  protected function prepareNewRevision(EntityInterface $entity, $message) {
    $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
    if ($storage instanceof ContentEntityStorageInterface) {
      $revision = $storage->createRevision($entity);
      if ($revision instanceof RevisionLogInterface) {
        $revision->setRevisionLogMessage($message);
        $revision->setRevisionCreationTime($this->time->getRequestTime());
        $revision->setRevisionUserId($this->currentUser()->id());
      }
      return $revision;
    }
    return $entity;
  }

}
