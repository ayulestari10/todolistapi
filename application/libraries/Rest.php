<?php 
require(APPPATH . 'libraries/REST_Controller.php');

class Rest extends \Restserver\Libraries\REST_Controller
{
	protected $currentDateCore;
	protected $currentTimeCore;

	public function isValidJSON($str) {
       json_decode($str);
       return json_last_error() == JSON_ERROR_NONE;
    }

    public function getDataFromApp(){
        if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
            $dataJson = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        else{
            $dataJson = file_get_contents('php://input');
        }

        if (strlen($dataJson) > 0 && self::isValidJSON($dataJson)){
            $value = json_decode($dataJson, true);

            if(isset($value[0]) && is_array($value[0])){
                $value = $value[0];
            }

            return $value;
        }

        return false;
    }

	public function __construct()
	{
		date_default_timezone_set("Asia/Jakarta");
		// Allow from any origin
	    if (isset($_SERVER['HTTP_ORIGIN'])) 
	    {
	        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
	        // you want to allow, and if so:
	        // header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	        header('Access-Control-Allow-Origin: *');
	        header('Access-Control-Allow-Credentials: true');
	        header('Access-Control-Max-Age: 86400');    // cache for 1 day
	    }

	    // Access-Control headers are received during OPTIONS requests
	    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') 
	    {
	        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
	            // may also be using PUT, PATCH, HEAD etc
	            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

	        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
	            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	        exit(0);
	    }

	    parent::__construct();
	    
	    $this->load->model('M_SetupApplication');
	    // var_dump($_SERVER['REQUEST_METHOD']);exit;

	    $setupId = null;
	    switch ($_SERVER['REQUEST_METHOD'])
	    {
	    	case 'POST':
	    		$setupId = $this->input->post('SETUP_ID');
	    		$version = $this->input->post('VERSION');
	    		break;

	    	case 'GET':
	    		$setupId = $this->input->get('SETUP_ID');
	    		$version = $this->input->get('VERSION');
	    		break;
	    }

	    if(!isset($setupId)){
	    	$value      = self::getDataFromApp();
	    	$setupId 	= isset($value['SETUP_ID'])? $value['SETUP_ID'] : null;
	    	$version 	= isset($value['VERSION'])? $value['VERSION'] : null;
	    }


	    if (isset($setupId))
	    {
	    	$setup = $this->M_SetupApplication->get_row(['ID' => $setupId]);

	    	if ($setup['SETUP_VALUE'] != $version)
	    	{
	    		$this->data = [
	    			'code'		=> FORBIDDEN,
					'status'	=> 'FORBIDDEN',
					'message'	=> 'Tolong update aplikasi ke versi ' . $setup['SETUP_VALUE'] . '. Versi aplikasi sekarang ' . $version
				];

				$this->response($this->data, FORBIDDEN);
				exit;
	    	}
	    }
	    else
	    {
	    	$this->data = [
    			'code'		=> FORBIDDEN,
				'status'	=> 'FORBIDDEN',
				'message'	=> 'Tolong update aplikasi ke versi terbaru'
			];

			$this->response($this->data, FORBIDDEN);
			exit;
	    }

		$this->load->library('ci_jwt');
	}

	protected function check_token_get()
	{
		$token = $this->get('token');
		if (!isset($token))
		{
			$this->response(EMPTY_TOKEN);
			exit;
		}

		try
		{
			$payload = $this->ci_jwt->decode($token);
		}
		catch (Exception $e)
		{
			$this->data = [
				'code'		=> BAD_REQUEST,
				'status'	=> 'BAD_REQUEST',
				'message'	=> $e->getMessage()
			];
			$this->response(BAD_REQUEST);
			exit;
		}
		
		$this->load->model('M_GenAccount');
		$user = $this->M_GenAccount->get_row(['GEN_USER' => $payload->GEN_USER]);

		if (!isset($user))
		{
			$this->data = [
				'code'		=> UNAUTHORIZED,
				'status'	=> 'UNAUTHORIZED',
				'message'	=> 'Anda tidak memiliki akses'
			];

			$this->response(UNAUTHORIZED);
			exit;
		}

		// extend token expiration
		$datetime = new DateTime('+30days');
		$payload->EXPIRED_AT = $datetime->format('Y-m-d H:i:s');
		$token = $this->ci_jwt->encode((array)$payload);

		return $token;
	}

	protected function check_token_post()
	{
		$token = $this->post('token');
		if (!isset($token))
		{
			$this->data = [
				'code'		=> BAD_REQUEST,
				'status'	=> 'BAD_REQUEST',
				'message'	=> 'Anda tidak memiliki token'
			];

			$this->response(EMPTY_TOKEN);
			exit;
		}

		try
		{
			$payload = $this->ci_jwt->decode($token);
		}
		catch (Exception $e)
		{
			$this->data = [
				'code'		=> BAD_REQUEST,
				'status'	=> 'BAD_REQUEST',
				'message'	=> $e->getMessage()
			];
			$this->response(BAD_REQUEST);
			exit;
		}
		
		$this->load->model('M_GenAccount');
		$user = $this->M_GenAccount->get_row(['GEN_USER' => $payload->GEN_USER]);

		if (!isset($user))
		{
			$this->data = [
				'code'		=> UNAUTHORIZED,
				'status'	=> 'UNAUTHORIZED',
				'message'	=> 'Anda tidak memiliki akses'
			];

			$this->response(UNAUTHORIZED);
			exit;
		}
		
		// extend token expiration
		$datetime = new DateTime('+30days');
		$payload->EXPIRED_AT = $datetime->format('Y-m-d H:i:s');
		$token = $this->ci_jwt->encode((array)$payload);

		return $token;
	}

	protected function check_token_post2()
	{
		$token = $this->post('token');
		if (!isset($token))
		{
			$response       = [
                'status'    => EMPTY_TOKEN,
                'message'   => 'Anda tidak memiliki token'
            ];
            $this->response($response);
            exit;
		}

		try
		{
			$payload = $this->ci_jwt->decode($token);
		}
		catch (Exception $e)
		{

			$response       = [
                'status'    => BAD_REQUEST,
                'message'   => 'Bad Request'
            ];
            $this->response($response);
            exit;
		}
		
		$this->load->model('M_GenAccount');
		$user = $this->M_GenAccount->get_row(['GEN_USER' => $payload->GEN_USER]);

		if (!isset($user))
		{

			$response       = [
                'status'    => UNAUTHORIZED,
                'message'   => 'Anda tidak memiliki akses'
            ];
            $this->response($response);
            exit;
		}
		
		// extend token expiration
		$datetime = new DateTime('+30days');
		$payload->EXPIRED_AT = $datetime->format('Y-m-d H:i:s');
		$token = $this->ci_jwt->encode((array)$payload);

		return $token;
	}

	protected function remove_directory($directory)
	{
	    foreach(glob($directory . '/*') as $file)
	    {
	        if(is_dir($file)) 
	        { 
	            recursiveRemoveDirectory($file);
	        } else 
	        {
	            @unlink($file);
	        }
	    }
	    rmdir($directory);
	}

	protected function decode_base64_file($base64_string, $output_file)
    {
        $file = fopen($output_file, 'wb');
        $data = explode(',', $base64_string);

		if (count($data) < 1)
		{
			$this->data = [
				'status'	=> 'failed',
				'message'	=> 'You must upload an appropriate image'
			];
			$this->response($this->data);
			exit;
		}

		fwrite($file, base64_decode($data[1]));
		fclose($file);
		
		return $outputFile;
    }

    protected function get_current_datetime()
    {
    	$currentDateTime 	= explode(' ', date('Y-m-d H:i:s'));
		$currentDate 		= str_replace('-', '', $currentDateTime[0]);
		$currentTime 		= str_replace(':', '', $currentDateTime[1]);
		return [
			'date'	=> $currentDate,
			'time'	=> $currentTime
		];
    }
}