<?php
require 'vendor/autoload.php';
use League\OAuth2\Client\Provider\Google;

session_start();
require __DIR__ . '/../config.php';

$provider = new Google([
    'clientId'     => $googleClientId ,
    'clientSecret' => $googleClientSecret,
    'redirectUri'  => 'https://www.calicehockey.com/callback.php',
]);

$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();
header('Location: ' . $authUrl);
exit;