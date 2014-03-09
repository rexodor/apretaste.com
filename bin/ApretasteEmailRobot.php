<?php
/**
 * Apretaste!com Email Robot
 *
 * version 1.5
 */
class ApretasteEmailRobot {
	function __construct($autostart = true, $verbose = false, $debug = false){
		$this->verbose = $verbose;
		$this->debug = $debug;
		
		Apretaste::loadSetup();
		
		$this->commands = Apretaste::$config["commands"];
		$clase = $this;
		
		$this->callback = function ($headers, $textBody = false, $htmlBody = false, $images = false, $otherstuff = false, $account = null) use($clase){
			
			$rawCommand = array(
					'headers' => $headers,
					'textBody' => $textBody,
					'htmlBody' => $htmlBody,
					'images' => $images,
					'otherstuff' => $otherstuff
			);
			
			$command = $clase->_prepareCommand($rawCommand);
			if ($command['operation']) {
				
				$clase->log("Performing a " . $command['operation'] . " operation");
				
				$cmdpath = "../cmds/{$command['operation']}.php";
				$answer = array();
				
				if (file_exists($cmdpath)) {
					include_once $cmdpath;
					$user_func = 'cmd_' . $command['operation'];
					$params = $command['parameters'];
					array_unshift($params, $clase);
					$command['parameters'] = $params;
					
					$answer = call_user_func_array($user_func, $command['parameters']);
					if (! isset($answer['command']))
						$answer['command'] = $command['operation'];
					if (! isset($answer['from']))
						$answer['from'] = $params[1];
					if (! isset($answer['answer_type']))
						$answer['answer_type'] = $command['operation'];
				}
			} else {
				echo $clase->verbose ? "retrieving documentation\n" : "";
				$answer = array(
						'answer_type' => $command['parameters'][0]
				);
			}
			
			$msg_id = $clase->logger->log($rawCommand, $answer);
			
			echo $clase->verbose ? "sending a " . $answer['answer_type'] . " type message\n" : "";
			if (is_null($account)) {
				foreach ( $clase->config_answer as $k => $v ) {
					$account = $k;
					break;
				}
			}
			$answerMail = new ApretasteAnswerEmail($config = $clase->config_answer[$account], $to = $rawCommand['headers']->fromaddress, $servers = $clase->smtp_servers, $data = $answer, $send = true, $verbose = $clase->verbose, $debug = $clase->debug, $msg_id);
		};
		
		$this->logger = new ApretasteEmailLogger($this->verbose, $this->debug);
		
		// Loading configuration
		
		$thesource = new DOMImplementation();
		$configuration = $thesource->createDocument();
		$configuration->load("../etc/configuration.xml");
		$configuration->validate();
		
		// SMTP servers
		$smtps = $configuration->documentElement->getElementsByTagName('smtp');
		$this->smtp_servers = array();
		for($i = 0; $i < $smtps->length; $i ++)
			if (mb_strtolower($smtps->item($i)->getAttribute('auth')) == 'false' || mb_strtolower($smtps->item($i)->getAttribute('auth')) == 'no')
				$this->smtp_servers[$smtps->item($i)->getAttribute('address')] = array(
						'host' => $smtps->item($i)->getAttribute('host'),
						'port' => $smtps->item($i)->getAttribute('port'),
						'auth' => false,
						'username' => "",
						'password' => ""
				);
			else
				$this->smtp_servers[$smtps->item($i)->getAttribute('address')] = array(
						'host' => $smtps->item($i)->getAttribute('host'),
						'port' => $smtps->item($i)->getAttribute('port'),
						'auth' => true,
						'username' => $smtps->item($i)->getAttribute('username'),
						'password' => $smtps->item($i)->getAttribute('password')
				);
			
			// IMAP servers
		$imaps = $configuration->documentElement->getElementsByTagName('imap');
		$this->imap_servers = array();
		for($i = 0; $i < $imaps->length; $i ++) {
			$this->imap_servers[$imaps->item($i)->getAttribute('address')] = array(
					'mailbox' => $imaps->item($i)->getAttribute('mailbox'),
					'username' => $imaps->item($i)->getAttribute('username'),
					'password' => $imaps->item($i)->getAttribute('password')
			);
		}
		
		// Answers configuration
		$configNodes = $configuration->documentElement->getElementsByTagName('config');
		$this->configs = array();
		
		for($i = 0; $i < $configNodes->length; $i ++) {
			if (! isset($this->config_answer[$configNodes->item($i)->getAttribute('for')]))
				$this->config_answer[$configNodes->item($i)->getAttribute('for')] = array();
			$this->config_answer[$configNodes->item($i)->getAttribute('for')][$configNodes->item($i)->getAttribute('name')] = $configNodes->item($i)->getAttribute('value');
		}
		
		if ($autostart)
			$this->start();
	}
	function start(){
		// Scan for new messages
		$this->collect = new ApretasteEmailCollector($this->imap_servers, $verbose = $this->verbose, $debug = $this->debug);
		$this->collect->get($callback = $this->callback);
	}
	function _prepareCommand($anounce){
		$command = explode(' ', urldecode($anounce['headers']->subject), 2);
		$command_name = mb_strtolower(trim($command[0]));
		
		if (count($command) == 2)
			$argument = trim($command[1]);
		else
			$argument = false;
		
		$from = $anounce['headers']->from[0]->mailbox . '@' . $anounce['headers']->from[0]->host;
		$body = $anounce['textBody'] ? $anounce['textBody'] : $anounce['htmlBody'];
		
		if (array_key_exists($command_name, $this->commands)) {
			$actual_command = $this->commands[$command_name];
			
			if ($this->commands[$command_name] != "exclusion") {
				Apretaste::incorporate($from);
			}
			
			$parameters = array(
					$from,
					$argument,
					$body,
					$anounce['images']
			);
		} else {
			$actual_command = $this->commands[Apretaste::$config["default_command"]];
			$parameters = array(
					$from,
					implode(' ', $command),
					$body,
					$anounce['images']
			);
		}
		
		return array(
				'operation' => $actual_command,
				'parameters' => $parameters
		);
	}
	
	/**
	 * Output log messages
	 *
	 * @param string $message
	 * @param string $level
	 */
	function log($message, $level = 'INFO'){
		echo $this->verbose ? '[' . $level . '] ' . date("h:i:s") . "-" . $message . "\n" : '';
	}
}
