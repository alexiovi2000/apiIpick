<?php

abstract class API
{
    protected $method = '';
    protected $endpoint = '';
    protected $verb = '';
    protected $args = Array();
    protected $file = Null;
	protected $status;
	
    public function __construct($request,$queryString) {
		$this->status = 200;
		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_reverse($this->args); 
		$this->endpoint = $this->endpoint[0];
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
        
       
        switch($this->method) {
			case 'DELETE':
				if($this->endpoint=='users') {
					$this->args = json_decode(file_get_contents('php://input'), true);
					$this->endpoint = (count($this->args)==0?'deleteAllUsers':'deleteUserById');
				}
				break;
			case 'POST':		
			    $this->args = $_POST;
				if($this->endpoint=='users') {
					$this->endpoint = 'insertUser';
					$this->status = 201;
				} 
				break;
			case 'GET':
				break;
			case 'PUT':
				if($this->endpoint=='users') {
					$this->args = json_decode(file_get_contents('php://input'), true);
					$this->endpoint = 'updateUser';
				}
				break;
			default:
				$this->status = 405;
				$this->_response('Invalid Method');
				break;
        }
    }
	
	public function processAPI() {
		if (method_exists($this, $this->endpoint)) {
			if($this->endpoint=='login' || $this->endpoint=='insertUser') {
				return $this->_response($this->{$this->endpoint}($this->args));	
			}
			else {
			    if ($this->endpoint=='insertUser'){
			        
			    }
				$authHeader = get_headers();
				if ($authHeader['Authorization']) {
					list($jwt) = sscanf( $authHeader, 'Bearer %s');

					if ($jwt) {
						try {
							$secretKey = 'test';//base64_decode($config->get('jwtKey'));
							$token = JWT::decode($jwt, $secretKey, array('HS512'));
							return $this->_response($this->{$this->endpoint}($this->args));
						} catch (Exception $e) {
							header('HTTP/1.0 401 Unauthorized');
						}
					} else {
						header('HTTP/1.0 400 Bad Request');
					}
				} else {
					header('HTTP/1.0 400 Bad Request');
					echo 'Token not found in request';
				}	
		  }
		} else {
			$this->status = 404;
			return $this->_response("No Endpoint: $this->endpoint");
		}
    }

    //private function _response($data, $status = 200) {
	private function _response($data) {
        header("HTTP/1.1 " . $this->status . " " . $this->_requestStatus($this->status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
			201 => 'Created',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}
?>