<?php

namespace Drupal\senhaunicausp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  /**
   * Login.
   *
   * @return string
   *   Return Hello string.
   */
  public function login() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: login')
    ];
  }

}
