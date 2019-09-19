<?php
/**
 * @file
 * Contains \Drupal\wdfm_gateway_integration\Form\WDFMGatewayRegisterForm.
 *
 * credits to: https://gist.github.com/davidDuymelinck/cd20ab7049749358717127f12666b68c
 */

namespace Drupal\wdfm_gateway_integration\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\Profile;
use Drupal\user\RegisterForm;
use Drupal\wdfm_gateway_integration\Entity\MembershipEntity;
use Drupal\wdfm_gateway_integration\WDFMConnectionClient;

/**
 * Provides a user register form.
 */
class WDFMGatewayRegisterForm extends RegisterForm {

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['method'] = [
      '#type' => 'fieldset',
      '#title' => 'Select Registration Method',
      '#weight' => -11,
      'registration_method' => [
        '#type' => 'radios',
        '#options' => [
          'membership' => 'Register With Existing WDFM Membership',
          'standalone' => 'Register Without WDFM Membership',
        ],
        '#default_value' => $form_state->getValue('registration_method', 'membership'),
      ],
      'divider' => [
        '#markup' => '<hr>',
      ],
    ];
    $form['membership'] = [
      '#type' => 'fieldset',
      '#title' => 'Confirm Membership Details',
      'description' => [
        '#markup' => '<p>' .
          t('To create a new account using your existing WDFM membership, please enter the barcode number from the back of your current member card and last name of the primary member to access this page.') .
          '<br/><strong>' .
          t('If you are experiencing any issues, please call the membership department 415.345.6810') .
          '</strong></p>',
      ],
      'barcode' => [
        '#type' => 'textfield',
        '#title' => 'Membership Barcode Number',
        '#description' => 'Enter the number printed underneath the barcode on your membership ID card or membership materials',
        '#attributes' => [
          'placeholder' => 'Enter Barcode Number'
        ],
      ],
      'last_name' => [
        '#type' => 'textfield',
        '#title' => 'Member\'s Last Name',
        '#description' => 'Must match the last name registered with membership',
        '#attributes' => [
          'placeholder' => 'Enter Last Name'
        ],
      ],
    ];

    if(!empty($form['account']['pass'])) {
      $form['account']['pass']['#required'] = FALSE;
      $form['membership']['member_pass'] = $form['account']['pass'];
      $form['membership']['member_notify'] = $form['account']['notify'];
    }

    $form['account']['#type'] = 'fieldset';
    $form['account']['#title'] = t('Select Account Details');

    $form['account']['mail']['#required'] = FALSE;
    $form['account']['name']['#required'] = FALSE;

    $form['actions']['submit']['#value'] = t('Register New Account Without Membership');

    $form['actions']['register_membership'] = [
      '#type' => 'submit',
      '#value' => t('Register New Account With Membership'),
      '#button_type' => 'primary',
      '#submit' => [
        '::submitForm',
        'wdfm_gateway_integration_register_submit',
      ],
    ];

    /** Set conditional sections of form */

    $form['membership']['#states'] = [
      'visible' => [
        'input[name="registration_method"]' => ['value' => 'membership'],
      ],
    ];
    $form['actions']['register_membership']['#states'] = [
      'visible' => [
        'input[name="registration_method"]' => ['value' => 'membership'],
      ],
    ];
    if(!empty($form['membership']['member_pass'])) {
      $form['membership']['member_pass']['#states'] = [
        'required' => [
          'input[name="registration_method"]' => ['value' => 'membership'],
        ],
      ];
    }
    $form['account']['mail']['#states'] = [
      'required' => [
        'input[name="registration_method"]' => ['value' => 'standalone'],
      ],
    ];
    $form['account']['name']['#states'] = [
      'required' => [
        'input[name="registration_method"]' => ['value' => 'standalone'],
      ],
    ];
    if(!empty($form['account']['pass'])) {
      $form['account']['pass']['#states'] = [
        'required' => [
          'input[name="registration_method"]' => ['value' => 'standalone'],
        ],
      ];
    }
    $form['account']['#states'] = [
      'visible' => [
        'input[name="registration_method"]' => ['value' => 'standalone'],
      ],
    ];
    $form['actions']['submit']['#states'] = [
      'visible' => [
        'input[name="registration_method"]' => ['value' => 'standalone'],
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    if(!empty($storage['data'])) {
      unset($storage['data']);
    }
    if(!empty($storage['profile'])) {
      unset($storage['profile']);
    }
    $form_state->setStorage($storage);
    if($form_state->getValue('registration_method') == 'membership') {
      if(!empty($form_state->getValue('member_pass'))) {
        $form_state->setValue('pass', 'member_pass');
      }
      if(!empty($form_state->getValue('member_notify'))) {
        $form_state->setValue('notify', 'member_notify');
      }
      if(empty($form_state->getValue('barcode'))) {
        $form_state->setErrorByName('barcode', t('Membership barcode cannot be blank.'));
      }
      if(empty($form_state->getValue('last_name'))) {
        $form_state->setErrorByName('last_name', t('Member last name cannot be blank.'));
      }
      $barcode = $form_state->getValue('barcode');
      $last_name = $form_state->getValue('last_name');
      $client = new WDFMConnectionClient();
      $client->setup('wdfm_gateway_dev', '1');
      $response = $client->checkMembership(
        $barcode,
        'LastName',
        $last_name);
      $response = json_encode($response);
      $response = json_decode($response,TRUE);
      $statusCode = $response['Status']['StatusCode'];
      if($statusCode == "0" && !empty($response['QueryTicketResponse']['Products']['Product']['DataRequestResponse'])) {
        $data = $response['QueryTicketResponse']['Products']['Product']['DataRequestResponse'];
        if($data['isValid'] == "True") {
          $is_unique = FALSE;
          $suffix = 1;
          $username = $data['FirstName'] . $data['LastName'];
          while(!$is_unique) {
            if($suffix != 1) {
              $username = $data['FirstName'] . $data['LastName'] . '_' . $suffix;
            }
            $ids = \Drupal::entityQuery('user')
              ->condition('name', $username)
              ->range(0, 1)
              ->execute();
            if(empty($ids)){
              $is_unique = TRUE;
            }
            $suffix++;
          }
          $ids = \Drupal::entityQuery('membership')
            ->condition('id', $barcode)
            ->range(0, 1)
            ->execute();
          if(!empty($ids)) {
            $form_state->setErrorByName('membership', t('This membership is already associated with an active account.'));
          } else {
            $form_state->setValue('name', $username);
            $form_state->setValue('mail', $data['Email']);
            $form_state->setValue('roles', ['wdfm_member']);
            $storage = $form_state->getStorage();
            $storage['data'] = $data;
            $form_state->setStorage($storage);
          }
        } else {
          $form_state->setErrorByName('membership', t('This membership is not currently valid.  Current status is ' . $data['StatusDescription']));
        }
      } else {
        $form_state->setErrorByName('membership', t('Your membership details could not be validated.'));
      }
    }
    return parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    if($form_state->getValue('registration_method') == 'membership') {
      $entity = $this->entity;
      $data = $form_state->getStorage()['data'];
      $profile = Profile::create([
        'type' => 'customer',
        'address' => [
          'country_code' => 'US',
          'postal_code' => !empty($data['ZIP']) ? $data['ZIP'] : '',
          'locality' => !empty($data['City']) ? $data['City'] : '',
          'address_line1' => !empty($data['Street1']) ? $data['Street1'] : '',
          'address_line2' => !empty($data['Street2']) ? $data['Street2'] : '',
          'administrative_area' => !empty($data['State']) ? $data['State'] : '',
          'given_name' => !empty($data['FirstName']) ? $data['FirstName'] : '',
          'family_name' => !empty($data['LastName']) ? $data['LastName'] : '',
        ],
        'field_contact_phone' => !empty($data['Phone']) ? $data['Phone'] : '',
      ]);
      $profile->save();
      $entity->set('customer_profiles', [
        $profile
      ]);
      $this->setEntity($entity);

      if(!empty($data['PassKindName'])) {
        $tids = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', 'membership_types')
          ->condition('name', '%' . $data['PassKindName'] . '%', 'LIKE')
          ->accessCheck(FALSE)
          ->range(0, 1)
          ->execute();
        if(!empty($tids)) {
          foreach($tids as $tid) {
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
          }
        }
      }
      $membership = MembershipEntity::create([
        'bundle' => 'wdfm_membership',
        'id' => $form_state->getValue('barcode'),
        'first_name' => !empty($data['FirstName']) ? $data['FirstName'] : '',
        'last_name' => !empty($data['LastName']) ? $data['LastName'] : '',
        'street1' => !empty($data['Street1']) ? $data['Street1'] : '',
        'street2' => !empty($data['Street2']) ? $data['Street2'] : '',
        'city' => !empty($data['City']) ? $data['City'] : '',
        'state' => !empty($data['State']) ? $data['State'] : '',
        'zip' => !empty($data['ZIP']) ? $data['ZIP'] : '',
        'email' => !empty($data['Email']) ? $data['Email'] : '',
        'is_valid' => 1,
        'valid_until' => !empty($data['ValidUntil']) ? strtotime($data['ValidUntil']) : '',
      ]);
      if(!empty($term->id())) {
        $membership->set('membership_type', $term->id());
      }
      $membership->save();
      $storage = $form_state->getStorage();
      $storage['profile'] = $profile;
      $storage['membership'] = $membership;
      $form_state->setStorage($storage);
      if(empty($form_state->getValue('administer_users'))) {
        $form_state->setRedirectUrl(Url::fromUserInput('/registration/welcome-new-account'));
      }
      parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
    } else {
      parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
    }
  }

  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state); // TODO: Change the autogenerated stub
    $admin = $form_state->getValue('administer_users');
    if(!$admin) {
      $form_state->setRedirectUrl(Url::fromUserInput('/registration/welcome-new-account'));
    }
    $storage = $form_state->getStorage();
    if(!empty($storage['profile'])) {
      $profile = $storage['profile'];
      $profile->set('uid', $this->entity->id());
      $profile->save();
    }
    if(!empty($storage['membership'])) {
      $membership = $storage['membership'];
      $membership->set('uid', $this->entity->id());
      $membership->save();
    }
  }
}
