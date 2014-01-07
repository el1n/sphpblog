<?php
class CreativeCommons extends Sidebar
{
	var $plugin;

	/* ------ INITIALIZE ------ */

	function CreativeCommons()
	{
		$this->plugin = 'CreativeCommons';
		$this->loadPrefs();
	}

	/* ------ PREFERENCES ------ */

	function defaultPrefs()
	{
		$arr = parent::defaultPrefs();
		$arr['license'] = 'by-nc-nd';
		$arr['licensever'] = '3.0';
		return $arr;
	}

	/* ------ OPTIONS ------ */

	function optionsForm()
	{
		$sel = ' selected="selected" ';
		$license = $this->prefs['license'];
		$licensever = $this->prefs['licensever'];
		$save = _sb('submit_btn');
		ob_start();?>
                        <!-- FORM -->
			<p>Creative Commons License</p>
			<p><a href="http://creativecommons.org/choose/">Get help choosing a Creative Commons license for your work.</a></p>
                        <form method="post" onsubmit="return validate(this)">
				<select name="license">
					<option value='by' <?php if($license == 'by'){
			echo $sel;
		}?>>Attribution</option>
					<option value='by-nc' <?php if($license == 'by-nc'){
			echo $sel;
		}?>>Attribution Non-Commercial</option>
					<option value='by-sa' <?php if($license == 'by-sa'){
			echo $sel;
		}?>>Attribution Share Alike</option>
					<option value='by-nc-sa' <?php if($license == 'by-nc-sa'){
			echo $sel;
		}?>>Attribution Non-Commercial Share Alike</option>
					<option value='by-nd' <?php if($license == 'by-nd'){
			echo $sel;
		}?>>Attribution No Derivatives</option>
					<option value='by-nc-nd' <?php if($license == 'by-nc-nd'){
			echo $sel;
		}?>>Attribution Non-Commercial No Derivatives</option>
				</select>
				<select name="licensever">
					<option value='3.0' <?php if($licensever == '3.0'){
			echo $sel;
		}?>>3.0</option>
					<option value='2.5' <?php if($licensever == '2.5'){
			echo $sel;
		}?>>2.5</option>
					<option value='2.0' <?php if($licensever == '2.0'){
			echo $sel;
		}?>>2.0</option>
					<option value='1.0' <?php if($licensever == '1.0'){
			echo $sel;
		}?>>1.0</option>
				</select>

                                <input type="submit" name="save" value="<?php echo($save);?>" />
                        </form>
                        <?php
                        $str = ob_get_clean();
		return $str;
	}

	function optionsPost()
	{
		if(array_key_exists('save',$_POST)){
			$value = '';
			if(array_key_exists('license',$_POST)){
				$this->prefs['license'] = $_POST['license'];
			}
			if(array_key_exists('licensever',$_POST)){
				$this->prefs['licensever'] = $_POST['licensever'];
			}
			$this->savePrefs();
		}
	}

	/* ------ GETTERS & SETTERS ------ */

	function getTitle()
	{
		return 'Creative Commons';
	}

	function getContent()
	{

		// WEB BADGES
		$html = '<div style="text-align: center">'."\n";
		if(!empty($this->prefs['license'])) 
			$html .= $this->get_license($this->prefs['license'],$this->prefs['licensever']);
		$html .= '</div>'."\n";
		return $html;
	}

	function get_license($name,$v = "3.0")
	{
		$cc['by'] = 'Attribution';
		$cc['by-nc'] = 'Attribution-NonCommercial';
		$cc['by-sa'] = 'Attribution-ShareAlike';
		$cc['by-nc-sa'] = 'Attribution-NonCommercial-ShareAlike';
		$cc['by-nd'] = 'Attribution-NoDerivs';
		$cc['by-nc-nd'] = 'Attribution-NonCommercial-NoDerivs';
		if(array_key_exists($name,$cc)){
			$lookup = $cc[$name];
		}
		if(empty($lookup)){
			return '';
		}
		return '
<!--Creative Commons License--><a rel="license" href="http://creativecommons.org/licenses/' . $name . '/' . $v . '/"><img alt="Creative Commons License" 
height="31" width="88" src="http://i.creativecommons.org/l/' . $name . '/' . $v . '/88x31.png"/></a><br/>This work is licensed under a <a rel="license" 
href="http://creativecommons.org/licenses/' . $name . '/' . $v . '/">Creative Commons ' . $lookup . ' ' . $v . ' License</a>.<!--/Creative Commons License-->
';
	}
}
?>
