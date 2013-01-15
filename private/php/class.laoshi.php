<?php

/**
 * Laoshi provides all staff portal major functions.
 * Must be initialized.
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     30104
 * @package     Phoenix
 */
class Laoshi {

    public $sso = null;
    public $myPerms = null;

    /**
     * Constructor class gets staff type
     */
    public function __construct($objId) {

        $this->sso = ACL::getSsoObject($objId);
        $this->sso['ObjType'] = ACL::getSsoType($this->sso['ObjType']);
        $this->staff = $this->getStaff();
        $this->myPerms = ACL::getPermsByType($this->sso['ObjType']['TypeID']);

        return true;

    }

    public function fetchDefaultPage() {
        switch($this->sso['ObjType']['TypeName']) {
            case 'secretary':
            case 'director':
            case 'administrator':
                return 'staff/dash_admin';
            break;
            default:
                return 'staff/dash_no_acl';
            break;
        }
    }

    public function fetchNavPage() {
        switch($this->sso['ObjType']['TypeName']) {
            case 'administrator':
                return 'common/nav_staff_secretary';
            break;
            case 'director':
                return 'common/nav_staff_secretary';
            break;
            case 'secretary':
                return 'common/nav_staff_secretary';
            break;
            default:
                return 'common/nav_public_staff_login';
            break;
        }
    }

    public function perms() {
        $args = func_get_args();
        foreach ($args as $perm) {
            if (!array_key_exists($perm, $this->myPerms)) {
                echo UX::grabPage('dev/permissions');
                exit();
            }
        }
    }




    private function getStaff() {

        $stmt = Data::prepare('SELECT * FROM `staff` WHERE `ObjID` = :objid LIMIT 1');
        $stmt->bindParam('objid', $this->sso['ObjID']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

}