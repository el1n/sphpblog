<?php
class pukiwiki
{
	public $priority = 0x02;
	private $b = false;

	function pukiwiki()
	{
		if($this->b = file_exists($file = ROOT_DIR.'scripts/plugins/entry/pukiwiki/lib/convert_html.php')){
			require_once($file);
		}
	}

	function main($s)
	{
		return($this->b ? convert_html($s) : $s);
	}
}
?>
