<?php

namespace Drupal\senhaunicausp\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Drupal\Core\Controller\ControllerBase;

use Drupal\senhaunicausp\Utils\ServerUSP;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  /**
   * Login.
   */
  public function login(Request $request) {

    $config = $this->config('senhaunicausp.config');

    $server = new ServerUSP([
        'identifier' => $config->get('key_id'),
        'secret' => $config->get('secret_key'),
    ]);

    $temporaryCredentials = $server->getTemporaryCredentials();

    $session = $request->getSession();
    $value = $session->get('senhaunicausp.temporary_credentials');
    

    $session->set('senhaunicausp', serialize($temporaryCredentials));

    print_r($session->get('senhaunicausp.temporary_credentials')); die();


    $url = $server->getAuthorizationUrl($temporaryCredentials) . '&callback_id=' . $config->get('callback_id');

    return new TrustedRedirectResponse($url);

  }

}
