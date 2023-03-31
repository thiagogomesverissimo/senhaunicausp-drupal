<?php

namespace Drupal\senhaunicausp\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\senhaunicausp\Utils\ServerUSP;
use GuzzleHttp\Client;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  /**
   * Login.
   */
  public function login(Request $request) {

    $config = $this->config('senhaunicausp.config');
    $session = $request->getSession();

    // Verifica se o módulo está configurado
    if( empty($config->get('key_id')) || empty($config->get('secret_key')) || empty($config->get('default_role')) ) {
      \Drupal::messenger()->addMessage('Módulo Senha Única USP ainda não configurado!');
      return $this->redirect('<front>');
    }

    $server = new ServerUSP([
        'identifier' => $config->get('key_id'),
        'secret' => $config->get('secret_key'),
    ]);

    if( !is_null($session->get('token_credentials') )) {
      $tokenCredentials = unserialize($session->get('token_credentials'));
      $data = $server->getUserDetails($tokenCredentials);

      // Verifica se o usuário em questão tem permissão para logar
      if( !empty($config->get('numeros_usp')) ) {

        $numeros_usp = $config->get('numeros_usp');
        $numeros_usp = array_map('trim', explode(',', $numeros_usp));

        if(!in_array($data->uid,$numeros_usp)) {
          \Drupal::messenger()->addMessage(t('Desculpe-nos! Você não permissão para logar nesse site.'), 'error');
          return $this->redirect('<front>');
        }
      }

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

      // role
      $role = $config->get('default_role');
      if ($role != 'authenticated') {
        $user->addRole($role);
      }

      // Bem, user não deve ter sem senha local...
      $user->setPassword(FALSE);

      // Populando os campos criados em Drupal Accounts Fields
      // admin/config/people/accounts/fields
      // Array com as chaves (sufixo do field name no Drupal accounts fields) e o valor no OAuth USP
      // field name no Drupal = field_nompes = Nome Completo
      $profile = array (
        'nompes'    => $data->name, # Nome Completo
        'codpes'    => $data->uid, # Nº USP
        'nomabvset' => $data->extra[0]['nomeAbreviadoSetor'], # Sigla do Setor
        'nomset'    => $data->extra[0]['nomeSetor'], # Nome do Setor
        'sglund'    => $data->extra[0]['siglaUnidade'], # Sigla da Unidade
        'nomund'    => $data->extra[0]['nomeUnidade'], # Nome da Unidade
        'nomvin'    => $data->extra[0]['nomeVinculo'] # Nome do Vínculo
      );

      // Pega os vínculos retornados no OAuth
      $vinculosOauth = $data->extra;
      // Cria um array de vinculos vazio
      $vinculos = array();
      // Laço para popular o array de vinculos
      foreach ($vinculosOauth as $vinculo) {
        // Guarda o vínculo no array de vinculos
        array_push($vinculos, $vinculo['nomeVinculo']);
      }
      // Transforma o array de vinculos em string
      $vinculos = implode(',', $vinculos);
      // Guarda a string vinculos no array profile
      $profile['nomvin'] = $vinculos;

      foreach ($profile as $chave => $valor) {
        // Se existe o campo no Drupal accounts fields, popula
        if ($user->hasField("field_$chave")) {
          // Se no OAuth não retornar valor, grava valor = ' ';
          // Isso possibilita criar os campos customizados sem ser obrigatório
          if (empty($valor)) {
            $user->set("field_$chave", ' ');
          } else {
            $user->set("field_$chave", $valor);
          }
        }
      }

      //Save user.
      $user->save();

      // Loga usuário
      user_login_finalize($user);

      \Drupal::messenger()->addMessage(t('Login efetuado com sucesso!'), 'status');

      return $this->redirect('<front>');

    } elseif ( !is_null($request->get('oauth_token')) && !is_null($request->get('oauth_verifier')) ) {
      $temporaryCredentials = unserialize($session->get('temporary_credentials'));

      $tokenCredentials = $server->getTokenCredentials($temporaryCredentials,
                          $request->get('oauth_token'),
                          $request->get('oauth_verifier'));

      $session->set('token_credentials', serialize($tokenCredentials));

      return $this->redirect('senhaunicausp.login_controller_login');

    } else {
      $temporaryCredentials = $server->getTemporaryCredentials();
      $session->set('temporary_credentials', serialize($temporaryCredentials));
      $url = $server->getAuthorizationUrl($temporaryCredentials) . '&callback_id=' . $config->get('callback_id');
      return new TrustedRedirectResponse($url);
    }
  }

}
