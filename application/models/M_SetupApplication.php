<?php 

class M_SetupApplication extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();
		$this->data['table_name']	= LEGACY . '.POD_SETUP_APPLICATION';
		$this->data['primary_key']	= 'ID';
	}
}

?>