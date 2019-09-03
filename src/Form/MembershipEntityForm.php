<?php

namespace Drupal\wdfm_gateway_integration\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base Memberships entity configuration form.
 *
 * @ingroup wdfm_gateway_integration
 */
class MembershipEntityForm extends ContentEntityForm {

  /**
   * Entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Constructs a Membership object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the Membership entity type.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * Get the title of the membership.
   *
   * @return string
   *   The label of the entity.
   */
  public function getTitle() {
    return $this->t('Edit %membership', [
      '%membership' => $this->entity->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $membership = $this->getEntity();
    $status = $membership->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label membership.', [
        '%label' => $membership->id(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('There was an error while saving the %label membership.', [
        '%label' => $membership->id(),
      ]));
    }

    $form_state->setRedirect('entity.membership.collection');
  }
}
