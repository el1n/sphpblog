<?php

flush();
if(function_exists('page_content')){
	if(function_exists('theme_pagelayout')){
		theme_pagelayout();
	}else{
		page_content();
	}
}

if(CUSTOM_HEADER){
	// put custom header code here
	@include_once("$webroot/include/footer.php");
}else{
	print "</body></html>";
}
?>
