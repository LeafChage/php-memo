<?php
/*Facebookログインで使える関数の詰め合わせ*/

define("APP_ID", "");
define("APP_SECRET", "");
define("STATE", "");
define("CALLBACK_URL", "");

//facebookのログイン画面にリダイレクト
function redirect(){
	$callback_url = urlencode(CALLBACK_URL);
	$facebook_redirct_url =  "https://www.facebook.com/dialog/oauth?client_id=". APP_ID. "&redirect_uri=". $callback_url. "&state=". STATE. "&scope=". $scope;
	header("Location: {$facebook_redirct_url}");
	return; // or exit();
}

//フェイスブックとの認証やユーザーの登録
function oauth(){
	if(isCodeCorrect($_GET) == false) return;
	if(isStateCorrect($_GET) == false) return;
	
	$token_array = accessToken($code, CALLBACK_URL);
	if($token_array == null) return;

	$access_token = $token_array['access_token'];
	$user_array = userGet($access_token);
	if($user_array == null) return;

	userCreate($user_array);
	return;
}

//codeが正しくきているのか？
function isCodeCorrect($params){
	//codeのついて確認
	if(isset($params['code'])){
		return true;
	}else if(isset($params['error_reason'])){
		echo $params['error_reason'];
		return false;
	}else{
		echo "なぜここにきた？";
		return false;
	}

}

//facebookに渡したstateが正しいかチェック
function isStateCorrect($params){
	if(isset($params['state'])){
		$state = str_replace('#_=_', '', $params['state']);
		if($state == STATE){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}	
}

//アクセストークンの取得
function accessToken($code, $redirect_url){
	$get_token_url = "https://graph.facebook.com/v2.7/oauth/access_token?client_id=". APP_ID. "&redirect_uri=". $redirect_url. "&client_secret=". APP_SECRET. "&code=${code}";
	if($get_token_json = file_get_contents($get_token_url)){
		$get_token_array = json_decode($get_token_json, true);
		return $get_token_array;
	}else{
		return null;	
	}
}

//ユーザー情報の取得だけ
function userGet($access_token){
	$info_url = "https://graph.facebook.com/v2.7/me?fields=name,first_name,last_name,link&access_token=${access_token}";
	if($json = file_get_contents($info_url)){
		$user_array = json_decode($json, true);
		return $user_array;
	}else{
		return null;	
	}
}

//ユーザー情報の登録
function userCreate($user_array){
	//DB登録処理
	return;
}

