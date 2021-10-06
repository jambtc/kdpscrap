<?php
$secrets = require __DIR__ . '/secrets.php';

if (gethostname() == $secrets['hostname']){
	$myName = $secrets['domain_name'];	// username
	$myPwd = $secrets['domain_password'];	// password
	$proxy = $secrets['proxy_url'];	// proxy url and port
	$proxydata = $myName.":".$myPwd;

	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
	curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxydata);
}
?>
