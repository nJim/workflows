<?php

namespace Drupal\app_dashboard\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\file\Entity\File;

/**
 * Custom views application details field.
 *
 * Renders some of the application fields through a custom template to enhance
 * the application dashboard view. For example, while views can display the
 * field_app_overnight as a TRUE/FALSE, I wanted to show it as an icon that is
 * active or not.
 *
 * @see: /admin/structure/views/view/manage_applications.
 *
 * @ViewsField("app_meta")
 */
class AppMetaField extends FieldPluginBase {

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
    // The application entity.
    $entity = $values->_entity;

    return [
      '#theme' => 'app_meta',
      '#isOvernight' => $this->isOvernightStaff($entity),
      '#canAttendTraining' => $this->canAttendTraining($entity),
      '#resumeUrl' => $this->getResumeUrl($entity),
      '#appUrl' => $entity->toUrl()->toString(),
    ];
  }

  protected function getResumeUrl($entity) {
    $fieldName = 'field_app_resume';
    if (!$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty()) {
      return NULL;
    }
    $uri = $entity->get('field_app_resume')->entity->getFileUri();
    return file_create_url($uri);
  }

  protected function canAttendTraining($entity) {
    return (bool) $this->getFieldValue($entity, 'field_app_orientation');
  }

  protected function isOvernightStaff($entity) {
    return (bool) $this->getFieldValue($entity, 'field_app_overnight');
  }

  protected function getFieldValue($entity, $fieldName) {
    if (!$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty()) {
      return NULL;
    }
    return $entity->get($fieldName)->getString();
  }

}
