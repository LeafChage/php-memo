<?php
/*
 * 動作試していない
 * S3に画像をアップロードする方法 aws.pharはダウンロードする
 * */
require_once('aws.phar');
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Excepton;
use Guzzle\Http\EntityBody;

class S3{
	const KEY = '';
	const SECRET_KEY = '';
	const REGION = '';
	const BUCKET_NAME = '';
	private $local_file = ""; //S3にアップロードするローカルのファイルの名前

	function __construct($file_path){
		$this->local_file = $file_path;
	}

	//アップロード後のS3でのファイル名(new_file)
	public function uploadS3($new_file){
		if(isset($this->local_file) == false) return null;
		if(file_exists($this->local_file) == false) return null;
		if(isset($new_file) == false) return null;

		$mime_type = mime_content_type($this->local_file);
		$s3_object = $this->getS3ClientInstance();
		$upload_information = [
			'Bucket' => self::BUCKET_NAME,
			'Key' => $new_file,
			'SourceFile' => $this->local_file,
			'ContentType' => $mime_type,
			'ACL' => 'public-read',
		];
		$result = $s3_object->putObject($upload_information);
		if(isset($result['ObjectURL']) == false) return null;

		//不要になった画像の削除
		if(file_exists($this->local_file)) unlink($this->local_file);
		return $result['ObjectURL'];
	}

	//S3クライアントのインスタンス作成
	private function getS3ClientInstance(){
		$s3_setting = [
			'credentials' => ['key' => self::KEY, 'secret' => self::SECRET_KEY,],
			'region' => self::REGION,
			'version' => 'latest',
		];
		$s3_object = S3Client::factory($s3_setting);
		return $s3_object;
	}
}
