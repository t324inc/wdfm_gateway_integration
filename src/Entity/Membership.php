<?php

namespace Drupal\wdfm_gateway_integration\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Membership entity.
 *
 * @ingroup membership
 *
 * @ContentEntityType(
 *   id = "membership",
 *   label = @Translation("Membership"),
 *   handlers = {
 *     "form" = {
 *       "add" = "\Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "\Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "local_action_provider" = {
 *       "collection" = "\Drupal\entity\Menu\EntityCollectionLocalActionProvider",
 *     },
 *     "list_builder" = "\Drupal\wdfm_gateway_integration\Controller\MembershipListBuilder",
 *     "views_data" = "\Drupal\views\EntityViewsData",
 *   },
 *   base_table = "membership",
 *   admin_permission = "administer memberships",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   uri_callback = "membership_uri",
 *   links = {
 *     "canonical" = "/admin/content/memberships/{membership}",
 *     "edit-form" = "/admin/content/memberships/{membership}/edit",
 *   }
 *
 * )
 */
class Membership extends ContentEntityBase implements ContentEntityInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Visual ID'))
      ->addConstraint('UniqueField')
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The user that owns this membership.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValue(0)
      ->setDisplayConfigurable('view', TRUE);

    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('The first name associated with the membership.'))
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('The last name associated with the membership.'))
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['street1'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Street 1'))
      ->setDescription(t('The street (line 1) associated with the membership.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['street2'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Street 2'))
      ->setDescription(t('The street (line 2) associated with the membership.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['city'] = BaseFieldDefinition::create('string')
      ->setLabel(t('City'))
      ->setDescription(t('The city associated with the membership.'))
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State'))
      ->setDescription(t('The state associated with the membership.'))
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['zip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Zip Code'))
      ->setDescription(t('The zip code associated with the membership.'))
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phone'))
      ->setDescription(t('The phone number associated with the membership.'))
      ->setSetting('max_length', 16)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 8,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email address associated with the membership.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_valid'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is Valid'))
      ->setDescription(t('Is the membership valid.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setReadOnly(TRUE)
      ->setSettings([
        'on_label' => t('Valid'),
        'off_label' => t('Invalid'),
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['valid_until'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Valid Until'))
      ->setDescription(t('The date the membership is valid until.'))
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
