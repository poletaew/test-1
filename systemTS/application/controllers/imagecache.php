<?php

/**
 * @desc тестовый класс для работы с изображениями по заданным в README.md условиям
 * 
 * @author Michael Poletaew <poletaew@gmail.com>
 */

class ImageCache extends Controller {
	
	//т.к. задание выполнялось под windows, пути к файлам не те, что были затребованы
	//однако константы можно изменить под свои нужды в любое время
	const IMAGE_PATH = 'V:\\home\\test\\www\\test-1\\images\\original\\';
	const IMAGE_CACHE = 'V:\\home\\test\\www\\test-1\\images\\cache\\';

	function __construct(){
		parent::Controller();
	}
	
	function index($new_width, $new_height, $name){
		
		$cache_name = "{$new_width}-{$new_height}-{$name}";
		
		if(!self::get_cache($cache_name)){
			
			if(!is_file(self::IMAGE_PATH.$name))
				die('Undefined image');
		
			$img = new Imagick(self::IMAGE_PATH.$name);

			$width = $img->getImageWidth();
			$height = $img->getimageheight();

			$ratio = $width/$height;
			$new_ratio = $new_width/$new_height;

			//изменяем размер по большей стороне
			if($ratio>$new_ratio){
				$img->adaptiveResizeImage(0,$new_height);
				$y = 0;
				$x = ($new_height-$new_width)/2;
			}else{
				$img->adaptiveResizeImage($new_width,0);
				$y = ($new_width-$new_height)/2;
				$x = 0;
			}

			//также делаем обрезку, если соотношения сторон не совпадают
			//X и Y нужны для центрирования при обрезке
			if($ratio!=$new_ratio){
				$img->cropImage($new_width,$new_height,$x,$y);
			}

			//если изображение было увеличено, применяем нормализацию цвета и фильтр нерезкого маскирования
			if($width<$new_width || $height<$new_height){
				$img->normalizeImage();
				$img->unsharpMaskImage(0, 0.5, 2, 0.05);
			}

			$img->writeimage(self::IMAGE_CACHE.$cache_name);

			header("Content-type: image/".$img->getimageformat());
			echo $img;
			die;
		}
	}
	
	private static function get_cache($cache_name){
		$path = self::IMAGE_CACHE.$cache_name;
		
		if(is_file($path)) {
			$info = getimagesize($path);
			if(!$info) return false;
			
			header('Content-type: '.$info['mime']); //браузеру не важен формат,
			readfile($path);
			return true;
		} 
		else return false;
	}
}