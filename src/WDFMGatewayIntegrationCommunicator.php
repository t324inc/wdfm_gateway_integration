<?php

namespace Drupal\wdfm_gateway_integration;

use Drupal\commerce_payment\Exception\InvalidRequestException;
use Drupal\simple_integrations\ConnectionClient;
use Drupal\simple_integrations\Exception\IntegrationInactiveException;
use Drupal\simple_integrations\IntegrationInterface;
use GuzzleHttp\Client;
use http\Exception\RuntimeException;
use Meng\AsyncSoap\Guzzle\Factory;
use WebDriver\Exception\InvalidRequest;

class WDFMGatewayIntegrationMembershipCommunicator {

  /**
   * @var ConnectionClient
   */
  protected $client;

  /**
   * @var IntegrationInterface
   */
  protected $integration;

  public function __construct(ConnectionClient $client) {
    $this->client = $client;
  }

  public function setIntegration(IntegrationInterface $integration) {
    $this->integration = $integration;
    $this->client->setIntegration($integration);
    $this->client->configure();
  }

  public function addCommonHeaders() {
    if(!isset($this->integration)) {
      throw new \RuntimeException('No integration entity specified for the connection client.');
    }
  }

  public function authenticateMembership($barcode, $last_name) {
    $request = $this->client->get($this->client->getRequestEndPoint(), $this->client->getConfig());
    return $request;
  }

}
