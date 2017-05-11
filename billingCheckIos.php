<?php
define('PRODUCT', 'item_name');

// レシート受け取り
$receipt  = $_POST['receipt'];
$post_data = json_encode(array('receipt-data' => $receipt));

// 本番環境
$response = post("https://buy.itunes.apple.com/verifyReceipt", $post_data);
if($response === NULL || $response == FALSE){
	errorCheck(1);
	exit();
}

// 本番環境に問い合わせを行ったが，テスト環境用というstatusが帰ってきた場合、テスト環境に問い合わせなおす。
if($response['status'] == 21007){
	$response = post("https://sandbox.itunes.apple.com/verifyReceipt", $postData);

	if($response === NULL || $response == FALSE){
		errorCheck(1);
		exit();
	}
}

if(isset($response['receipt']['product_id'], $response['receipt']['transaction_id']) == false){
	errorCheck(1);
	exit();
}

if(errorCheck($response['status'])){
	// 商品のIDが正しくありません。
	if($response['receipt']['product_id'] == 'PRODUCT'){
		errorCheck(3);
		exit();
	}

	//すでに登録されているレシートかどうかの確認 DBをみて確認
	if($response['receipt']['transaction_id'] == 'transaction_id'){
		errorCheck(2);
		exit();
	}


	/*
	 * DBに登録
	if($transaction_id != $response['receipt']['transaction_id']){
		$transaction_id = $response['receipt']['transaction_id'];
	}*/
	jsonMaker(['status' => 'success', 'message' => '']);
	echo '検証成功です！';

}else{
	exit();
}


//appleと通信を行うための関数
function post($endpoint, $post_data){
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8'));
	$json = curl_exec($ch);
	$response = json_decode($json, true);

	curl_close($ch);
	return $response;
}

//配列からjsonを作って表示する
function jsonMaker($array){
	$json = json_encode($array);
	header("Content-Type: text/javascript; charset=utf-8");
	echo $json;
	return;
}

//20000台はappleのエラーそのまま
function errorCheck($status){
	switch($status){
		case 0:
			return true;
		case 1:
			$message = "I could not be verified";
			break;
		case 2:
			$message = "user is uncorrect";
			break;
		case 3:
			$message = "product id is uncorrect";
			break;
		case 21000:
			$message = "The App Store could not read the JSON object you provided.";
			break;
		case 21002:
			$message = "The data in the receipt-data property was malformed or missing.";
			break;
		case 21003:
			$message = "The receipt could not be authenticated.";
			break;
		case 21004:
			$message = "The shared secret you provided does not match the shared secret on file for your account. Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.";
			break;
		case 21005:
			$message = "The receipt server is not currently available.";
			break;
		case 21006:
			$message = "This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response. Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.";
			break;
		case 21007:
			$message = "This receipt is from the test environment, but it was sent to the production environment for verification. Send it to the test environment instead.";
			break;
		case 21008:
			$message = "This receipt is from the production environment, but it was sent to the test environment for verification. Send it to the production environment instead.";
			break;
		default:
			$message = "Unexpected error";
			break;
	}
	jsonMaker(['status' => 'error', 'message' => $message]);
	return false;
}
