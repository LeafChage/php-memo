<?php

// レシート受け取り
$receipt  = $_POST['#receipt#'];
$postData = json_encode(array('receipt-data' => $receipt));

// 本番環境
$response = post("https://buy.itunes.apple.com/verifyReceipt", $postData);

if($response === NULL or $response == FALSE){
	echo '正しく検証できませんでした。';
}

// 本番環境に問い合わせを行ったが，テスト環境用というstatusが帰ってきた場合、テスト環境に問い合わせなおす。
if($response->status == 21007){
	$response = post("https://sandbox.itunes.apple.com/verifyReceipt", $postData);

	if($response === NULL or $response == FALSE){
		echo '正しく検証できませんでした。';
	}
}

if(!isset($response->receipt->product_id, $response->receipt->transaction_id)){
	echo '正しく検証できませんでした';
}

if($response->status == 21005){
	echo 'アップルのレシート検証のサーバーがダウンしています';
}

if($response->status != 0){
	echo '著名の検証でエラーが発生しました';
}

//春日さんのソースだとここでtry{}catchのcatch

if($response->receipt->transaction_id == '###transaction_id###'){
	echo 'ユーザーが正しくありません';
}

if($response->receipt->product_id == '###product_id###'){
	echo '商品のIDが正しくありません。';
}

if($response->status == 0){
	// 決済毎に transaction_id がユニークになるのでこれを控え、
	// 同じレシートで複数回リクエストがあったら無視するように。
	/*
	if($transaction_id != $response->receipt->transaction_id){
		$transaction_id = $response->receipt->transaction_id;
	}*/
	echo '検証成功です！'; 
}

//appleと通信を行うための関数
function post($endpoint_url, $postData){
	$ch = curl_init($endpoint_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8')); // HTTPヘッダー

	$json = curl_exec($ch); //実行 
	$response = json_decode($json);

	curl_close($ch);
	return $response;
}

?>
