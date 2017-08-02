<?php

/*php用のtwitterSDKを入れておいてね！*/
require('twitteroauth/autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter{
      const API_KEY = '';
      const API_SECRET = '';
      const CALLBACK_URL = "";

      //twitterログイン画面に移動するURL取得
      public function redirect(){
            $connection = new TwitterOAuth(self::API_KEY, self::API_SECRET);
            $request_token = $connection->oauth("oauth/request_token", array('oauth_callback' => self::CALLBACK_URL));
            $twitter_login_url = 'https://api.twitter.com/oauth/authenticate?oauth_token='. $request_token['oauth_token'];
            return $twitter_login_url;
      }


      public function oauth($params){
            if($this->isTokenCorrect($params) == false) return false;
            $oauth_verifier = $params['oauth_verifier'];
            $oauth_token = $params['oauth_token'];

            $access_token = $this->accessToken($oauth_token, $oauth_verifier);
            $user_info = $this->userGet($access_token['oauth_token'], $access_token['oauth_token_secret']);

            $this->userCreate($user_info);
      }

      //tokenが正しく送られてきているかのチェック
      private function isTokenCorrect($params){
            if(isset($params['oauth_token']) && isset($params['oauth_varifier'])){
                  return true;
            }else if(isset($params['denied'])){
                  //拒否の場合はdeniedを取得
                  echo $params['denied'];
                  return false;
            }else{
                  echo "UNexpected error";
                  return false;
            }
      }

      //twitterからアクセストークンの取得
      private function accessToken($oauth_token, $oauth_verifier){
            $connection = new TwitterOAuth(self::API_KEY, self::API_SECRET, $oauth_token, $oauth_verifier);
            $access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $oauth_verifier, 'oauth_token' => $oauth_token));
            return $access_token;
      }


      //ユーザー情報の取得
      private function userGet($oauth_token, $oauth_token_secret){
            $user_connection = new TwitterOAuth(self::API_KEY, self::API_SECRET, $oauth_token, $oauth_token_secret);
            $user_info = $user_connection->get('account/verify_credentials');
            return $user_info;
      }

      private function userCreate($user_info){
            //DBに入れる
      }
}
