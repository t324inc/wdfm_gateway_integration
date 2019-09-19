<?php
/**
 * @file
 * Contains \Drupal\wdfm_gateway_integration\Form\WDFMGatewayRegisterForm.
 *
 * credits to: https://gist.github.com/davidDuymelinck/cd20ab7049749358717127f12666b68c
 */

namespace Drupal\wdfm_gateway_integration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\wdfm_gateway_integration\Entity\MembershipEntity;
use Drupal\wdfm_gateway_integration\WDFMConnectionClient;

/**
 * Provides a user register form.
 */
class WDFMAttachMembershipForm extends FormBase {

  public function getFormId() {
    return 'wdfm_attach_membership_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $form['form'] = [
      '#type' => 'container',
      '#title' => 'Attach a WDFM Membership',
      'description' => [
        '#markup' => '<p>' .
          t('To add an existing WDFM Membership to your account, please enter the barcode number from the back of your current member card and last name of the primary member to access this page.') .
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
      'actions' => [
        '#type' => 'actions',
        'register_membership' => [
          '#type' => 'submit',
          '#value' => t('Attach Membership to Account'),
          '#button_type' => 'primary',
          '#submit' => [
            '::submitForm',
          ],
        ]
      ]
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
        $storage = $form_state->getStorage();
        $storage['data'] = $data;
        $form_state->setStorage($storage);
      } else {
        $form_state->setErrorByName('membership', t('This membership is not currently valid.  Current status is ' . $data['StatusDescription']));
      }
    } else {
      $form_state->setErrorByName('membership', t('Your membership details could not be validated.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $user = $build_info['args'][0];
    $data = $form_state->getStorage()['data'];

    /**
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
    $this->setEntity($entity);
    **/
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
      'uid' => $user->id(),
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
    $user->addRole('wdfm_member');
    $user->save();
  }

  /**
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
   **/
}
