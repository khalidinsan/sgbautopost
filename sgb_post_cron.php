<?php

function get_curl($url,$cookies=false,$post=false){
	$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
	$header = array(
		'host: social.sgbteam.id',
		'accept: */*',
		'accept-Language: en-GB,en;q=0.9,ja-JP;q=0.8,ja;q=0.7,id-ID;q=0.6,id;q=0.5,en-US;q=0.4',
		'referer: https://social.sgbteam.id/',
		'x-requested-with: XMLHttpRequest'
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if($post) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	if($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	$response = curl_exec($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	$result = array($body,$httpcode,$header);
	return $result;
}


function get_cookies($username,$password){
	$field = array('username'=>$username,'password'=>$password);
	$result = get_curl('https://social.sgbteam.id/requests.php?f=login',false,$field);
	preg_match_all('/^set-cookie:\s*([^;]*)/mi', $result[2], $matches);
	$cookies = "";
	foreach($matches[1] as $item) {
		$cookies .= $item.";";
	}
	$arr = json_decode($result[0]);
	if(isset($arr->errors)){
		return false;
	}else{
		return $cookies;
	}
}


function get_hash_id($c){
	$result = get_curl('https://social.sgbteam.id/',$c);
	$pattern = '/<input(.*?)name=\"hash_id\" value=\"(.*?)\"/i';
	preg_match_all($pattern, $result[0], $matches);
	return $matches[2][0];
}

function post($text,$hash_id,$privacy=0,$c){
	$field = array('postText'=>$text,'hash_id'=>$hash_id,'postPrivacy'=>$privacy);
	$result = get_curl('https://social.sgbteam.id/requests.php?f=posts&s=insert_new_post',$c,$field);
	return json_decode($result[0])->status;
}


$username = "khalid";
$password = "Um3n948222";
$interval = 1200;
$c = get_cookies($username,$password);
echo "---------------------\n";
if($c){
	echo "[SUCCESS] Login berhasil\n";
	$lines = file('post.txt');
	$hash_id = get_hash_id($c);
	$text =  $lines[array_rand($lines)];
	$post = post($text,$hash_id,0,$c);
	if($post==200){
		echo "[SUCCESS] Post Sukses\n";
	}else{
		echo "[ERROR] Terjadi kesalahan";
	}
}else{
	echo "[ERROR] Username atau Password ente salah gayn";
}
?>