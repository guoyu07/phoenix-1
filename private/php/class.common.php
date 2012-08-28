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
        Security::initiateSecurity();
        
        return true;
        
    }
    
    /**
     * Adds an action into the log table
     *
     * @package Phoenix
     * @version 20819
     */
    public function logAction($action, $result, $remarks = '', $ip = false, $ua = false) {
    
        // Set defaults
        $ip = (($ip === FALSE) ? $_SERVER['REMOTE_ADDR'] : $ip);
        $ua = (($ua === FALSE) ? $_SERVER['HTTP_USER_AGENT'] : $ua);
        
        // Add to database...
        try {
            $stmt = Data::prepare("INSERT INTO `log` (`LogTS`, `LogIP`, `LogUA`, `LogAction`, `LogResult`, `LogRemarks`) VALUES (NOW(), :ip, :ua, :action, :result, :remarks)");
            $stmt->bindParam('ip', $ip);
            $stmt->bindParam('ua', $ua);
            $stmt->bindParam('action', $action);
            $stmt->bindParam('result', $result);
            $stmt->bindParam('remarks', $remarks);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->throwNiceDataException($e);
        }
    }
    
    /**
     * Throws a nicely formatted error page
     *
     * @package Phoenix
     * @version 20819
     * @param PDOException $e PDO Exception object from failed query
     */
    public function throwNiceDataException($e) {
        
        $pretext = "A database error occured. The data object handler responded with:\n\n" . $e->getMessage() . "\n\nBest stacktrace:\n" . $e->getTraceAsString();
        $this->niceException($pretext);
        return null;
        
    }
    
    
    /**
     * Error handling output function
     *
     * @package Phoenix
     * @version 20819
     * @param string $pretext Unformatted (for insertion within a <pre> tag) error technical information. Use stacktraces where applicable.
     */
    private function niceException($pretext = 'No further information supplied') {
        require_once('class.ux.php');
        echo UX::grabPage('dev/error', array('pretext' => $pretext), true);
        exit();
    }
}

?>