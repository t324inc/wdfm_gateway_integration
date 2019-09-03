<?php

namespace Drupal\wdfm_gateway_integration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\wdfm_gateway_integration\Entity\Membership;

/**
 * Basic Membership entity controller.
 */
class MembershipController extends ControllerBase {

  /**
   * Return the label of a membership as the page title.
   *
   * @param \Drupal\wdfm_gateway_integration\Entity\Membership $membership
   *   A membership entity.
   */
  public function getTitle(Membership $membership) {
    return $this->t('Edit %membership', [
      '%membership' => $membership->id(),
    ]);
  }

}
