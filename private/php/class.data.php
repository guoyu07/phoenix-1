<?php


/**
 * Database host. Use 127.0.0.1 instead of localhost
 * @package     Phoenix
 */
define('DB_HOST',   '127.0.0.1');

/**
 * @package     Phoenix
 */
define('DB_USER',   'webuser');

/**
 * @package     Phoenix
 */
define('DB_PASS',   'Ci$L1n0d3VPS');

/**
 * @package     Phoenix
 */
define('DB_NAME',   'phoenix');

/**
 * @package     Phoenix
 */
define('DB_DSN',    'mysql:dbname='.DB_NAME.';host='. DB_HOST);

/**
 * Database class
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20624
 * @package     Phoenix
 */
class Data {
    
    /**
     * Forwarder variable. <b>Internal Use Only</b>
     */
    private static      $objInstance; 
    
    /**
     * Constructor is set to protected so nobody can access it
     *
     * @see         __destruct()
     */
    protected function __construct() {
        return null;
    }
    
    /**
     * Destruct is set to protected so nobody can access it
     *
     * @see __construct()
     */
    protected function __destruct() {
        return null;
    }
    
    /**
     * Used for forwarding requests so we don't need <code>new Data</code>
     */
    private function __clone() {} 
    
    /** 
     * Returns DB instance or create initial connection 
     *
     * @return $objInstance; 
     */ 
    public static function getInstance() { 
            
        if(!self::$objInstance){ 
            self::$objInstance = new PDO(DB_DSN, DB_USER, DB_PASS); 
            self::$objInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } 
        
        return self::$objInstance; 
    
    }
    
    /** 
     * Passes on any static calls to this class onto the singleton PDO instance 
     * @param $chrMethod
     * @param $arrArguments 
     * @return $mix 
     */ 
    final public static function __callStatic( $chrMethod, $arrArguments ) { 
            
        $objInstance = self::getInstance(); 
        
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments); 
        
    } # end method 
    
    
}

?>