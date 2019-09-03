<?php

namespace Drupal\wdfm_gateway_integration\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a listing of Membership entities.
 */
class MembershipListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Visual ID');
    $header['name'] = $this->t('Customer');
    $header['valid_until'] = $this->t('Valid Until');
    $header['is_valid'] = $this->t('Valid');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->id();
    $row['name'] = $entity->get('first_name')->value . ' ' . $entity->get('last_name')->value;
    $row['valid_until'] = date('Y-m-d', $entity->get('valid_until')->value);
    $row['is_valid'] = $entity->get('is_valid')->value ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

}
