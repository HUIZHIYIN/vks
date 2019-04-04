<?php

define("CACHE_DIR","tmp/");

class MyCache
{
	/* gets the contents of a file if it exists, otherwise grabs and caches */
	public function get_content($url, $hours = 1, $fn = '',$fn_args = '') {
        
		$file = CACHE_DIR . md5($url);

		//vars
        $current_time = time(); 
		$expire_time = $hours * 60 * 60; 
		
		//get cache file created time
		$file_time = file_exists($file) ?  filemtime($file) : 0;

		if(($current_time - $expire_time) < $file_time) {

			//echo 'returning from cached file';
			return file_get_contents($file);
		}
		else {
			$content = $this->get_url($url);
			// $fn is some function to format data
			if($fn) { $content = $fn($content, $fn_args); }
			$content.= '<!-- cached:  '.time().'-->';

			file_put_contents($file, $content);
			// echo 'retrieved fresh from '.$url.':: '.$content;
			return $content;
		}
	}

	/* gets content from a URL via curl */
	private function get_url($url) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
}


/* usage */

$cache = new MyCache;

$api_url = 'http://preesee.cn/wp-json/wp/V2/POSTS?per_page=2&amp;categories=5';

$api_content = $cache->get_content($api_url, 0.1 , 'format_data');



/* callback function */
function format_data($content, $args) {
	$content = json_decode($content);
	return json_encode($content[0]->content->rendered);

}

echo  json_encode(array($api_content)) ;
	

?>
