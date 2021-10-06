<?php


// initial login page which redirects to correct sign in page, sets some cookies
$URL = 'https://kdp.amazon.com/it_IT/bookshelf';

// declare(strict_types=1);
// header("content-type: text/plain;charset=utf-8");
$email    = '';
$password = '';

$cookieFile = 'amazon-cookie.txt';
$headers = ["User Agent" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1;)Trident/4.0; InfoPath.2; .NET CLR 2.0.50727)\r\n"];


$ch=curl_init();
curl_setopt_array($ch,array(
    CURLOPT_AUTOREFERER => true,
    CURLOPT_BINARYTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_COOKIEJAR => $cookieFile, // <<makes curl save/load cookies across requests..
    CURLOPT_COOKIEFILE => $cookieFile, // <<makes curl save/load cookies across requests..
    CURLOPT_ENCODING => "", // << makes curl post all supported encodings, gzip/deflate/etc, makes transfers faster
    CURLOPT_USERAGENT => 'whatever; curl/' . (curl_version() ['version']) . ' (' . (curl_version() ['host']) . '); php/' . PHP_VERSION,
    CURLOPT_RETURNTRANSFER=>1,
    CURLOPT_HEADER=>1,
    CURLOPT_HTTPHEADER=>$headers,
    CURLOPT_URL=>$URL,
));
$html=curl_exec($ch);
//var_dump($html) & die();
$domd=@DOMDocument::loadHTML($html);
$xp=new DOMXPath($domd);
$form=$xp->query("//form[@name='signIn']")->item(0);
$inputs=[];
foreach($form->getElementsByTagName("input") as $input){
    $name=$input->getAttribute("name");
    if(empty($name) && $name!=="0"){
        continue;
    }
    $inputs[$name]=$input->getAttribute("value");
}
assert(isset($inputs['email'],$inputs['password'],
$inputs['appActionToken'],$inputs['workflowState'],
$inputs['rememberMe']),"missing form inputs!");
$inputs["email"]=$email;
$inputs["password"]=$password;
$inputs["rememberMe"]="false";
$login_url=$form->getAttribute("action");
var_dump($inputs,$login_url);
curl_setopt_array($ch,array(
CURLOPT_URL=>$login_url,
CURLOPT_POST=>1,
CURLOPT_POSTFIELDS=>http_build_query($inputs)
));
$html=curl_exec($ch);
$domd=@DOMDocument::loadHTML($html);
$xp=new DOMXPath($domd);
$loginErrors=[];
// warning-message-box is also used for login *errors*, amazon web devs are just being stupid with the names.
foreach($xp->query("//*[contains(@id,'error-message-box')]|//*[contains(@id,'warning-message-box')]") as $loginError){
    $loginErrors[]=preg_replace("/\s+/"," ",trim($loginError->textContent));
}
if(!empty($loginErrors)){
    echo "login errors: ";
    var_dump($loginErrors);
    die();
}
//var_dump($html);
echo "login successful!";
