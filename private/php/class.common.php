<?php

/**
 * The Common class provides default page construction common functions
 * and classes.
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20627
 * @package     Phoenix
 */
class Common {
    
    /**
     * Output buffer and Content-Type handler
     *
     * @param string $scriptType Diagnostic script type. Default outputs plaintext
     * @access  public
     * @return  true Always.
     * 
     */
    public function __construct($scriptType = 'HTML') {
    
        // Turn on error reporting
        if (defined(BETA) && (BETA === true)) {
            error_reporting(E_ALL | ~E_NOTICE);
            ini_set('display_errors','On');
        }
        
        // Script type switcher
        switch($scriptType) {
            case 'HTML':
                date_default_timezone_set('Asia/Hong_Kong');
                ob_start('ob_gzhandler');
                header('Pragma: no-cache');
                header('Content-Type: text/html; charset=utf-8');
            break;
            case 'JSON':
                header('Content-Type: application/json');
            break;
            case 'PLAIN':
                header('Content-Type: text/plain');
            break;
            case 'CSS':
                header('Content-Type: text/css');
            break;
            case 'JS':
                header('Content-Type: text/javascript');
            break;
            default:
                // Be safe, use plaintext
                header('Content-Type: text/plain');
            break;
        }
        
        // Initiate security class
        $this->security = Security::initiateSecurity();
        
        return true;
        
    }
    
}

?>