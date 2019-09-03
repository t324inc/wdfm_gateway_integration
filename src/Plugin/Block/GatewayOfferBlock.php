<?php

namespace Drupal\wdfm_gateway_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to offer registration via membership
 *
 * @Block(
 *   id = "gateway_offer_block",
 *   admin_label = @Translation("Gateway Offer Block"),
 *   category = @Translation("Gateway"),
 * )
 */
class GatewayOfferBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'title' => [
        '#markup' => '<h2 class="gateway-login__heading">Register Your Membership</h2>',
      ],
      'question' => [
        '#type' => 'container',
        'content' => [
          '#markup' => $this->t('Do you have an existing Walt Disney Family Museum <strong>Membership</strong> 
          or <strong>Donor Circle</strong> relationship?'),
        ],
        '#attributes' => [
          'class' => ['gateway-login__question'],
        ],
      ],
      'offer' => [
        '#type' => 'container',
        'content' => [
          '#markup' => $this->t('Use your membership ID now to instantly create an online account and gain access to discounts 
          from our webstore available only to special members!'),
        ],
        '#attributes' => [
          'class' => ['gateway-login__offer'],
        ],
      ],
      'buttons' => [
        '#type' => 'container',
        'content' => [
          '#markup' => '<a class="success button" href="/gateway/register">Register Now</a>',
        ],
        '#attributes' => [
          'class' => ['gateway-login__actions'],
        ],
      ],
    ];
  }

}
