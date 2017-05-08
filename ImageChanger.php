<?php
/* 
 * 動作試していない
 * ヒントにはなるかも
 * 画像のexifを解析して回転を元に戻す 
 * */

class ImageFormat{
	const FILE_MAX_LENGTH = 750;

	//イメージを縮小、回転させてjpegに変換
	public function imageCreate($pre_file){
		if(isset($pre_file) == false) return null;
		if(file_exists($pre_file) == false) return null;

		//元画像のサイズ.タイプ取得
		$infos = getimagesize($this->pre_file);
		$w = $infos[0];
		$h = $infos[1];
		$type = $infos['mime'];
		$size = $this->imageSize($w, $h);

		//型作り
		$new_image = imagecreatetruecolor($size['width'], $size['height']);

		switch($type){
			case 'image/gif':
				$base_image = imagecreatefromgif($pre_file);
				break;
			case 'image/png':
				$base_image = imagecreatefrompng($pre_file);
				break;				
			case 'image/jpeg':
			case 'image/jpg':
				$base_image = imagecreatefromjpeg($pre_file);
				break;
			default:
				return null;
				
		}
		//型に合わせて元の画像を縮小してコピペ
		imagecopyresampled($new_image, $base_image, 0, 0, 0, 0, $size['width'], $size['height'],  $w, $h);
		
		//メモリ解放
		ImageDestroy($base_image);
		
		//tmpファイルを消して新しい画像のパスを作る
		if(file_exists($pre_file)) unlink($pre_file);
		$microtime = microtime(true);
		$data = str_replace('.', '', $microtime);
		$file_path = 'tmp/'. $data. '.jpeg';

		//加工済み画像、ファイル名(path)、圧縮率60
		imagejpeg($new_image, $file_path, 60);

		//メモリ解放
		ImageDestroy($new_image);

		return $file_path;
	}

	//画像の向きを元に戻す
	public function imageRotate($pre_file){
		if(isset($pre_file) == false) return null;
		if(file_exists($pre_file) == false) return null;

		//元画像のサイズ.タイプ取得
		$infos = getimagesize($this->pre_file);
		$w = $infos[0];
		$h = $infos[1];
		$type = $infos['mime'];
		$size = $this->imageSize($w, $h);
		if($type != "image/jpeg" && $type != "image/jpg"){
			return null;
		}
		//型作り
		$new_image = imagecreatetruecolor($size['width'], $size['height']);

		$exif = $this->exifInformation($pre_file);
		if($exif == null) return null;

		$base_image = imagecreatefromjpeg($pre_file);
		if(isset($exif['IFD0']['Orientation'])){
			//回転処理
			$rotate_infos = $this->imageRotation($exif['IFD0']['Orientation']);
			//反転
			if(!empty($rotate_infos['mode'])){
				$base_image = imageflip($base_image, $rotate_infos['mode']);
			}

			//回転
			if($rotate_infos['degrees'] > 0){
				$base_image = imagerotate($base_image, $rotate_infos['degrees'], 0);
			}
		}

		//tmpファイルを消して新しい画像のパスを作る
		if(file_exists($pre_file)) unlink($pre_file);
		$microtime = microtime(true);
		$data = str_replace('.', '', $microtime);
		$file_path = 'tmp/'. $data. '.jpeg';

		//加工済み画像、ファイル名(path)、圧縮率60
		imagejpeg($base_image, $file_path);
		
		//メモリ解放 一旦いらない
		ImageDestroy($base_image);
		
		return $file_path;

	}

	//exif情報取得
	public function exifInformation($filepath){
		if(isset($filepath) == false) return null;
		if(file_exists($filepath) == false) return null;
		$exif = exif_read_data($file, 0, true);
		if($exif != false){
			return null;
		}else{
			return $exif;
		}
	}

	//同じ縦横比のまま長いほうが指定した数値になるように縦横を返す
	private function imageSize($w, $h){
		if($w < self::FILE_MAX_LENGTH || $h < self::FILE_MAX_LENGTH){
			$width = $w;
			$height = $h;
		}elseif($w > $h){
			$ratio = $w / $h;
			$height = self::FILE_MAX_LENGTH;
			$width = round($height * $ratio);
		}elseif($w < $h){
			$ratio = $h / $w;
			$width = self::FILE_MAX_LENGTH;
			$height = round($width * $ratio);
		}else{
			$width = self::FILE_MAX_LENGTH;
			$height = self::FILE_MAX_LENGTH;
		}
		$size = ['width' => $width, 'height' => $height];
		return $size;
	}

	//イメージの回転修正
	//ファイルのパスをもらって回転の処理に必要な$mode, $degreesを返す
	private function imageRotation($exif){
		if(isset($exif) == false){
			JsonMaker::write_json(0, ['message' => 'I need exif']);
			return false;
		}
		$degrees = 0;
		$mode = '';

		switch($exif){
			case 1:
				break;
			case 2:
				$mode = 'IMG_FLIP_HORIZONTAL';
				break;
			case 3:
				$degrees = 180;
				break;
			case 4:
				$mode = 'IMG_FLIP_VERTICAL';
				break;
			case 5:
				$degrees = 90;
				$mode = 'IMG_FLIP_HORIZONTAL';
				break;
			case 6:
				//$degrees = 90;
				$degrees = 270;
				break;
			case 7:
				$degrees = 90;
				$mode = 'IMG_FLIP_VERTICAL';
				break;
			case 8:
				$degrees = 270;
				break;
		}
	
		$array = ['mode' => $mode, 'degrees' => $degrees];	
		return $array;
	}
}

