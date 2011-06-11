<?php

class page_cat extends page_base{
	function get(){
		echo $this->get_request_path();
	}
}

?>