<?php
/*android レシート検証 メモ*/


//レシート，公開鍵の受け取り
define('PUBLIC_KEY_FILE', '');
define('USER_IDENTIFIER', ''); //developer payload

$receipt = $_POST['receipt'];
$receipt = base64_decode($receipt);

$signature = $_POST['signature'];
$signature = base64_decode($signature);

$public_key = file_get_contents(PUBLIC_KEY_FILE);
$public_key_id = openssl_get_publickey($public_key);

//レシートに関してその著名が正しいかの確認
$result = (int)openssl_verify($receipt, $signature, $public_key_id);

//メモリ解放
openssl_free_key($public_key_id);

if($result === 1){
	//echo '著名が正しいです';
}else if($result === 0){
	//echo '著名が正しくありません';
	jsonMaker(['status' => 'error', 'message' => 'Uncorrect signature']);
	exit();
}else if($result === -1){
	//echo '著名の検証でエラーが発生しました';
	jsonMaker(['status' => 'error', 'message' => 'Unexpected error']);
	exit();
}

//レシートと一緒に飛ばしてもらう識別子の判定
$obj = json_decode($receipt);
if ($obj->developerPayload !== USER_IDENTIFIER) {
	//echo 'Developer Payloadが正しくありません';
	jsonMaker(['status' => 'error', 'message' => 'Developer Payload is uncorrect']);
	exit();
}else{
	//ここに来れば成功
	jsonMaker(['status' => 'success', 'message' => '']);
	exit();
}

function jsonMaker($array){
	$json = json_encode($array);
	header("Content-Type: text/javascript; charset=utf-8");
	echo $json;
	return;
}
