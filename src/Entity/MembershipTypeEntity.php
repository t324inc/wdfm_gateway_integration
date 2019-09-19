<?php

namespace Drupal\wdfm_gateway_integration\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Membership Type entity. A configuration entity used to manage
 * bundles for the Membership entity.
 *
 * @ConfigEntityType(
 *   id = "membership_type",
 *   label = @Translation("Membership Type"),
 *   bundle_of = "membership",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "membership_type",
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wdfm_gateway_integration\MembershipTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\wdfm_gateway_integration\Form\MembershipTypeEntityForm",
 *       "add" = "Drupal\wdfm_gateway_integration\Form\MembershipTypeEntityForm",
 *       "edit" = "Drupal\wdfm_gateway_integration\Form\MembershipTypeEntityForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer membership types",
 *   links = {
 *     "canonical" = "/admin/structure/membership_type/{membership_type}",
 *     "add-form" = "/admin/structure/membership_type/add",
 *     "edit-form" = "/admin/structure/membership_type/{membership_type}/edit",
 *     "delete-form" = "/admin/structure/membership_type/{membership_type}/delete",
 *     "collection" = "/admin/structure/membership_type",
 *   }
 * )
 */
class MembershipTypeEntity extends ConfigEntityBundleBase {}
