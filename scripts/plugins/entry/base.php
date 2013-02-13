<?php
class base
{
	public $priority = 0x00;

	function main($s)
	{
		static $tags = array
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
			'PRE' =>'<pre>',
			'/PRE' =>'</pre>',
			'STRIKE' =>'<strike>',
			'/STRIKE' =>'</strike>',
			'STRONG' =>'<strong>',
			'/STRONG' =>'</strong>',
			'UNDERLINE' =>'<u>',
			'/UNDERLINE' =>'</u>',
		);

		return(preg_replace('/\[(#|\!)?(\/?[0-9A-Z]{2,})\]/eisS','\'\1\' ? ("\1" == "#" ? "&#\2;" : "<\2>") : (isset($tags[strtoupper(\'\2\')]) ? $tags[strtoupper(\'\2\')] : \'[\1\2]\')',$s));
	}
}
?>