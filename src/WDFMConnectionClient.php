<?php

namespace Drupal\wdfm_gateway_integration;

use Drupal\migrate\Plugin\migrate\process\UrlEncode;
use Drupal\simple_integrations\ConnectionClient;
use SimpleXMLElement;

/**
 * The connection client.
 *
 * Acts as an extension of core's http_client service, with additional
 * functionality to configure the request automatically.
 */
class WDFMConnectionClient extends ConnectionClient {

  /**
   * The Galaxy source ID value.
   *
   * @var string
   */
  private $sourceID;

  /**
   * Perform setup tasks.
   *
   * @param string $integrationID
   *    The ID of the Simple Integration entity
   * @param string $sourceID
   *    The Source ID for the Galaxy message
   */
  public function setup(string $integrationID, string $sourceID) {
    $integration = \Drupal::entityTypeManager()
      ->getStorage('integration')
      ->load($integrationID);
    $this->setIntegration($integration);
    $this->sourceID = $sourceID;
    $this->configure();
  }

  /**
   * Create the XML envelope for the Gateway API message
   *
   * @param string $messageType
   *
   * @return SimpleXMLElement
   */
  private function createEnvelope(string $messageType) {
    $envelope = new SimpleXMLElement("<Envelope><Header></Header><Body></Body></Envelope>");
    $envelope->Header->addChild('MessageType', $messageType);
    $envelope->Header->addChild('SourceID', $this->sourceID);
    $envelope->Header->addChild('TimeStamp', date('Y-m-d H:i:s'));
    return $envelope;
  }

  /**
   * Create the XML envelope for the Gateway API message
   *
   * @param string $membershipCode
   *    Membership code from a WDFM membership card
   * @param string $membershipKey
   *    Key to validate membership against (LastName, Email)
   * @param string $membershipValue
   *    Value of key to validate membership against
   *
   * @return SimpleXMLElement
   *    Response data formatted as SimpleXMLElement
   */
  public function checkMembership(string $membershipCode, string $membershipKey, string $membershipValue) {
    $message = $this->createEnvelope('QueryTicket');
    $message->Body->addChild('QueryTicket');
    $message->Body->QueryTicket->addChild('Queries');
    $message->Body->QueryTicket->Queries->addChild('Query');
    $message->Body->QueryTicket->Queries->Query->addChild('VisualID', $membershipCode);
    $message->Body->QueryTicket->Queries->Query->addChild('IncludeIsValid', 'YES');
    $message->Body->QueryTicket->Queries->Query->addChild($membershipKey, $membershipValue);
    $message->Body->QueryTicket->addChild('DataRequest');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'isValid');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'FirstName');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'LastName');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'Street1');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'Street2');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'City');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'State');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'ZIP');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'CountryCode');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'Phone');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'Email');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'DOB');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'ValidUntil');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'CustomerID');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'Contact');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'GalaxyContactID');
    $message->Body->QueryTicket->DataRequest->addChild('Field', 'StatusDescription');

    // Send the message
    return $this->sendXMLMessage($message);
  }

  /**
   * Send the XML message to API
   *
   * @param SimpleXMLElement $message
   *    XML structured message for Galaxy API
   *
   * @return SimpleXMLElement
   *    Response data formatted as SimpleXMLElement
   */
  public function sendXMLMessage(SimpleXMLElement $message) {
    $config = $this->getConfig();
    $config['headers']['Content-Type'] = "text/xml; charset=UTF8";
    $config['body'] = $message->asXML();
    $response = $this->post($this->getRequestEndPoint(), $config);
    $response_contents = $response->getBody();
    $responseXML = simplexml_load_string($response_contents);
    return $responseXML->Body;
  }
}
