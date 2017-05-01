<?php
/*
 * facebookのソーシャルログイン実装時のクラス
 * 動作確認していない
 * */

class Facebook{
	const APP_ID = "";
	const APP_SECRET = "";
	const STATE = "";
	const CALLBACK_URL = "";

	//facebookのログイン画面のリダイレクトURL取得
	public function redirectURL(){
		$callback_url = urlencode(self::CALLBACK_URL);
		$facebook_redirct_url =  "https://www.facebook.com/dialog/oauth?client_id=". self::APP_ID. "&redirect_uri=". $callback_url. "&state=". self::STATE. "&scope=". $scope;
		return $facebool_url;
	}

	//フェイスブックとの認証やユーザーの登録
	public function oauth($params){
		if($this->isCodeCorrect($params) == false) return false;
		if($this->isStateCorrect($params) == false) return false;
		
		$token_array = $this->accessToken($code);
		if($token_array == null) return false;

		$access_token = $token_array['access_token'];
		$user_array = $this->userGet($access_token);
		if($user_array == null) return;

		$result = $this->userCreate($user_array);
		return $result;
	}

	//codeが正しくきているのか？
	private function isCodeCorrect($params){
		//codeのついて確認
		if(isset($params['code'])){
			return true;
		}else if(isset($params['error_reason'])){
			echo $params['error_reason'];
			return false;
		}else{
			echo "Unexpected error";
			return false;
		}

	}

	//facebookに渡したstateが正しいかチェック
	private function isStateCorrect($params){
		if(isset($params['state'])){
			$state = str_replace('#_=_', '', $params['state']);
			if($state == self::STATE){
				return true;
			}else{
				echo "Unexpected error";
				return false;
			}
		}else{
			return false;
		}	
	}

	//アクセストークンの取得
	private function accessToken($code){
		$get_token_url = "https://graph.facebook.com/v2.7/oauth/access_token?client_id=". self::APP_ID. "&redirect_uri=". self::CALLBACK_URL. "&client_secret=". self::APP_SECRET. "&code=${code}";
		if($get_token_json = file_get_contents($get_token_url)){
			$get_token_array = json_decode($get_token_json, true);
			return $get_token_array;
		}else{
			return null;	
		}
	}

	//ユーザー情報の取得だけ
	private function userGet($access_token){
		$info_url = "https://graph.facebook.com/v2.7/me?fields=name,first_name,last_name,link&access_token=${access_token}";
		if($json = file_get_contents($info_url)){
			$user_array = json_decode($json, true);
			return $user_array;
		}else{
			return null;	
		}
	}

	//ユーザー情報の登録
	private function userCreate($user_array){
		//DB登録処理
		return true; //or false
	}



}
