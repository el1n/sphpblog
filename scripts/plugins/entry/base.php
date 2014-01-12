<?php
class base
{
	public $priority = 0x01;
	private $regx;

	function base($s)
	{
		$this->regx = array
		(
			'/\[\[(?:(.*?)(:)(?!\/{2}))?(?:([a-z]*?):(?!\/{2}))?(.+?)\]\]/iS' =>
			function($m){
				switch(true){
					case preg_match('/s/',$m[3]):
						$m[3] = '_self';
						break;
					case preg_match('/b/',$m[3]):
					default:
						$m[3] = '_blank';
						break;
				}
				return(sprintf('<a href="%s" target="%s">%s</a>',$m[4],$m[3],$m[1] ? $m[1] : ($m[2] ? basename($m[4]) : $m[4])));
			},
			'/\[\{(?:(.+?):(?!\/{2}))?(?:([a-z]*?):(?!\/{2}))?(.+?\.(?:bmp|gif|jpe?g|png))\}\]/iS' =>
			function($m){
				switch(true){
					case preg_match('/p/',$m[2]):
						$m[2] = ' popup=true';
						break;
					default:
						$m[2] = ' popup=false';
						break;
				}
				return(sprintf('[IMG=%s%s]',$m[3],$m[2]));
			},
			'/\[([!#$\.])?(\/?(?:\w{2,})?)\]/isS' =>
			function($m){
				static $prev;
				static $s = array
				(
					'BOLD' =>'<b>',
					'/BOLD' =>'</b>',
					'BLOCKQUOTE' =>'<blockquote>',
					'/BLOCKQUOTE' =>'</blockquote>',
					'CENTER' =>'<center>',
					'/CENTER' =>'</center>',
					'CODE' =>'<code>',
					'/CODE' =>'</code>',
					'DEL' =>'<del>',
					'/DEL' =>'</del>',
					'EM' =>'<em>',
					'/EM' =>'</em>',
					'H1' =>'<h1>',
					'/H1' =>'</h1>',
					'H2' =>'<h2>',
					'/H2' =>'</h2>',
					'H3' =>'<h3>',
					'/H3' =>'</h3>',
					'H4' =>'<h4>',
					'/H4' =>'</h4>',
					'H5' =>'<h5>',
					'/H5' =>'</h5>',
					'H6' =>'<h6>',
					'/H6' =>'</h6>',
					'HR' =>'<hr>',
					'ITALIC' =>'<i>',
					'/ITALIC' =>'</i>',
					'INS' =>'<ins>',
					'/INS' =>'</ins>',
					'JS' =>'[HTML]<script type="text/javascript">',
					'/JS' =>'</script>[/HTML]',
					'CSS' =>'[HTML]<style type="text/css">',
					'/CSS' =>'</style>[/HTML]',
					'PRE' =>'<pre>',
					'/PRE' =>'</pre>',
					'STRIKE' =>'<strike>',
					'/STRIKE' =>'</strike>',
					'STRONG' =>'<strong>',
					'/STRONG' =>'</strong>',
					'UNDERLINE' =>'<u>',
					'/UNDERLINE' =>'</u>',
				);

				switch($m[1]){
					case '!':
						return('['.$m[2].']');
						break;
					case '$':
						$prev = $m[2];
						return('<'.$m[2].'>');
						break;
					case '#':
						return('&#'.$m[2].';');
						break;
					case '.':
						$prev = "span";
						return('<span class="'.$m[2].'">');
						break;
					default:
						break;
				}
				switch($m[2]){
					case '/':
						return('</'.$r.'>');
						break;
					default:
						if(isset($s[strtoupper($m[2])])){
							$prev = $m[2];
							return($s[strtoupper($m[2])]);
						}else{
							return('['.$m[1].$m[2].']');
						}
						break;
				}
			},
			'/\[&lt;(.+?)(?::([a-z]*?))?&gt;\]/iS' =>
			function($m){
				switch(true){
					case preg_match('/s/',$m[2]):
						$m[2] = '_self';
						break;
					case preg_match('/b/',$m[2]):
					default:
						$m[2] = '_blank';
						break;
				}
				return(sprintf('<a href="%s?entry=%s" target="%s">%s</a>',BASEURL,urlencode($m[1]),$m[2],$m[1]));
			},
		);
	}

	function main($s)
	{
		foreach($this->regx as $regx =>$func){
			$s = preg_replace_callback($regx,$func,$s);
		}
		return($s);
	}
}
?>
