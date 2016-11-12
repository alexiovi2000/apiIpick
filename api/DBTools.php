<?php
/**
 * @author iovino Alex
 * 
 * Classe di accesso al dbMysql
 * 
 * Una classe di accesso ai dati dovrebbe sempre essre un singleton
 * 
 * $instanceDb = DB_SQLite::getInstance();
 * 
 * 
 *
 */

class DBTools
{
    
    private static $instance = null;
    private $dbMysql;
    
    
    private function __construct(){
      
       $this->mySql = new PDO(
                            'mysql:host=localhost;dbname=my_ipicktrack',
                            'ipicktrack',
                            'vimcecidgo32'
                       );
       
        /**
         * Il seguente settaggio è utile (forse sarebbe meglio dire "fondamentale")
         * perchè permette di "catchare" gli errori db, altrimenti
         * il try-catch non sarebbe efficace ma spara fuori un fatal-error.
         * 
         * 
         * 
         */
        $this->mySql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        /**
         * con questo gli array associativi in output hanno campo con case maiuscolo
         */
        $this->mySql->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
    }
    
    
    public static function getInstance(){
        
       if (null==self::$instance){
           self::$instance = new DBTools();
       }
          
       return self::$instance;
        
    }
    
    public function getDbConnection(){
        return $this->mySql;
    }
    
}

?>