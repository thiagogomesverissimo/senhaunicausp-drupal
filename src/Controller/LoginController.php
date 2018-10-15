<?php

namespace Drupal\senhaunicausp\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\senhaunicausp\Utils\ServerUSP;

# TODO: avaliar se mantemos os dados na sessão privados:
# https://drupal.stackexchange.com/questions/197576/storing-data-session-for-anonymous-user?rq=1


/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {


  /**
   * Login.
   */
  public function login(Request $request) {
   
    // Verifica se o módulo está configurado
    //if(!empty($config->get('key_id')) )
    // set flash messages
    //$session->getFlashBag()->add('notice', 'Profile updated');

    $config = $this->config('senhaunicausp.config');
    $session = $request->getSession();

    $server = new ServerUSP([
        'identifier' => $config->get('key_id'),
        'secret' => $config->get('secret_key'),
    ]);

    if( !is_null($session->get('token_credentials') )) {
      $tokenCredentials = unserialize($session->get('token_credentials'));
      $data = $server->getUserDetails($tokenCredentials);

      $user = user_load_by_name($data->uid);
      if (empty($user)) {
        $user = \Drupal\user\Entity\User::create();
        $user->setUsername($data->uid);
        $user->enforceIsNew();
      }
      $user->setEmail($data->email);
      
      // Configura língua default do sistema
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $user->set("langcode", $language);
      $user->set("preferred_langcode", $language);
      $user->set("preferred_admin_langcode", $language);

      // não sei o que faz, mas se não colocarmos não cria o usuário
      $user->set("init", 'email');

      // Ativa usuário
      $user->activate();

      // Bem, user não deve ter sem senha local...
      $user->setPassword(FALSE);
          
      //Save user.
      $user->save();

      // Loga usuário
      user_login_finalize($user);

      return $this->redirect('<front>');

    } elseif ( !is_null($request->get('oauth_token')) && !is_null($request->get('oauth_verifier')) ) {
      $temporaryCredentials = unserialize($session->get('temporary_credentials'));
     
      $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, 
                          $request->get('oauth_token'), 
                          $request->get('oauth_verifier'));

      $session->set('token_credentials', serialize($tokenCredentials));
      // TODO: podemos descartar os token temporários
      //unset($_SESSION['temporary_credentials']);

      return $this->redirect('senhaunicausp.login_controller_login');
      //return new Response();
  
    } else {
      $temporaryCredentials = $server->getTemporaryCredentials();
      $session->set('temporary_credentials', serialize($temporaryCredentials));
        $session->set('thiago', 'gomes');
      $url = $server->getAuthorizationUrl($temporaryCredentials) . '&callback_id=' . $config->get('callback_id');
      return new TrustedRedirectResponse($url);
    }
  }

}
