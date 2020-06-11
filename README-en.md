Implements the proper parameterization of OAuth 1.0 used at the University of São Paulo.

<h2>Installation</h2>

<code>
    composer require drupal/senhaunicausp
</code>

<h2>Settings</h2>

https://www.youtube.com/watch?v=BnWZhfQcjS8

<strong>Get consumer key and consumer secret from Oauth Consumidor USP using the url:</strong>

https://uspdigital.usp.br/adminws/oauthConsumidorAcessar

<strong>Set the url consumidor using the url:</strong>

https://yoursite.usp.br/login

<strong>Set credentials using the url:</strong>

https://yoursite.usp.br/admin/config/senhaunicausp

<strong>Once configured, useres will enter your site using url the url:</strong>

https://yoursite.usp.br/login

<h2>Extras</h2>

<strong>You can add extra data from OAuth USP</strong>

The idea is: when the fields bellow exists at account manage fields, 
https://yoursite.usp.br/admin/config/people/accounts/fields, 
they will be filled by this module, using key:

<code>
    oauth               = account field name
    ----------------------------------------
    uid                 = field_codpes
    nomeAbreviadoSetor  = field_nomabvset
    nomeSetor           = field_nomset
    siglaUnidade        = field_sglund
    nomeUnidade         = field_nomund
    nomeVinculo         = field_nomvin
</code>

<strong>Including the option to consume the USP numbers from an API</strong>

<h2>Implements with webform and rules</h2>

https://uspdev.github.io/posts/drupal-senhaunica-rules




