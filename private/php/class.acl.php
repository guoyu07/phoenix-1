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
        
        Common::logAction('backend.subroutine.acl', 'generate_session', 'ID='.$id.' [ok]');
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
     * Logs a user in
     *
     * @package		Phoenix
     * @version		20827
     */
    public function checkLogin($email, $pass) {
        $stmt = Data::prepare('SELECT FamilyID, FamilySalt, FamilyPassword, FamilyCTS, FamilyAccountStatus FROM `families` WHERE FamilyEmail = :email');
        $stmt->bindParam('email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check email
        if (sizeof($result) !== 5) {
            return false;
        } else {
            if (sha1($pass.$result['FamilySalt'].$result['FamilyCTS']) == $result['FamilyPassword']) {
                // Check account status
                if ($result['FamilyAccountStatus'] == 1) {
                    return $result['FamilyID'];
                } else {
                    // Non-activated
                    return 0;
                }
            } else {
                // Wrong password
                echo sha1($pass.$result['FamilySalt'].$result['FamilyCTS'])." ".$result['FamilyPassword'];
                exit();
                return false;
            }
        }
    }
    
}