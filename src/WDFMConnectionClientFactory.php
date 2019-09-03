<?php

namespace Drupal\wdfm_gateway_integration;

/**
 * Custom implementation of the Drupal HTTP client.
 *
 * Returns a new connection client, which acts as an extension of core's
 * http_client service.
 */
class WDFMConnectionClientFactory {

  /**
   * Return a WDFMConnection client.
   *
   * @return WDFMConnectionClient
   *   A connection client.
   */
  public function get() {
    return new WDFMConnectionClient();
  }

}
