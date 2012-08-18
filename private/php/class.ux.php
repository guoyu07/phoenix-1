<?php

/*
* The user experience class contains features and methods
* to drive page HTML and other source code...
*
* @author      Yectep Studios <info@yectep.hk>
* @version     20703
* @package     Phoenix
*
*/
class UX {
    
    // Environment variables
    private	$env;			// Environment variable
    private $env_reset;		// For development use
    
    public	$head;			// Basically the output buffer
    
	/*
	* Class does not need initialization. Constructor and destructor disabled.
	*/
    protected function __construct() {      }
    
    /*
    * Class doesn't have a construct, no destruct required.
    */
    protected function  __destruct() {      }
    
	/*
	* Gets a snippet of HTML with replacements
	*
	* @param string $snippet The snippet name, stored in private/snippets/: based on environment
	* @param string $vars Variable array to be replaced accordingly by SMARTY-style {} braces.
	* @param bool $nice I forgot what this does...
	* @note All logic is done via the PHP page. Replce lang strings with {}, no logic! (Easier for i89n)
	* @return string|false Returns false if the snippet file is not found and don't force nice errors
	*/
    public function grabPage($snippet, $vars = array(), $nice = true) {
    
        $headers = UX::getallheaders();
    	if (array_key_exists('X-Yectep-Dev', $headers)) {
    	    
            // If a development page doesn't exist, make sure we headify it to notify the dev
            // Also reset the environment variable
            
            if (!file_exists(PTP."snippets/".$snippet.".dev.html")) {
                header("X-Yectep-Dev: Development file does not exist");
                die("Missing a development file: ".PTR."snippets/".$snippet.".dev.html");
            } else {
                header("X-Yectep-Dev: Using development display");
            }
    	}
    	
        
        if (file_exists(PTP."snippets/".$snippet.".cur.html")) {
            // First we get the page
            $unparsed = file_get_contents(PTP."snippets/".$snippet.".cur.html");
            
            // Replace strings as performed by the actual page code
            $keys = array();
            if (sizeof($vars) > 0) {
	            foreach(array_keys($vars) as $index=>$val) {
	                array_push($keys, "{{".$val."}}");
	            }
	        }
            
            $final = str_replace($keys, $vars, $unparsed);
            
            // Clean up any replacement variables we weren't able to properly place.
            $final = preg_replace('/{{(.*?)}}/', '', $final);
            
            return $final;
            
        } else {
            return ($nice ? "A required HTML snippet was not found." : false);
        }
        
    }
    
    /*
     * Flushes the buffer as well as the UX push buffer
     *
     */
    public function flush() {
        ob_flush();
    }
    
    /**
    * Placeholder for < PHP 5.4.0 under FastCGI
    */
    function getallheaders() 
    {
       foreach ($_SERVER as $name => $value) 
       {
           if (substr($name, 0, 5) == 'HTTP_') 
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
    
    
}

?>