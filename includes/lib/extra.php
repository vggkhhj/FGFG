<?php
function request($url){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($ch, CURLOPT_PROXYPORT, 29842);
	curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
	curl_setopt($ch, CURLOPT_PROXY, '91.108.64.141');
	curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'ikolpi:HGvm4ZMD');

	$output = curl_exec($ch);

	if ($output === FALSE) {
		//Тут-то мы о ней и скажем
		echo "cURL Error: " . curl_error($ch);
	}
	curl_close($ch);
	return $output;
}
function multi_implode($sep, $array) {
	if(empty($array)) return '';
	foreach($array as $val)
		$_array[] = is_array($val)? multi_implode($sep, $val) : $val;
	return implode($sep, $_array);
}