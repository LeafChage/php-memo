<?php
class ImageChnager{
      const FILE_MAX_LENGTH = 750; //画像の縮小時の長い方の長さ
      const COMPRESSION = 60;

      private $target_file;
      private $width;
      private $height;
      private $type;

      function __construct($_image = ""){
            $this->image = $image;
      }

      //画像の縮小
      public function image_create(){ //->bool
            if(!$this->file_exists()) return false;
            $this->get_image_information();
            $size = $this->imageSize(self::FILE_MAX_LENGTH);

            //型作り
            $new_image = imagecreatetruecolor($size['width'], $size['height']);

            switch($type){
            case 'image/gif':
                  $base_image = imagecreatefromgif($this->target_file);
                  break;
            case 'image/png':
                  $base_image = imagecreatefrompng($this->target_file);
                  break;
            case 'image/jpeg':
            case 'image/jpg':
                  $base_image = imagecreatefromjpeg($this->target_file);
                  break;
            default:
                  return false;

            }
            //型に合わせて作成
            imagecopyresampled($new_image, $base_image, 0, 0, 0, 0, $size['width'], $size['height'],  $w, $h);
            ImageDestroy($base_image);

            //元データの削除
            if(file_exists($this->image)){
                  $this->image = "";
                  unlink($this->image);
            }
            $file_path = $this->get_new_image_path();

            imagejpeg($new_image, $file_path, self::COMPRESSION);
            ImageDestroy($new_image);

            $this->image = $file_path;
            return true;
      }


      //画像の向きを元に戻す
      public function image_rotate(){ //->bool
            if(!$this->file_exists()) return false;
            $this->get_image_information();

            if($this->type != "image/jpeg" && $this->type != "image/jpg"){
                  return false;
            }
            //型作り
            $new_image = imagecreatetruecolor($this->width, $this->height);

            $exif = $this->exifInformation($pre_file);
            if($exif == null) return;

            $base_image = imagecreatefromjpeg($this->image);
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

            //元データの削除
            if(file_exists($this->image)){
                  $this->image = "";
                  unlink($this->image);
            }
            $file_path = $this->get_new_image_path();

            imagejpeg($base_image, $file_path, self::COMPRESSION);
            ImageDestroy($base_image);

            $this->image = $file_path;
            return true;
      }

      //exif情報取得
      public function exif_information($filepath){
            if(!$this->file_exists()) return false;
            $exif = exif_read_data($file, 0, true);
            if($exif != false){
                  return null;
            }else{
                  return $exif;
            }
      }

      //同じ縦横比のまま長いほうが指定した数値になるように縦横を返す
      private function image_size($max_length){ // -> [int, int]
            if($this->width < $max_length || $this->height < $max_length){
                  $w = $this->width;
                  $h = $this->height;
            }elseif($this->width > $this->height){
                  $ratio = $this->width / $this->height;
                  $h = $max_length;
                  $w = round($height * $ratio);
            }elseif($w < $h){
                  $ratio = $this->height / $this->width;
                  $w = $max_length;
                  $h = round($w * $ratio);
            }else{
                  $w = $max_length;
                  $h = $max_length;
            }
            $size = ['width' => $w, 'height' => $h];
            return $size;
      }

      //イメージの回転修正
      //ファイルのパスをもらって回転の処理に必要な$mode, $degreesを返す
      private function image_rotation($exif){ //-> [string, int]
            if(isset($exif) == false){
                  return ["", 0];
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

            return ['mode' => $mode, 'degrees' => $degrees];
      }
      //fileが存在しているか?
      private function file_exists(){ //-> bool
            $result = true;
            if(!isset($this->target_file)){
                  $result = false;
            } else if(!file_exists($pre_file)){
                  $result = false;
            }
            return $result;
      }

      private function get_new_image_path(){ // -> string
            $name = str_replace('.', '', microtime(true));
            $filepath = 'tmp/'. $name. '.jpeg';
            return $file_path;
      }
      //file 情報取得
      private function get_image_information(){ //->void
            $infos = getimagesize($this->target_file);
            $this->width = $infos[0];
            $this->height = $infos[1];
            $this->type = $infos['mime'];
      }
}

