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

if (isset($_GET['code'])) {
    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        $ownerDetails = $provider->getResourceOwner($token);
        $email = $ownerDetails->getEmail();
        $name  = $ownerDetails->getName();

        $allowed_emails = [
            "tianshu_liu81@berkeley.edu",
            "kristian.seppanen@berkeley.edu",
            "jburbank@berkeley.edu",
            "Enzogoebel@berkeley.edu",
            "dolimsean@berkeley.edu",
            "brybartolo2@berkeley.edu",
            "Brendan.baker@berkeley.edu",
            "Ryan_wan_lee@berkeley.edu",
            "jason_lee@berkeley.edu",
            "Kayden@berkeley.edu",
            "ysakbas@berkeley.edu",
            "mark.rejna@berkeley.edu",
            "allan.anaka@berkeley.edu",
            "hconlin@berkeley.edu",
            "kmizuno@berkeley.edu",
            "naha_academy123@berkeley.edu",
            "rchebaclo@berkeley.edu",
            "patrick_nasta@berkeley.edu",
            "eric_khodorenko@berkeley.edu",
            "trent_teruya@berkeley.edu",
            "Acomeau@berkeley.edu",
            "coltenjfazio@berkeley.edu",
            "liam.collins@berkeley.edu",
            "ellis_odowd@berkeley.edu",
            "lucasfung@berkeley.edu",
            "william_hagan@berkeley.edu",
            "simonmantoani@berkeley.edu",
            "ckanas@berkeley.edu",
            "dom_sedlak-braude@berkeley.edu",
            "n.tomic@berkeley.edu"
        ];
        
        if (!in_array($email, array_map('strtolower', $allowed_emails))) {
            $_SESSION['user'] = ['email' => $email];
            header("Location: /not-authorized.php");
            exit;
        }
        // Store user info in session
        $_SESSION['user'] = [
            'email' => $ownerDetails->getEmail(),
            'name'  => $ownerDetails->getName()
        ];

        // Redirect to a protected page
        header("Location: /dashboard.php");
        exit;
    } catch (Exception $e) {
        exit('Login failed: ' . $e->getMessage());
    }

}
