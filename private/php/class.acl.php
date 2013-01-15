<?php

/**
 * <p>The Security class provides security and access control services
 * to the entire package. Requires Common class.</p>
 * <p>ACL and Security class as of 04JAN13 require SSO access.</p>
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     30104
 * @package     Phoenix
 */
class ACL extends Security {

    /**
     * Begins ACL class and handles sessions
     *
     * @access      public
     */
    static public function initiateSecurity() {
    
        // Begin session
        session_name('SummerSession');
        session_start();
        
        return true;
        
    }
    
    /**
     * Generates a session key based on UA and IP and logs the action
     *
     */
    static public function genSession($id) {
        $stmt = Data::prepare('SELECT ObjHash FROM `sso_objects` WHERE ObjID = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Common::logAction('backend.subroutine.acl', 'success', 'SSOID='.$id, 'via ACL::generateSession');
        $_SESSION['SSOID'] = $id;
        $_SESSION['AuthCheck'] = hash("sha256", $result['ObjHash'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        
        // Should always return true
        return true;
    }
    
    /**
     * Looks up SSO object ID by email and (optionally) object type. Success returns SSOID.
     *
     * @package		Phoenix
     * @version		20820
     */
    static public function checkSsoEmail($email, $portalType = 'public') {
        // Get email
        $stmt = Data::prepare('SELECT `ObjID` FROM `sso_objects` WHERE ObjEmail = :email AND ObjPortal = :type');
        $stmt->bindParam('email', $email, PDO::PARAM_STR);
        $stmt->bindParam('type', $portalType, PDO::PARAM_STR);
        $stmt->execute();
        $objResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (sizeof($objResult) > 0) {
            return $objResult[0]['ObjID'];
        } else {
            return false;
        }
    }

    /**
     * Creates an SSO object of the specified type
     * @package     Phoenix
     * @version     30104
     */
    static public function makeSsoObject($email, $password, $type = 'family', $portal = "public") {
        // Get Object Type ID
        $typeId = self::getSsoTypeByName($type);
        if (!$typeId) return false;

        // Does user already exist in specified portal?
        if (self::checkSsoEmail($email, $portal)) return false;

        try {
            // Get password hash
            $passhash = self::getHash($password);
    
            $stmt = Data::prepare('INSERT INTO sso_objects (ObjType, ObjPortal, ObjEmail, ObjHash, ObjCTS, ObjLLTS, ObjPassUpdateTS) VALUES (:type, :portal, :email, :hash, NOW(), NOW(), NOW())');
            $stmt->bindParam('email', $email, PDO::PARAM_STR);
            $stmt->bindParam('portal', $portal, PDO::PARAM_STR);
            $stmt->bindParam('hash', $passhash);
            $stmt->bindParam('type', $typeId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::niceException('Failed to create SsoObject in ACL::makeSsoObject');
        }

        return true;

    }

    /**
     * Verifies SSO authentication, generate and store session information
     *
     * @package     Phoenix
     * @version     30104
     */
    static public function checkPassword($ssoid, $plainpass) {
        // Get information based on SSOID
        $stmt = Data::prepare('SELECT * FROM sso_objects WHERE ObjID = :ssoid LIMIT 1');
        $stmt->bindParam('ssoid', $ssoid, PDO::PARAM_INT);
        $stmt->execute();
        $objData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compare password
        $passCheck = self::checkHash($plainpass, $objData['ObjHash']);

        if ($passCheck) {
            $stmt = Data::prepare('UPDATE `sso_objects` SET ObjLLTS = NOW() WHERE ObjID = :ssoid LIMIT 1');
            $stmt->bindParam('ssoid', $ssoid, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $passCheck;
    }

    /**
     * Gets SSO object
     */
    static public function getSsoObject($objId) {
        $stmt = Data::prepare('SELECT * FROM `sso_objects` WHERE `ObjID` = :objid LIMIT 1');
        $stmt->bindParam('objid', $objId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets SSO Object Type
     */
    static public function getSsoType($typeId) {
        $stmt = Data::prepare('SELECT * FROM `sso_types` WHERE `TypeID` = :typeid LIMIT 1');
        $stmt->bindParam('typeid', $typeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Logs a user out based on their session
     * @package     Phoenix
     */
    static public function logout() {
        Common::logAction('http.logout.acl', 'success', 'SSOID='.$_SESSION['SSOID'], 'via ACL class');
        setcookie(session_name(), '', time()-(3600*24));
        return session_destroy();
    }

    /**
     * Performs an SSO login
     * @package     Phoenix
     */
    static public function login($email, $plainpass, $portal = 'public') {
        $ssoid = self::checkSsoEmail($email, $portal);
 
        // If email is invalid
        if (!$ssoid) return null;
        
        // Check password
        if (!self::checkPassword($ssoid, $plainpass)) return false;
        else return $ssoid;

    }
    
    
    /**
     * Verifies an active SSO session information including IP and UA validation
     */
    static public function checkLogin($portal = 'families') {
        $stmt = Data::prepare('SELECT ObjHash FROM `sso_objects` WHERE ObjID = :id AND ObjPortal = :portal');
        $stmt->bindValue('portal', $portal);
        $stmt->bindValue('id', $_SESSION['SSOID']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compare has with session value
        if ($_SESSION['AuthCheck'] == hash("sha256", $result['ObjHash'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get array of permissions allowed determined by the object type
     */
    static public function getPermsByType($typeId) {
        $stmt = Data::prepare('SELECT `acl_perms`.* FROM `acl`, `acl_perms` WHERE `acl_perms`.`PermID` = `acl`.`PermID` AND `acl`.`TypeID` = :typeid');
        $stmt->bindParam('typeid', $typeId);
        $stmt->execute();
        $permsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $permsAllow = array();
        foreach($permsRaw as $perm) {
            $permsAllow[$perm['PermID']] = $perm['PermName'];
        }
        return $permsAllow;
    }

    /* =============== INTERNAL PRIVATE FUNCTIONS ============== */

    static private function getSsoTypeByName($name) {
        $stmt = Data::prepare('SELECT TypeID FROM sso_types WHERE TypeName = :name');
        $stmt->bindParam('name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);

        if (sizeof($return) > 0) return $return['TypeID'];
        else return false;
    }
    
}