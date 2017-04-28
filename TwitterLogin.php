<?php
/*
 *動作確認していない
 * twittreログイン用の関数*/
/*php用のtwitterSDKを入れておいてね！*/
require('twitteroauth/autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;
use Phalcon\Mvc\Controller;

define('API_KEY', '');
define('API_SECRET', '');

//twitterログイン画面に移動
function redirect(){
	$callback_url = "";
	$connection = new TwitterOAuth(API_KEY, API_SECRET);
	$request_token = $connection->oauth("oauth/request_token", array('oauth_callback' => $redirect_url));
	$twitter_login_url = 'https://api.twitter.com/oauth/authenticate?oauth_token='. $request_token['oauth_token'];
	header("Location: ${twitter_login_url}");
	return; //or exit();
}


function oauth(){
	if(isTokenCorrect($_GET) == false) return;
	$oauth_verifier = $_GET['oauth_verifier'];
	$oauth_token = $_GET['oauth_token'];


	$access_token = accessToken($oauth_token, $oauth_verifier);
	$user_info = userGet($access_token['oauth_token'], $access_token['oauth_token_secret']);

	userCreate($user_info);
}

//tokenが正しく送られてきているかのチェック
function isTokenCorrect($params){
	if(isset($params['oauth_token']) && isset($params['oauth_varifier'])){
		return true;
	}else if(isset($params['denied'])){
		//拒否の場合はdeniedを取得
		echo $params['denied'];
		return false;
	}else{
		echo "なぜここにきた？";
		return false;
	}
}

//twitterからアクセストークンの取得
function accessToken($oauth_token, $oauth_verifier){
	$connection = new TwitterOAuth(API_KEY, API_SECRET, $oauth_token, $oauth_verifier);
	$access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $oauth_verifier, 'oauth_token' => $oauth_token));
	return $access_token;
}


//ユーザー情報の取得
function userGet($oauth_token, $oauth_token_secret){
	$user_connection = new TwitterOAuth(API_KEY, API_SECRET, $oauth_token, $oauth_token_secret);
	$user_info = $user_connection->get('account/verify_credentials');
	return $user_info;
}


function userCreate($user_info){
	//DBに入れる
}
