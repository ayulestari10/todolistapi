<?php 
require(APPPATH . 'libraries/Rest.php');

class SetupApplication extends Rest
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index_get()
	{
		$this->load->model('M_SetupApplication');
		$data_setup = $this->M_SetupApplication->get();

		for($i = 0; $i < count($data_setup); $i++) {
			$setup[$data_setup[$i]->DESCRIPTION] = $data_setup[$i]->SETUP_VALUE;
		}

		$this->response($setup);
	}

	public function setup_get()
	{
		$this->load->model('M_SetupApplication');
		$id 		= $this->input->get('ID');
		$data_setup = $this->M_SetupApplication->get_row(['ID' => $id]);

		if(count($data_setup) > 0){
			if($id == '4'){
				$data_setup = (float) $data_setup['SETUP_VALUE'];
			}
			else{
				$data_setup = $data_setup['SETUP_VALUE'];
			}
		}
		else{
			$data_setup = "";
		}

		$this->response($data_setup);
	}
}

?>