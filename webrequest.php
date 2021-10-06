<?php
class webrequest {
	private $username;
	private $password;

	public function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
    }

	/**
	 * login()
	 * @param URL Path $url
	 * @return Html page containing data returned from the path
	*/

	public function login($url){
		$cookieFile = dirname(__FILE__) . '/cookie/amazon-cookie.txt';
		$useragent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1;)Trident/4.0; InfoPath.2; .NET CLR 2.0.50727)';
		static $ch = null;

		if (is_null($ch)){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // man-in-the-middle defense by verifying ssl cert.

		// curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // man-in-the-middle defense by verifying ssl cert.
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);  // Enables session support
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		require __DIR__ . '/httpproxy.php';

		// run the query
		$get_page = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($get_page, 0, $header_size);
        $result['body'] = substr( $get_page, $header_size );
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		// echo '<pre>'.print_r($result,true).'</pre>';
		// exit;

		$html = @DOMDocument::loadHTML($result['body']);
		$xp = new DOMXPath($html);

		$form = $xp->query("//form[@name='signIn']")->item(0);
		if (null === $form){
			die('No page grabbed!');
		}
		$inputs=[];
		foreach($form->getElementsByTagName("input") as $input){
		    $name = $input->getAttribute("name");
		    // echo '<pre>'.print_r($name,true).'</pre>';
		    if(empty($name) && $name!=="0"){
		        continue;
		    }
		    $inputs[$name] = $input->getAttribute("value");
		}

		$inputs['email'] = $this->username;
		$inputs['password'] = $this->password;
		$inputs['metadata1'] = 'true';
		// echo '<pre>'.print_r($inputs,true).'</pre>';
		// exit;

		// generate the POST data string
        $post_data = http_build_query($inputs, '', '&');

		// extra headers
		$headers["User Agent"] = $useragent;
		// echo '<pre>'.print_r($headers,true).'</pre>';

		// wait for human time
		sleep(2);

		curl_setopt($ch, CURLOPT_URL, $result['last_url'] );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		$html = @DOMDocument::loadHTML($result['body']);
		$xp = new DOMXPath($html);

		$captcha = $xp->query("//img[@id='auth-captcha-image']")->item(0);
		if (null !== $captcha){
			$imageurl = urldecode($captcha->getAttribute('src'));

			$new_file_name = __DIR__ . "/captcha/captcha.jpg";
			$temp_file_contents = $this->collect_file($imageurl);
			$this->write_to_file($temp_file_contents, $new_file_name);



			// .... working

			
			die();

		}






		echo '<pre>'.print_r($result,true).'</pre>';
		exit;



        return $result;
	}

	private function collect_file($url){
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $url);
	   curl_setopt($ch, CURLOPT_VERBOSE, 1);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($ch, CURLOPT_AUTOREFERER, false);
	   curl_setopt($ch, CURLOPT_REFERER, "http://www.xcontest.org");
	   curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	   curl_setopt($ch, CURLOPT_HEADER, 0);
	   require __DIR__ . '/httpproxy.php';

	   $result = curl_exec($ch);
	   curl_close($ch);
	   return($result);
   }

   private function write_to_file($text,$new_filename){
	   $fp = fopen($new_filename, 'w');
	   fwrite($fp, $text);
	   fclose($fp);
   }
}
