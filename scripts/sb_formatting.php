<?php
// The Simple PHP Blog is released under the GNU Public License.
//
// You are free to use and modify the Simple PHP Blog. All changes
// must be uploaded to SourceForge.net under Simple PHP Blog or
// emailed to apalmo <at> bigevilbrain <dot> com
// --------------------
// Entry Format Parsing
// --------------------
function clean_post_text($str)
{

	// Cleans post text input.
	//
	// Strip out and replace pipes with colons. HTML-ize entities.
	// Use charset from the language file to make sure we're only
	// encoding stuff that needs to be encoded.
	//
	// This makes entries safe for saving to a file (since the data
	// format is pipe delimited.)
	global $lang_string;
	$str = str_replace('|','&#124;',$str);
	$str = @htmlspecialchars($str,ENT_QUOTES,$GLOBALS['lang_string']['php_charset']);
	return($str);
}

function htmlDecode($temp_str)
{
	$trans_str = get_html_translation_table(HTML_ENTITIES);
	foreach($trans_str as $k=>$v){
		$ttr[$v] = utf8_encode($k);
	}
	$temp_str = strtr($temp_str,$ttr);
	$temp_str = str_replace('&#039;','\'',$temp_str);
	return($temp_str);
}

function blog_to_html($str,$comment_mode,$strip_all_tags,$add_no_follow=false,$emoticon_replace=false)
{
	if($comment_mode){
		return($str);
	}

	$closure = function($m){
		static $plugins = array();

		if(!count($plugins)){
			if($dh = opendir(ROOT_DIR.'scripts/plugins/entry/')){
				while($file = readdir($dh)){
					if(is_file(ROOT_DIR.'scripts/plugins/entry/'.$file) && preg_match('/\.php$/isS',$file)){
						require_once(ROOT_DIR.'scripts/plugins/entry/'.$file);
						$plugin = preg_replace('/\.php$/isS','',$file);
						$plugins[$plugin] = new $plugin;
					}
				}
				uasort($plugins,function($a,$b){return($a->priority > $b->priority);});
			}
		}

		foreach($plugins as $name =>$plugin){
			$m[2] = $plugin->main($m[2]);
		}
		return(implode(array_slice($m,1)));
	};

	$str = preg_replace_callback('/(^|\[\/(?:HTML)\])(.*?)($|\[(?:HTML)\])/isS',$closure,$str);
	$str = replace_url_tag($str,'[url=',']','[/url]',$strip_all_tags ? true : false,$add_no_follow);
	$str = replace_url_tag($str,'[URL=',']','[/URL]',$strip_all_tags ? true : false,$add_no_follow);
	$str = replace_img_tag($str,'[img=',']',$strip_all_tags ? true : false);
	$str = replace_img_tag($str,'[IMG=',']',$strip_all_tags ? true : false);
	$str = replace_html_tag($str,$strip_all_tags ? true : false);

	return($str);
}

function replace_html_tag($str,$strip_tags)
{

	// Replacements for HTML tags. Sub-function of blog_to_html.
	//
	// This function decodes HTML entities that are located between
	// HTML tags. Also, inserts <br />'s for new lines only if blocks
	// are outside the HTML tags.
	global $lang_string;
	$str_out = NULL;
	$tag_begin = '[html]';
	$tag_end = '[/html]';

	// Search for the openning HTML tag. Tag could be either upper or
	// lower case so we want to find the nearest one.
	//
	// Get initial $str_offset value.
	$temp_lower = strpos($str,strtolower($tag_begin));
	$temp_upper = strpos($str,strtoupper($tag_begin));
	if($temp_lower === false){
		if($temp_upper === false){
			$str_offset = false;
		}else{
			$str_offset = $temp_upper;
		}
	}else{
		if($temp_upper === false){
			$str_offset = $temp_lower;
		}else{
			$str_offset = min($temp_upper,$temp_lower);
		}
	}

	// Loop
	while($str_offset !== false){

		// Store all the text BEFORE the openning HTML tag.
		$temp_str = substr($str,0,$str_offset);

		//
		// Replace hard returns in string with '<br />' tags.
		// "\r\n" - WINDOWS
		// "\n"		- UNIX
		// "\r"		- MACINTOSH
		$temp_str = str_replace("\r\n",'<br />',$temp_str);
		$temp_str = str_replace("\n",'<br />',$temp_str);
		$temp_str = str_replace("\r",'<br />',$temp_str);

		// $temp_str = str_replace( chr(10), '<br />', $temp_str );
		$str_out .= $temp_str;

		// Store all text AFTER the tag
		$str = substr($str,$str_offset + strlen($tag_begin));

		// Search for the closing HTML tag. Find the nearest one.
		$temp_lower = strpos($str,strtolower($tag_end));
		$temp_upper = strpos($str,strtoupper($tag_end));
		if($temp_lower === false){
			if($temp_upper === false){
				$str_offset = false;
			}else{
				$str_offset = $temp_upper;
			}
		}else{
			if($temp_upper === false){
				$str_offset = $temp_lower;
			}else{
				$str_offset = min($temp_upper,$temp_lower);
			}
		}
		if($str_offset !== false){

			// Store all the text BETWEEN the HTML tags.
			$temp_str = substr($str,0,$str_offset);

			//
			// Decode HTML entities between the tags.
			if($strip_tags === false){

				/*
				$trans_str = get_html_translation_table(HTML_ENTITIES);
				foreach($trans_str as $k => $v){
					$ttr[$v] = utf8_encode($k);
				}
				$temp_str = strtr($temp_str, $ttr);

				$str_out	.= $temp_str;
				*/

				$str_out .= htmlDecode($temp_str);
			}

			// Store sub_string after the tag.
			$str = substr($str,$str_offset + strlen($tag_end));

			// Search for openning HTML tag again.
			$temp_lower = strpos($str,strtolower($tag_begin));
			$temp_upper = strpos($str,strtoupper($tag_begin));
			if($temp_lower === false){
				if($temp_upper === false){
					$str_offset = false;
				}else{
					$str_offset = $temp_upper;
				}
			}else{
				if($temp_upper === false){
					$str_offset = $temp_lower;
				}else{
					$str_offset = min($temp_upper,$temp_lower);
				}
			}
		}
	}

	// Append remainder of text.
	//
	// All this text will be outside of any HTML tags so
	// we need to encode the line breaks.
	// "\r\n" - WINDOWS
	// "\n"		- UNIX
	// "\r"		- MACINTOSH
	$str = str_replace("\r\n",'<br />',$str);
	$str = str_replace("\n",'<br />',$str);
	$str = str_replace("\r",'<br />',$str);

	// $str = str_replace( chr(10), '<br />', $str );
	$str = $str_out.$str;
	return($str);
}

function replace_url_tag($str,$tag_begin,$tag_end,$tag_close,$strip_tags,$add_no_follow = false)
{

	// Replacements for URL tags. Sub-function of blog_to_html.
	//
	// If $strip_tags == true then it will strip out the tag
	// instead of making them HTML.
	$str_out = NULL;

	// Search for the beginning part of the tag.
	$str_offset = strpos($str,$tag_begin);
	while($str_offset !== false){

		// Store sub_string before the tag.
		$str_out .= substr($str,0,$str_offset);

		// Store sub_string after the tag.
		$str = substr($str,$str_offset + strlen($tag_begin));

		// Search for the ending part of the tag.
		$str_offset = strpos($str,$tag_end);
		if($str_offset !== false){
			if($strip_tags == false){

				// Store attribues BETWEEN between the tags.
				$attrib_array = explode(' ',substr($str,0,$str_offset));
				$attrib_new = 'false';
				if(is_array($attrib_array)){
					$str_url = $attrib_array[0];
					for($i = 1;$i < count($attrib_array);$i++){
						$temp_arr = explode('=',$attrib_array[$i]);
						if(is_array($temp_arr) && count($temp_arr) == 2){
							switch($temp_arr[0]){
								case 'new';
								$attrib_new = $temp_arr[1];
								break;
							}
						}
					}
				}else{
					$str_url = $attrib_array;
				}

				// Append HTML tag.
				if(isset($attrib_new)){
					if($attrib_new == 'false'){
						$str_out .= "<a href=\"".$str_url."\" ";
						if($add_no_follow == true){
							$str_out .= "rel=\"nofollow\">";
						}else{
							$str_out .= ">";
						}
					}else{
						$str_out .= "<a href=\"".$str_url."\" target=\"_blank\" ";
						if($add_no_follow == true){
							$str_out .= "rel=\"nofollow\">";
						}else{
							$str_out .= ">";
						}
					}
				}else{
					$str_out .= "<a href=\"".$str_url."\" target=\"_blank\" ";
					if($add_no_follow == true){
						$str_out .= "rel=\"nofollow\">";
					}else{
						$str_out .= ">";
					}
				}
			}

			// Store sub_string AFTER the tag.
			$str = substr($str,$str_offset + strlen($tag_end));

			/*
			// Look for closing tag.
			$str_offset = strpos( $str, $tag_close );
			if ( $str_offset !== false ){
				$str_link = substr( $str, 0, $str_offset );
				if ( $strip_tags == false ){
					$str_out	.= $str_link . '</a>';
				}else{
					$str_out	.= $str_link;
				}
				$str = substr( $str, $str_offset + strlen( $tag_close ) );
			}
			*/
			// Look for closing tag.
			// HACK "CUT-URL" BY DRUDO ( drudo3	 jumpy	it )
			$str_offset = strpos($str,$tag_close);
			if($str_offset !== false){

				// If the address contains more than 56 characters and begins with "HTTP://"
				if($str_offset >= 56 && (substr($str,0,7)) == "http://"){

					// Store the URL up to the 39th character
					$str_link = substr($str,0,39);

					// Store the final part of the URL
					$str_link_fine = substr($str_url, - 10);
				}else{

					// If the URL is less than 56 characters, store the whole URL
					$str_link = substr($str,0,$str_offset);
				}
				if($strip_tags == false){

					// More than 56 characters
					if($str_offset >= 56 && (substr($str,0,7)) == "http://"){
						$str_out .= $str_link.' ... '.$str_link_fine.'</a>';
					}else{

						// Less than 56 characters
						$str_out .= $str_link.'</a>';
					}
				}else{

					// Strip tags...
					$str_out .= $str_link;
				}
				$str = substr($str,$str_offset + strlen($tag_close));
			}

			// Search for next beginning tag.
			$str_offset = strpos($str,$tag_begin);
		}
	}

	// Append remainder of tag.
	$str = $str_out.$str;
	return($str);
}

function replace_img_tag($str,$tag_begin,$tag_end,$strip_tags)
{

	// Replacements for IMG tags. Sub-function of blog_to_html.
	//
	// I made this another function because I wanted to be able
	// to call it for upper and lower case '[img=]' tags...
	//
	// If $strip_tags == true then it will strip out the tag
	// instead of making them HTML.
	global $theme_vars;
	$str_out = NULL;

	// Search for the beginning part of the tag.
	$str_offset = strpos($str,$tag_begin);
	while($str_offset !== false){

		// Store sub_string before the tag.
		$str_out .= substr($str,0,$str_offset);

		// Store sub_string after the tag.
		$str = substr($str,$str_offset + strlen($tag_begin));

		// Search for the ending part of the tag.
		$str_offset = strpos($str,$tag_end);
		if($str_offset !== false){
			if($strip_tags == true){

				// Store sub_string after the tag.
				$str = substr($str,$str_offset + strlen($tag_end));

				// Search for next beginning tag.
				$str_offset = strpos($str,$tag_begin);
			}else{

				// Store attribues between between the tags.
				$attrib_array = explode(' ',substr($str,0,$str_offset));
				$attrib_width = NULL;
				$attrib_height = NULL;
				$attrib_popup = NULL;
				$attrib_float = NULL;
				$attrib_alt = NULL;
				if(is_array($attrib_array)){
					$str_url = $attrib_array[0];
					for($i = 1;$i < count($attrib_array);$i++){
						$temp_arr = explode('=',$attrib_array[$i]);
						if(is_array($temp_arr) && count($temp_arr) == 2){
							switch($temp_arr[0]){
								case 'width';
								$attrib_width = intval($temp_arr[1]);
								break;
								case 'height';
								$attrib_height = intval($temp_arr[1]);
								break;
								case 'popup';
								$attrib_popup = $temp_arr[1];
								break;
								case 'float';
								$attrib_float = $temp_arr[1];
								break;
								case 'alt';
								$attrib_alt = $temp_arr[1];
								break;
							}
						}
					}
				}else{
					$str_url = $attrib_array;
				}

				// Grab image size and calculate scaled sizes
				// if ( file_exists( $str_url ) !== false ){
				$img_size = @getimagesize($str_url);
				if($img_size !== false){
					$width = $img_size[0];
					$height = $img_size[1];
					$max_image_width = $theme_vars['max_image_width'];
					$auto_resize = true;
					if(isset($attrib_width) && isset($attrib_height)){

						// Both width and height are set.
						$width = $attrib_width;
						$height = $attrib_height;
						$auto_resize = false;
					}else{
						if(isset($attrib_width)){

							// Only width is set. Calculate relative height.
							$height = round($height * ($attrib_width / $width));
							$width = $attrib_width;
							$auto_resize = false;
						}
						if(isset($attrib_height)){

							// Only height is set. Calculate relative width.
							$width = round($width * ($attrib_height / $height));
							$height = $attrib_height;
							$auto_resize = false;
						}
					}
					if($auto_resize == true){
						if($width > $max_image_width){
							$height = round($height * ($max_image_width / $width));
							$width = $max_image_width;
						}
					}
					if(!isset($attrib_popup)){
						if($width != $img_size[0] || $height != $img_size[1]){
							$attrib_popup = 'true';
						}else{
							$attrib_popup = 'false';
						}
					}
					if($attrib_popup == 'true'){

						// Pop Up True
						$str_out .= '<a href="javascript:openpopup(\''.$str_url.'\','.$img_size[0].','.$img_size[1].',false);"><img src="'.$str_url.'" width="'.$width.'" height="'.$height.'" alt=""';
						if(isset($attrib_float)){
							switch($attrib_float){
								case 'left';
								$str_out .= ' id="img_float_left"';
								break;
								case 'right';
								$str_out .= ' id="img_float_right"';
								break;
							}
						}
						$str_out .= ' /></a>';
					}else{

						// Pop Up False
						$str_out .= '<img src="'.$str_url.'" width="'.$width.'" height="'.$height.'" alt=""';
						if(isset($attrib_float)){
							switch($attrib_float){
								case 'left';
								$str_out .= ' id="img_float_left"';
								break;
								case 'right';
								$str_out .= ' id="img_float_right"';
								break;
							}
						}
						$str_out .= ' />';
					}

					// Store sub_string after the tag.
					$str = substr($str,$str_offset + strlen($tag_end));

					// Search for next beginning tag.
					$str_offset = strpos($str,$tag_begin);
				}else{

					// Append HTML tag.
					if(isset($attrib_popup)){
						if($attrib_popup == 'true'){
							$str_out .= '<a href="javascript:openpopup(\''.$str_url.'\',800,600,false);"><img src="'.$str_url.'" alt="" /></a>';
						}else{
							$str_out .= '<img src="'.$str_url.'" alt="" ';
							if(!empty($attrib_width)) 
								$str_out .= " width='$attrib_width px'";
							if(!empty($attrib_height)) 
								$str_out .= " height='$attrib_height px'";
							$str_out .= ' />';
						}
					}else{
						$str_out .= '<a href="javascript:openpopup(\''.$str_url.'\',800,600,false);"><img src="'.$str_url.'" alt="" /></a>';
					}

					// Store sub_string after the tag.
					$str = substr($str,$str_offset + strlen($tag_end));

					// Search for next beginning tag.
					$str_offset = strpos($str,$tag_begin);
				}
			}
		}
	}

	// Append remainder of tag.
	$str = $str_out.$str;
	return($str);
}

function sb_parse_url($text)
{

	// eregi_replace is deprecated, use preg_replace with /i instead
	$text = preg_replace("/([[:space:]])((f|ht)tps?:\/\/[a-z0-9~#%@\&:=?+\/\.,_-]+[a-z0-9~#%@\&=?+\/_.;-]+)/i","\\1[url=\\2]\\2[/url]",$text);

	//http
	$text = preg_replace("/([[:space:]])(www\.[a-z0-9~#%@\&:=?+\/\.,_-]+[a-z0-9~#%@\&=?+\/_.;-]+)/i","\\1[url=http://\\2]\\2[/url]",$text);

	// www.
	$text = preg_replace("/([[:space:]])([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6})/i","\\1[url=mailto:\\2]\\2[/url]",$text);

	// mail
	// Al principio de una cadena
	$text = preg_replace("/^((f|ht)tps?:\/\/[a-z0-9~#%@\&:=?+\/\.,_-]+[a-z0-9~#%@\&=?+\/_.;-]+)/i","[url=\\1]\\1[/url]",$text);

	//http
	$text = preg_replace("/^(www\.[a-z0-9~#%@\&:=?+\/\.,_-]+[a-z0-9~#%@\&=?+\/_.;-]+)/i","[url=http://\\1]\\1[/url]",$text);

	// www
	$text = preg_replace("/^([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6})/i","[url=mailto:\\1]\\1[/url]",$text);

	// mail
	return($text);
}

function replace_more_tag($string,$strip_tags = true,$url = '',$trim_off_end = false)
{
	global $lang_string;
	$tagpos = strpos(strtoupper($string),'[MORE]');
	if($tagpos != false){
		if($strip_tags == true){
			$tagstart = strpos(strtoupper($string),'[MORE]');
			$tagend = $tagstart + strlen('[MORE]');
			$tmpstr = substr($string,0,$tagpos);
			if($trim_off_end == true){
				$string = $tmpstr;
			}else{
				$tmpstr .= substr($string,$tagend,strlen($string));
				$string = $tmpstr;
			}
		}else{
			$string = substr($string,0,$tagpos);

			//Now put in the More link
			if($url != ''){
				$string .= ' <a href="'.$url.'">'._sb('read_more').'</a><br />';
			}
		}
	}
	return($string);
}

function get_init_code(&$page_template)
{
	global $blog_config;

	// Meta Data
	$page_template->setTag('{HTML_CHARSET}',$GLOBALS['lang_string']['html_charset']);
	$page_template->setTag('{BLOG_TITLE}',$blog_config->getTag('BLOG_TITLE'));
	$page_template->setTag('{BLOG_AUTHOR}',$blog_config->getTag('BLOG_AUTHOR'));
	$page_template->setTag('{INFO_KEYWORDS}',$blog_config->getTag('INFO_KEYWORDS'));
	$page_template->setTag('{INFO_DESCRIPTION}',$blog_config->getTag('INFO_DESCRIPTION'));
	$page_template->setTag('{INFO_COPYRIGHT}',$blog_config->getTag('INFO_COPYRIGHT'));
	$page_template->setTag('{LOCALE}',str_replace('_','-',$GLOBALS['lang_string']['locale']));
	if((dirname($_SERVER['PHP_SELF']) == '\\' || dirname($_SERVER['PHP_SELF']) == '/')){
		$page_template->setTag('{URI}',sb_curPageURL().'/index.php');

		// Blog is root level
	}else{
		$page_template->setTag('{URI}',dirname(sb_curPageURL()).'/index.php');

		// Blog is in sub-directory
	}
	$page_template->setTag('{SEARCH_URI}',dirname($page_template->getTag('{URI}')).'/plugins/search.php');

	// Theme Style Sheet
	$page_template->setTag('{BLOG_THEME}',$GLOBALS['blog_theme']);

	// User Color CSS Override
	//ob_start();
	//require_once('themes/'.$GLOBALS['blog_theme'].'/user_style.php');
	//$page_template->setTag('{CSS}', ob_get_clean());
	// Javascript
	$page_template->setTag('{JAVASCRIPT}','');
}
?>
