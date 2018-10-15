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

    // Verifica se há tokens temporário

    $session = $request->getSession();

    if( empty($session->get('temporary_credentials','')) ) {

        $temporaryCredentials = $server->getTemporaryCredentials();
        $session->set('temporary_credentials', serialize($temporaryCredentials));
        $url = $server->getAuthorizationUrl($temporaryCredentials) . '&callback_id=' . $config->get('callback_id');
        return new TrustedRedirectResponse($url);
    }
    else {

        //return $this->redirect("senhaunicausp.login_controller_callback");
        $temporaryCredentials = unserialize($session->get('temporary_credentials',false));
        //$tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);

      //$user = $server->getUserDetails($tokenCredentials);
      
echo '<pre>';
//var_dump($_SESSION);
var_dump($temporaryCredentials);
echo '</pre>';
die('msorri');
    }

  }


}
