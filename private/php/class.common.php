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
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }

        // Set default timezone
        date_default_timezone_set('Asia/Hong_Kong');
        
        // Script type switcher
        switch($scriptType) {
            case 'HTML':
                ob_start('ob_gzhandler');
                header('Content-Type: text/html; charset=utf-8');
                header('X-UA-Compatible: IE=edge,chrome=1');
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
        ACL::initiateSecurity();
        return true;
        
    }
    
    /**
     * Adds an action into the log table
     *
     * @package Phoenix
     * @version 20819
     */
    public function logAction($action, $result, $user = 'UNK=unknown', $remarks = '', $ip = false, $ua = false) {
    
        // Set defaults
        $user = ($user == NULL) ? 'UNK=unknown' : $user;
        $ip = (($ip === FALSE) ? $_SERVER['REMOTE_ADDR'] : $ip);
        $ua = (($ua === FALSE) ? $_SERVER['HTTP_USER_AGENT'] : $ua);
        
        // Try to get GeoIP location
        $record = geoip_record_by_name($ip);
        $geo = (($record) ? $record['city'].', '.$record['country_code'] : '');
        
        // Add to database...
        try {
            $stmt = Data::prepare("INSERT INTO `log` (`LogTS`, `LogIP`, `LogGeoResult`, `LogUA`, `LogIdent`, `LogAction`, `LogResult`, `LogRemarks`) VALUES (NOW(), :ip, :geo, :ua, :user, :action, :result, :remarks)");
            $stmt->bindParam('ip', $ip);
            $stmt->bindParam('geo', $geo);
            $stmt->bindParam('ua', $ua);
            $stmt->bindParam('user', $user);
            $stmt->bindParam('action', $action);
            $stmt->bindParam('result', $result);
            $stmt->bindParam('remarks', $remarks);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
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
        Common::niceException($pretext);
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
        require_once(PTP . 'class.ux.php');
        echo UX::grabPage('dev/error', array('pretext' => $pretext), true);
        exit();
    }

    /**
     * Loads news items
     *
     * @package Phoenix
     * @version 21220
     */
    public function fetchNews($start = 0, $for = 5) {
        try {
            $stmt = Data::prepare("SELECT * FROM `news` ORDER BY `NewsCTS` DESC LIMIT :start,:for");
            $stmt->bindParam('for', $for, PDO::PARAM_INT);
            $stmt->bindParam('start', $start, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
    }

    /**
     * Relative datetime strings from Zachstronaut
     */
    static public function relativeTime($ptime) {
        $etime = time() - $ptime - 8*3600;
        
        if ($etime < 1) {
            return 'just now';
        }
        
        $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
                    );
        
        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }

    /**
     * Strips HTML and performs nl2br on a string. Allows <strong>, <em> and <blockquote>
     */
    static public function cleanse($str) {
        $str = strip_tags($str, "<strong><em><blockquote>");
        return nl2br($str);
    }

    /**
     * Returns current week
     */
    static public function getCurrentWeek() {
        if (time() < strtotime(WEEK_2)) {
            return '1';
        } elseif (time() < strtotime(WEEK_3)) {
            return '2';
        } elseif (time() < strtotime(WEEK_4)) {
            return '3';
        } else {
            return '4';
        }
    }
}

?>