<?php
class CImage{
/**
 * This is a PHP skript to process images using PHP GD.
 *
 */
	private $imgPath;
	private $cachePath;
	private $maxWidth;
	private $maxHeight;

	private $src;
	private $verbose;
	private $saveAs;
	private $quality;
	private $ignoreCache;
	private $newWidth;
	private $newHeight;
	private $cropToFit;
	private $sharpen;
	private $grayscale;
	private $pathToImage;
	private $fileExtension;
	private $image;
	private $filesize;
	private $cacheFileName;
	private $mime;
	private $width;
	private $height;
	private $cropWidth;
	private $cropHeight;


	/**
	* Constructor.
	*/
	public function __construct(){
		
		$this->imgPath = IMG_PATH;
		$this->cachePath = CACHE_PATH;
		$this->maxWidth = $this->maxHeight = 2000;
		
		//
		// Set the incoming arguments
		//
		$this->src        = isset($_GET['src'])     ? $_GET['src']      : null;
		$this->verbose    = isset($_GET['verbose']) ? true              : null;
		$this->saveAs     = isset($_GET['save-as']) ? $_GET['save-as']  : null;
		$this->quality    = isset($_GET['quality']) ? $_GET['quality']  : 60;
		$this->ignoreCache = isset($_GET['no-cache']) ? true           : null;
		$this->newWidth   = isset($_GET['width'])   ? $_GET['width']    : null;
		$this->newHeight  = isset($_GET['height'])  ? $_GET['height']   : null;
		$this->cropToFit  = isset($_GET['crop-to-fit']) ? true : null;
		$this->sharpen    = isset($_GET['sharpen']) ? true : null;
		$this->grayscale    = isset($_GET['grayscale']) ? true : null;
		$this->pathToImage = realpath($this->imgPath . $this->src);

		$this->displayImage();
	}
	
	/**
	* Process and display the image
	*/
	private function displayImage(){
		$this->validate();
		if($this->verbose){$this->displayLog();}
		$this->getInfoImage();
		$this->calcWidthHeight();
		$this->createNameCache();
		$this->isImageCached();
		$this->openImage();
		$this->reziseImage();
		$this->applyFilter();
		$this->saveImage();
		$this->outputImage($this->cacheFileName);
	}
	
	/**
	* Validate incoming arguments
	*/
	private function validate(){
		is_dir($this->imgPath) or $this->errorMessage('The image dir is not a valid directory.');
		is_writable($this->cachePath) or $this->errorMessage('The cache dir is not a writable directory.');
		isset($this->src) or $this->errorMessage('Must set src-attribute.');
		preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $this->src) or $this->errorMessage('Filename contains invalid characters.');
		substr_compare($this->imgPath, $this->pathToImage, 0, strlen($this->imgPath)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
		is_null($this->saveAs) or in_array($this->saveAs, array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension to save image as');
		is_null($this->quality) or (is_numeric($this->quality) and $this->quality > 0 and $this->quality <= 100) or $this->errorMessage('Quality out of range');
		is_null($this->newWidth) or (is_numeric($this->newWidth) and $this->newWidth > 0 and $this->newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
		is_null($this->newHeight) or (is_numeric($this->newHeight) and $this->newHeight > 0 and $this->newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
		is_null($this->cropToFit) or ($this->cropToFit and $this->newWidth and $this->newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
	}
	
	/**
	* Start displaying log if verbose mode & create url to current image
	*/
	private function displayLog(){
            $query = array();
            parse_str($_SERVER['QUERY_STRING'], $query);
            unset($query['verbose']);
            $url = '?'.http_build_query($query);

            echo "
            <html lang='en'>
            <meta charset='UTF-8'/>
            <title>img.php/Verbose mode</title>
            <h1>Verbose Mode</h1>
			<p><a href={$url}><code>{$url}</code></a><br>
            <img src='{$url}'/></p>";
    } 

	/**
	* Get information on the image
	*/
	private function getInfoImage(){
		$imgInfo = list($this->width, $this->height, $type, $attr) = getimagesize($this->pathToImage);
		!empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
		$this->mime = $imgInfo['mime'];

		if($this->verbose) {
			$this->filesize = filesize($this->pathToImage);
			$this->verBose("Image file: {$this->pathToImage}");
			$this->verBose("Image information: " . print_r($imgInfo, true));
			$this->verBose("Image width x height (type): {$this->width} x {$this->height} ({$type}).");
			$this->verBose("Image file size: {$this->filesize} bytes.");
			$this->verBose("Image mime type: {$this->mime}.");
		}
	}

	/**
	* Calculate new width and height for the image
	*/
	private function calcWidthHeight(){
		$aspectRatio = $this->width / $this->height;

		if($this->cropToFit && $this->newWidth && $this->newHeight) {
			$targetRatio = $this->newWidth / $this->newHeight;
			$this->cropWidth   = $targetRatio > $aspectRatio ? $this->width : round($this->height * $targetRatio);
			$this->cropHeight  = $targetRatio > $aspectRatio ? round($this->width  / $targetRatio) : $this->height;
			if($this->verbose) { $this->verBose("Crop to fit into box of {$this->newWidth}x{$this->newHeight}. Cropping dimensions: {$this->cropWidth}x{$this->cropHeight}."); }
		}
		else if($this->newWidth && !$this->newHeight) {
			$this->newHeight = round($this->newWidth / $aspectRatio);
			if($this->verbose) { $this->verBose("New width is known {$this->newWidth}, height is calculated to {$this->newHeight}."); }
		}
		else if(!$this->newWidth && $this->newHeight) {
			$this->newWidth = round($this->newHeight * $aspectRatio);
			if($this->verbose) { $this->verBose("New height is known {$this->newHeight}, width is calculated to {$this->newWidth}."); }
		}
		else if($this->newWidth && $this->newHeight) {
			$ratioWidth  = $this->width  / $this->newWidth;
			$ratioHeight = $this->height / $this->newHeight;
			$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
			$this->newWidth  = round($this->width  / $ratio);
			$this->newHeight = round($this->height / $ratio);
			if($this->verbose) { $this->verBose("New width & height is requested, keeping aspect ratio results in {$this->newWidth}x{$this->newHeight}."); }
		}
		else {
			$this->newWidth = $this->width;
			$this->newHeight = $this->height;
			if($this->verbose) { $this->verBose("Keeping original width & heigth."); }
		}

	}
	
	/**
	* Creating a filename for the cache
	*/
	private function createNameCache(){
		$parts          = pathinfo($this->pathToImage);
		$this->fileExtension  = $parts['extension'];
		$this->saveAs         = is_null($this->saveAs) ? $this->fileExtension : $this->saveAs;
		$quality_       = is_null($this->quality) ? null : "_q{$this->quality}";
		$cropToFit_     = is_null($this->cropToFit) ? null : "_cf";
		$sharpen_       = is_null($this->sharpen) ? null : "_s";
		$grayscale_     = is_null($this->grayscale) ? null : "_g";
		$this->dirName        = preg_replace('/\//', '-', dirname($this->src));
		$this->cacheFileName = $this->cachePath . "-{$this->dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}.{$grayscale_}.{$this->saveAs}";
		$this->cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $this->cacheFileName);

		if($this->verbose) { $this->verBose("Cache file is: {$this->cacheFileName}"); }
	}
	
	/**
	* Is there already a valid image in the cache directory, then use it and exit
	*/
	private function isImageCached(){
		$imageModifiedTime = filemtime($this->pathToImage);
		$cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;

		// If cached image is valid, output it.
		if(!$this->ignoreCache && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
			if($this->verbose) { $this->verBose("Cache file is valid, output it."); }
			$this->outputImage($this->cacheFileName);
		}

		if($this->verbose) { $this->verBose("Cache is not valid, process image and create a cached version of it."); }
	}	
	
	/**
	* Open up the original image from file
	*/
	private function openImage(){
		if($this->verbose) { $this->verBose("File extension is: {$this->fileExtension}"); }

		switch($this->fileExtension) {  
			case 'jpg':
			case 'jpeg':
				$this->image = imagecreatefromjpeg($this->pathToImage);
				if($this->verbose) { $this->$verBose("Opened the image as a JPEG image."); }
				break;  
  
			case 'png':  
				$this->image = imagecreatefrompng($this->pathToImage); 
				if($this->verbose) { $this->verbose("Opened the image as a PNG image."); }
				break;  

			default: $this->errorMessage('No support for this file extension.');
		}
	}	
	
	/**
	* Resize the image if needed
	*/
	private function reziseImage(){
		if($this->cropToFit) {
			if($this->verbose) { $this->verBose("Resizing, crop to fit."); }
			$cropX = round(($this->width - $this->cropWidth) / 2);  
			$cropY = round(($this->height - $this->cropHeight) / 2);    
			$imageResized = imagecreatetruecolor($this->newWidth, $this->newHeight);
			imagecopyresampled($imageResized, $this->image, 0, 0, $cropX, $cropY, $this->newWidth, $this->newHeight, $this->cropWidth, $this->cropHeight);
			$this->image = $imageResized;
			$this->width = $this->newWidth;
			$this->height = $this->newHeight;
		}
		else if(!($this->newWidth == $this->width && $this->newHeight == $this->height)) {
			if($this->verbose) { $this->verBose("Resizing, new height and/or width."); }
			$imageResized = imagecreatetruecolor($this->newWidth, $this->newHeight);
			imagecopyresampled($imageResized, $this->image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
			$this->image  = $imageResized;
			$this->width  = $this->newWidth;
			$this->height = $this->newHeight;
		}
	}	
	
	/**
	* Apply filters and postprocessing of image
	*/
	private function applyFilter(){
		if($this->sharpen) {
			$this->image = $this->sharpenImage($this->image);
		}
		if($this->grayscale) {
			$this->image = $this->grayscaleImage($this->image);
		}
	}	
	
	/**
	* Save the image
	*/
	private function saveImage(){
		switch($this->saveAs) {
			case 'jpeg':
			case 'jpg':
				if($this->verbose) { $this->verBose("Saving image as JPEG to cache using quality = {$this->quality}."); }
				imagejpeg($this->image, $this->cacheFileName, $this->quality);
				break;  

			case 'png':  
				if($this->verbose) { $this->verBose("Saving image as PNG to cache."); }
				imagepng($this->image, $this->cacheFileName);  
				break;  

			default: $this->errorMessage('No support to save as this file extension.');
			break;
		}

		if($this->verbose) { 
			clearstatcache();
			$cacheFilesize = filesize($this->cacheFileName);
			$this->verBose("File size of cached file: {$cacheFilesize} bytes."); 
			$this->verBose("Cache file has a file size of " . round($cacheFilesize/$this->filesize*100) . "% of the original size.");
		}
	}	
	
	/**
	* Output an image together with last modified header.
	*
	* @param string $file as path to the image..
	*/
	private function outputImage($file) {
		$info = getimagesize($file);
		!empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
		$this->mime   = $info['mime'];

		$lastModified = filemtime($file);  
		$gmdate = gmdate("D, d M Y H:i:s", $lastModified);

		if($this->verbose) {
			$this->verBose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
			$this->verBose("Memory limit: " . ini_get('memory_limit'));
			$this->verBose("Time is {$gmdate} GMT.");
		}

		if(!$this->verbose) header('Last-Modified: ' . $gmdate . ' GMT');
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
			if($verbose) { $this->verBose("Would send header 304 Not Modified, but its verbose mode."); exit; }
			header('HTTP/1.0 304 Not Modified');
		} 
		else {  
			if($this->verbose) { $this->verBose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
			header('Content-type: ' . $this->mime);  
			readfile($file);
		}
		exit;
	}
	
	/**
	* Sharpen image as http://php.net/manual/en/ref.image.php#56144
	* http://loriweb.pair.com/8udf-sharpen.html
		*
	* @param resource $image the image to apply this filter on.
	* @return resource $image as the processed image.
	*/
	private function sharpenImage($image) {
		$matrix = array(
		array(-1,-1,-1,),
		array(-1,16,-1,),
		array(-1,-1,-1,)
		);
		$divisor = 8;
		$offset = 0;
		imageconvolution($image, $matrix, $divisor, $offset);
		return $image;
	}
	
	/**
	* grayscale image as http://php.net/manual/en/function.imagefilter.php
	* 
	*
	* @param resource $image the image to apply this filter on.
	* @return resource $image as the processed image.
	*/
	private function grayscaleImage($image) {
		imagefilter($image, IMG_FILTER_GRAYSCALE);
		return $image;
	}
	
	/**
	* Display error message.
	*
	* @param string $message the error message to display.
	*/
	private function errorMessage($message) {
		header("Status: 404 Not Found");
		die('img.php says 404 - ' . htmlentities($message));
	}
	
	/**
	* Display log message.
	*
	* @param string $message the log message to display.
	*/
	private function verBose($message) {
		echo "<p>" . htmlentities($message) . "</p>";
	}
	
}
