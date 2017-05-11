<?php
/*android レシート検証*/
define('PUBLIC_KEY_FILE', ''); //公開鍵の置き場所
define('USER_IDENTIFIER', ''); //developer payload

if(isset($_POST['receipt']) == true){
	$receipt = $_POST['receipt'];
}else{
	jsonMaker(['status' => 'error', 'message' => 'you have to send receipt']);
	exit();
}
//$receipt = base64_decode($receipt);

/*signatureは元々base64でエンコードされている*/
if(isset($_POST['signature']) == true){
	$signature = base64_decode($_POST['signature']);
}else{
	jsonMaker(['status' => 'error', 'message' => 'you have to send signature']);
	exit();
}


if(file_exists(PUBLIC_KEY_FILE) == true){
	$public_key = file_get_contents(PUBLIC_KEY_FILE);
	$public_key_id = openssl_get_publickey($public_key);
}else{
	jsonMaker(['status' => 'error', 'message' => 'I can not find key file']);
	exit();
}

//レシートに関してその著名が正しいかの確認
$result = (int)openssl_verify($receipt, $signature, $public_key_id);

//メモリ解放
openssl_free_key($public_key_id);

if($result === 1){
	//レシートと一緒に飛ばしてもらう識別子の判定
	$obj = json_decode($receipt, true);
	if ($obj['developerPayload'] !== USER_IDENTIFIER) {
		//echo 'Developer Payloadが正しくありません';
		jsonMaker(['status' => 'error', 'message' => 'Developer Payload is uncorrect']);
		exit();
	}else{
		//ここに来れば成功
		//DBにログを残す
		jsonMaker(['status' => 'success', 'message' => '']);
		exit();
	}
}else if($result === 0){
	//echo '著名が正しくありません';
	jsonMaker(['status' => 'error', 'message' => 'Uncorrect signature']);
	exit();
}else if($result === -1){
	//echo '著名の検証でエラーが発生しました';
	jsonMaker(['status' => 'error', 'message' => 'Unexpected error']);
	exit();
}

//配列からjson作って表示する
function jsonMaker($array){
	$json = json_encode($array);
	header("Content-Type: text/javascript; charset=utf-8");
	echo $json;
	return;
}

//base64デコードするための整形 + デコード
function signDecoder($pre_signature){
	//64文字づつ改行しなきゃいけないらしい
	$pre_signature = str_replace('  ', '+', $pre_signature);
	$pre_signature = chunk_split($pre_signature, 64, "\n");
	$text = base64_decode($pre_signature);
	return $text;
}
