<?php
class fileembed
{
	public $priority = 0x10;

	function main($s)
	{
//
		return(preg_replace('/\[INCLUDE=(.+?)\]/eisS','is_file(ROOT_DIR.\'\1\') ? file_get_contents(ROOT_DIR.\'\1\') : \'\';',$s));
	}
}
?>
