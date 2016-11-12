<?php
require_once 'API.class.php';
require_once 'DBTools.php';

class MyAPI extends API
{
    protected $db;
    
    
    public function __construct($request, $queryString) {
        $this->db = DBTools::getInstance(); 
        $GLOBALS['dbConn'] = $this->db->getDbConnection();
        parent::__construct($request,$queryString);
    }
	
	
	private function checkUserAndEmail($username,$email){
	    
	    try{
	        
	        $query = "select username,email from user_anag u where u.username=:p_username or u.email = :p_email ";
	        $stmt = $GLOBALS['dbConn']->prepare($query);
	        $stmt->bindValue(':p_email',$email);
	        $stmt->bindValue(':p_username',$username);
	        $stmt->execute();
	        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	        $checkUsername = true;
	        $checkEmail = true;
	        if ($data){
	            foreach ($data as $key=>$value){
	                if ($value['USERNAME'] == $username){
	                    $checkUsername = false;
	                }
	                if ($value['EMAIL'] == $email){
	                    $checkEmail = false;
	                }
	            }
	            return array("success"=> false, "mail"=>$checkEmail,"username" => $checkUsername  );
	        }else{
	            return array("success"=>true);
	        }
	        
	        
	    }catch(PDOException $e){
	        
	       throw new Exception("Impossible to insert new User, retry more later");
	    }
	    
	    
	}
	
	
	protected function insertUser($data) {
	    try
	    {
	        $checkUser = $this->checkUserAndEmail($data['username'],$data['email']);
	        if (!$checkUser['success']){
	            if (!$checkUser['username']){
	                throw new Exception("Username just Used");
	            }  
	            if  (!$checkUser['mail']){
	                
	                throw new Exception("Email just Used");
	            }
	        }
	        
	        $query = "INSERT INTO user_anag (nome,cognome,email,username,password) values (:p_nome, :p_cognome ,:p_email ,:p_username, :p_password)";
	        $stmt = $GLOBALS['dbConn']->prepare($query);
	        $stmt->bindValue(':p_nome',$data['nome']);
	        $stmt->bindValue(':p_cognome',$data['cognome']);
	        $stmt->bindValue(':p_email',$data['email']);
	        $stmt->bindValue(':p_username',$data['username']);
	        $stmt->bindValue(':p_password',md5($data['password']));
	        $stmt->execute();
	     
	        return array("success"=>true);
	    }
	    catch (PDOException $e){
	        
	        throw new Exception("Impossible to insert new User, retry more later");
	    
	    }
	}
	
	protected function getUserByUserPass($data) {
	   try
	    {
	        $query = "select id from user_anag where username = :p_username and password = :p_password";
	        $stmt = $GLOBALS['dbConn']->prepare($query);
	        $stmt->bindValue(':p_username',$data['username']);
	        $stmt->bindValue(':p_password',md5($data['password']));
	        $stmt->execute();
	        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	        if (!$data){
	            throw new Exception("Username or Password not correct");
	        }
	        
	        return $data[0]['ID'];
	      
	    }
	    catch (PDOException $e){
	        
	        throw new Exception("Username or Password not correct");
	    
	    }
	}

	
	protected function insertTokenByUser($idUser, $token){
	    try
	    {
	        $query = "insert into token_user (id_utente,token,date) values (:p_id_utente, :p_token, :p_date)";
	        $stmt = $GLOBALS['dbConn']->prepare($query);
	        $stmt->bindValue(':p_id_utente',$idUser);
	        $stmt->bindValue(':p_token',$token);
	        $stmt->bindValue(':p_date',date("Y-m-d H:i:s"));
	        $stmt->execute();
	         
	    }
	    catch (PDOException $e){
	         
	        throw new Exception("Server Error, retry more later");
	         
	    }
	}


	
	protected function login($data) {
	    $idUser = $this->getUserByUserPass($data);
		
	    
		$token = hash("sha256", 
		               date("Y-m-d H:i:s")
		              );
		
		
		
		$this->insertTokenByUser($idUser,$token);
		
		
		return array("success"=>true,"token"=>$token);
	}

	protected function logout() {
		session_destroy();
		return 'Logout';
	}
	
 }
 ?>