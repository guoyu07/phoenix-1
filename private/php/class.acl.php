<?php

/**
 * <p>The Security class provides security and access control services
 * to the entire package. Requires Common class.</p>
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20627
 * @package     Phoenix
 */
class Security extends Common {

    /**
     * Begins ACL class and handles sessions
     *
     * @access      public
     */
    public function initiateSecurity() {
    
        // Begin session
        session_name('SummerSession');
        session_start();
        
        return true;
        
    }
    
    /**
     * Generates a session key based on UA and IP and logs the action
     *
     */
    public function generateSession($id) {
        $stmt = Data::prepare('SELECT FamilyPassword FROM `families` WHERE FamilyID = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Common::logAction('backend.subroutine.acl', 'success', 'FID='.$id, 'generate_session');
        $_SESSION['FID'] = $id;
        $_SESSION['AuthCheck'] = sha1($result['FamilyPassword'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        
        // Should always return true
        return true;
    }
    
    /**
     * Looks up whether an account exists via email
     *
     * @package		Phoenix
     * @version		20820
     */
    public function checkEmail($email) {
    	$stmt = Data::prepare('SELECT COUNT(*) FROM `families` WHERE FamilyEmail = :email');
    	$stmt->bindParam('email', $email);
    	$stmt->execute();
    	$num = $stmt->fetchColumn();
    	if ($num == 1) {
        	return true;
    	} else {
        	return false;
    	}
    }
    
    
    /**
     * Fetches user data
     *
     * @package		Phoenix
     * @version		20903
     */
    public function getUserData($email) {
    	$stmt = Data::prepare('SELECT `FamilyID`  as "FID", `FamilyEmail` as "email", `FamilyName` as "name", `FamilySalt` as "salt", `FamilyPassword` as "password", `FamilyAccountStatus` as "status", UNIX_TIMESTAMP(`FamilyCTS`) as "ts_created", UNIX_TIMESTAMP(`FamilyLATS`) as "ts_lastaction", UNIX_TIMESTAMP(`FamilyLLTS`) as "ts_lastlogin", `FamilyAddress` as "address", `FamilyCountry` as "from_hk", `FamilyPhoneHome` as "hphone", `FamilyPhoneMobile` as "mphone", `FamilyLanguage` as "language", `FamilyIC` as "comments" FROM `families` WHERE FamilyEmail = :email');
    	$stmt->bindParam('email', $email);
    	$stmt->execute();
    	return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    
    /**
     * Logs a user in
     *
     * @package		Phoenix
     * @version		20827
     */
    public function checkLogin($email, $pass) {
        $stmt = Data::prepare('SELECT FamilyID, FamilySalt, FamilyPassword, FamilyAccountStatus FROM `families` WHERE FamilyEmail = :email');
        $stmt->bindParam('email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check email
        if (sizeof($result) !== 4) {
            return false;
        } else {
            if (sha1($result['FamilySalt'].$pass) == $result['FamilyPassword']) {
                // Check account status
                if ($result['FamilyAccountStatus'] == 1) {
                    return $result['FamilyID'];
                } else {
                    // Non-activated
                    Common::logAction('http.post.login', 'failed', 'FID='.$result['FamilyID'],  'not activated');
                    return 0;
                }
            } else {
                // Wrong password
                Common::logAction('http.post.login', 'failed', 'FID='.$result['FamilyID'],  'invalid password');
                return false;
            }
        }
    }
    
    
    /**
     * Generate salt
     *
     * @package     Phoenix
     * @version     20904
     */
    public function generateSalt() {
        
        $vars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 !@#$%^&*()-=|\}{[]/?.>,<'";
        $salt = substr($vars, rand(0,(strlen($vars)-1)), 1).substr($vars, rand(0,(strlen($vars)-1)), 1).
                substr($vars, rand(0,(strlen($vars)-1)), 1).substr($vars, rand(0,(strlen($vars)-1)), 1).
                substr($vars, rand(0,(strlen($vars)-1)), 1).substr($vars, rand(0,(strlen($vars)-1)), 1).
                substr($vars, rand(0,(strlen($vars)-1)), 1).substr($vars, rand(0,(strlen($vars)-1)), 1).
                substr($vars, rand(0,(strlen($vars)-1)), 1).substr($vars, rand(0,(strlen($vars)-1)), 1);
        
        return ($salt);
        
    }
    
}