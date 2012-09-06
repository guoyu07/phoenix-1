<?php

//require_once('class.mandrill.php');

define('MANDRILL_KEY',  'd4fd5695-5805-43fa-8fd7-2a2f0c30fa9b');

/**
 * The Mailer class provides Mandrill app integration for 
 * transactional email services.
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20903
 * @package     Phoenix
 */
class Mailer {
    
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
     * Mail function for Mandrill app integration
     *
     * @param mixed $email  Email address (To) field
     * @param str   $sujb
     */
    public function send($to, $subj, $body, $fromText = "CIS Summer Programme", $fromAddr = "noreply@summer.cis.edu.hk") {
        
        $args = array(
            'key' => MANDRILL_KEY,
            'message' => array(
                "html" => nl2br($body),
                "text" => $body,
                "from_email" => $fromAddr,
                "from_name" => $fromText,
                "subject" => $subj,
                "to" => array($to),
                "track_opens" => true,
                "track_clicks" => true,
                "auto_text" => true
            )   
        );
        
        
        if (MAILER_SENDMAIL) {
            $curl = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
            
            
            $response = curl_exec($curl);
            curl_close($curl);
        }
        
        // Write information to database
        try {
            $stmt = Data::prepare('INSERT INTO `mandrill` (`MailTS`, `MailRequest`, `MailResponse`) VALUES (NOW(), :request, :response)');
            $stmt->bindParam('request', json_encode($args));
            $stmt->bindParam('response', $response);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
        
    }
    
    
}

?>