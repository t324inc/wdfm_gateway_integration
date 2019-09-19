<?php

namespace Drupal\wdfm_gateway_integration\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wdfm_gateway_integration\Entity\MembershipEntity;

/**
 * Provides the customer membership type condition for orders.
 *
 * @CommerceCondition(
 *   id = "order_customer_membership_type",
 *   label = @Translation("Customer has active membership of type"),
 *   category = @Translation("Customer"),
 *   entity_type = "commerce_order",
 *   weight = -1,
 * )
 */
class OrderCustomerMembershipType extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'membership_types' => [],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['membership_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed membership types'),
      '#default_value' => $this->configuration['membership_types'],
      '#options' => $this->membershipTypes(),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['membership_types'] = array_filter($values['membership_types']);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $customer = $order->getCustomer();
    $uid = $customer->id();

    $membership_ids = \Drupal::entityQuery('membership')
      ->condition('uid', $uid)
      ->condition('is_valid', TRUE)
      ->execute();

    if(!empty($membership_ids)) {
      $memberships = MembershipEntity::loadMultiple($membership_ids);
      foreach($memberships as $membership) {
        $type_tid = $membership->get('membership_type')->target_id;
        if(in_array($type_tid, $this->configuration['membership_types'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  private function membershipTypes() {
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('membership_types');
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

}
