<?php
/*
*    A simple betting bot
*    Verison: 0.1
*
*    Install:
*    0. Download and Install JAXL packege from the bloody "Google" here: http://code.google.com/p/jaxl/
*    1. Put this file into /usr/share/php/jaxl/app/betbot
*    2. run jaxl roombot.php
*
*    How it works:
*    0.The bot is listening in a chat room for matching 'I bet [something]'
*    1.It saves this string into a file and notify the author for that action
*    2.!show shows all active bets
*
*    Future releases:
*    0.The user should be able to read all bets and to take a part with '+' or '-'
*    1. Save the result into DB/WebPage/Wiki
*    2. We may use fakeDB or event NoSQL for saving the result
*    3. !beer - should bring me a beer :)
*    
*    Author: @bogomil <shopov@kolabsys.com>
*    	
*    Contributor(s):
*
*    LICENSE:
*      DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
*      Version 2, December 2004
*
*      Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>
*
*      Everyone is permitted to copy and distribute verbatim or modified
*      copies of this license document, and changing it is allowed as long
*      as the name is changed.
*
*      DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
*      TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
*
*      0. You just DO WHAT THE FUCK YOU WANT TO.
*
*/


// initialize Jaxl instance
    $jaxl = new JAXL(array(  
            'user'    =>    '',  //jabber username just username
            'pass'    =>    '',  //plaintext password 
            'host'    =>    '', //jabber host  
            'domain' =>    '', //jabber domain (or just leave it empty) 
            'port'    =>    5222 //port (5222 is default)
    ));  

// Include the required components
    jaxl_require(array(  
        'JAXL0085',  
        'JAXL0092',  
        'JAXL0203' ,
	'JAXL0045'
    ), $jaxl);  

//set some conf details
define('ROOM',"test@conference.jabber.minus273.org/Bet waiter");
define('PATH',"/usr/share/php/jaxl/app/echobot/bets.txt");


class betbot {
	
	
                function startStream()
		{
			global $jaxl;
			$jaxl->startStream();	
		}  
                function doAuth($mechanism) {
			global $jaxl;
			switch(TRUE) {
				case in_array("ANONYMOUS",$mechanism):
					$jaxl->auth("ANONYMOUS");
					break;
				case in_array("DIGEST-MD5",$mechanism):
					$jaxl->auth("DIGEST-MD5");
					break;
				case in_array("PLAIN",$mechanism):
					$jaxl->auth("PLAIN");
					break;
				default:
					die("No prefered auth method exists...");
					break;
			}
		}  
                function postAuth()
		{
			global $jaxl;
			//Join the Conference room
			$jaxl->JAXL0045('joinRoom', $jaxl->jid, ROOM, 0, 'seconds');
		}
		
                function getMessage($payloads) {
			global $jaxl;
			foreach($payloads as $payload) {
				if($payload['offline'] != JAXL0203::$ns
				&& (!$payload['chatState'] || $payload['chatState'] = 'active')
				) {
					if(strlen($payload['body']) > 0) {
						
						//Read the message and react
						if(preg_match('/I bet/', $payload['body']))
						{
							$this->betEngine($payload['body']);
							$from = explode('/',$payload['from']);
							$jaxl->sendMessage($payload['from'], $from[1]." you are on",'','broadcast');
						}
						
						if(preg_match('/!show/',$payload['body']))
						{
							$f=file('/usr/share/php/jaxl/app/echobot/bets.txt');
							for($i=0;$i<count($f);$i++)
							{
								$jaxl->sendMessage($payload['from'], $i.":".$f[$i],'','broadcast');
							}
						}
					}
				}
			}
		}
		
		
		function betEngine($sentence)
		{
			$fp = fopen(PATH, "a+");
			fwrite($fp, $sentence.'
');
			fclose($fp);
			
		}
                function getPresence($payloads) {}  
        }  
        $betbot = new betbot();  
  
        // Add callbacks on various xmpp events (#4)  
        JAXLPlugin::add('jaxl_post_connect', array($betbot, 'startStream'));  
        JAXLPlugin::add('jaxl_get_auth_mech', array($betbot, 'doAuth'));  
        JAXLPlugin::add('jaxl_post_auth', array($betbot, 'postAuth'));  
        JAXLPlugin::add('jaxl_get_message', array($betbot, 'getMessage'));  
        JAXLPlugin::add('jaxl_get_presence', array($betbot, 'getPresence'));  
?>