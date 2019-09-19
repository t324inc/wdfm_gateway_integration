<?php

namespace Drupal\wdfm_gateway_integration;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Class MembershipTypeListBuilder
 */
class MembershipTypeListBuilder extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader(){
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }
  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }
}
