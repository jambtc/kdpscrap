<h1>Scrap KDP Amazon</h1>

<?php
ini_set('display_errors', 'On');

require 'webrequest.php';

$secrets = require __DIR__ . '/secrets.php';


// login page
$url_login = 'https://kdp.amazon.com/en_US/bookshelf';


// set php class
$wr = new webrequest($secrets['username'], $secrets['password']);

// grab the login page
$login = $wr->login($url_login);


echo '<pre>'.print_r($login,true).'</pre>';
exit;



echo '<pre>'.print_r($inputs,true).'</pre>';
echo '<pre>'.print_r($login_url,true).'</pre>';
// echo '<pre>'.print_r($str,true).'</pre>';
exit;

$login = webrequest($login_url, $str['last_url'], $inputs, 'POST' );

$domd = @DOMDocument::loadHTML($login['body']);
$xp = new DOMXPath($domd);
$loginErrors=[];
// warning-message-box is also used for login *errors*, amazon web devs are just being stupid with the names.
foreach($xp->query("//*[contains(@id,'error-message-box')]|//*[contains(@id,'warning-message-box')]") as $loginError){
    $loginErrors[]=preg_replace("/\s+/"," ",trim($loginError->textContent));
}
if(!empty($loginErrors)){
    echo "login errors: ";
    echo '<pre>'.print_r($loginErrors,true).'</pre>';
    die();
}
echo "login successful!";
echo '<pre>'.print_r($login,true).'</pre>';
