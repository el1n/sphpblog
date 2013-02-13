<?php
class phpembed
{
	public $priority = 0x10;

	function main($s)
	{
		return(preg_replace('/\[PHP\](.*?)\[\/PHP\]/eisS','eval(htmlspecialchars_decode(\'\1\',ENT_QUOTES))',$s));
	}
}
?>
