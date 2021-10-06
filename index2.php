<h1>Scrap Amazon</h1>

<?php
ini_set('display_errors', 'On');

include('webrequest.php');

// get DOM from URL or file
$login_page = 'https://kdp.amazon.com/en_US/bookshelf';
$str = webrequest($login_page,$login_page, [], 'GET', true);
// echo '<pre>'.print_r($str,true).'</pre>';
// exit;


// $html = @DOMDocument::loadHTML($str['body']);
// sleep(1);
// $xp = new DOMXPath($html);
//
// $form = $xp->query("//form[@name='signIn']")->item(0);
// $inputs=[];
// foreach($form->getElementsByTagName("input") as $input){
//     $name = $input->getAttribute("name");
//     echo '<pre>'.print_r($name,true).'</pre>';
//
//     if(empty($name) && $name!=="0"){
//         continue;
//     }
//     $inputs[$name] = $input->getAttribute("value");
// }

// $test = §


$inputs['email'] = '';
$inputs['password'] = '';
$login_url = $form->getAttribute("action");

echo '<pre>'.print_r($inputs,true).'</pre>';
echo '<pre>'.print_r($login_url,true).'</pre>';
exit;

$login = webrequest($login_url, $str['last_url'], $inputs, 'POST' );
// echo '<pre>'.print_r($login,true).'</pre>';
// exit;

$domd = @DOMDocument::loadHTML($login['body']);
$xp = new DOMXPath($domd);
$loginErrors = [];
// warning-message-box is also used for login *errors*, amazon web devs are just being stupid with the names.
foreach($xp->query("//*[contains(@id,'error-message-box')]|//*[contains(@id,'warning-message-box')]") as $loginError){
    $loginErrors[] = preg_replace("/\s+/"," ",trim($loginError->textContent));
}
if(!empty($loginErrors)){
    echo "login errors: ";
    echo '<pre>'.print_r($loginErrors,true).'</pre>';
    die();
}
echo "login successful!";
echo '<pre>'.print_r($login,true).'</pre>';
