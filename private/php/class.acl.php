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
    
}