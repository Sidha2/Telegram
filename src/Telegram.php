<?php
/*******************************************************************/
/*                 			TELEGRAM CLASS         	     	       */
/*      									  		        	   */
/*******************************************************************/

namespace Bedri\Telegram;

class Telegram{
	
	private $token;
	
	
	public function __construct($token){
		$this->token = $token;
	}
	
	/********************************************************************************************** 
	*	
	*		S E N D     M E S S A G E
	*
	************************************************************************************************/
	
	
	/*
	*	Odosle spravu text/obrazok atd.
	*	
		method : 	sendPhoto, sendMessage, sendAudio, sendDocument, sendVideo, 
	*				sendAnimation, sendVoice, sendVideoNote, sendMediaGroup, 
	*				sendLocation, sendVenue, sendContact, sendPoll, sendChatAction
	*
	*/
	
	
	private function send($method, $parameters){
	
		$url = "https://api.telegram.org/". $this->token . "/" . $method;
	
		if (!$curld = curl_init()) {
			exit;
		}
		curl_setopt($curld, CURLOPT_POST, true);
		curl_setopt($curld, CURLOPT_POSTFIELDS, $parameters);
		curl_setopt($curld, CURLOPT_URL, $url);
		curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($curld);
		curl_close($curld);
		return $output;
		
	}
	
	/*
	*	Odosle textovu spravu, 
			- posledny parameter je klavesnica, ak nie je zadany posle sa iba text
	*
	* @param  $user_id, $text, $keyboard
	* @return 
	*/
	
	public function sendMessage($user_id, $text){
		
		$arg_list = func_get_args();

		$parameters =
		array(
			
			'chat_id' => $user_id,
			'text' => $text,
			'parse_mode' => 'html',
			'no_webpage' => '1' # nejak nefunguje zrusenie nahladu webu
			#'disable_web_page_preview' => 1
			
		);
			
		if(func_num_args() > 2){
			
			$encodedKeyboard = json_encode($arg_list[2]);	//cislovanie ide od 0 tak 2 je 3. v poradi
			$parameters['reply_markup'] = $encodedKeyboard;	//pridam do pola parameter
		}
		
		$result = $this->send('sendMessage', $parameters);

		
		$result = json_decode( $result, TRUE );
		return $result;
		
	}
	
	

	/** 
	* Odosle spravu
		- posledny parameter je klavesnica, ak nie je zadany posle sa iba text
	*
	* @param  $user_id, $photo, $caption, $keyboard
	* @return message_id 
	*/
		
	public function sendPhoto($user_id, $photo, $caption){
		
		$arg_list = func_get_args();
		
		$parameters =
			array(
				'chat_id' => $user_id,
				'photo' => $photo,
				'caption' => $caption,
				'parse_mode' => 'html'
			);
			
		if(func_num_args() > 3){
			
			$encodedKeyboard = json_encode($arg_list[3]);	//cislovanie ide od 0 tak 2 je 3. v poradi
			$parameters['reply_markup'] = $encodedKeyboard;	//pridam do pola parameter
		}
		
		$result = $this->send('sendPhoto', $parameters);
		$result = json_decode( $result, TRUE );
		
		return $result['result']['message_id'];
	}
	
	

	/** 
	* Zmaze spravu
	*
	* @param  chat_id, message_id
	* @return 1 (true)
	*/
		
	function deleteMessage($chat_id, $message_id)
	{
	
		$parameters = [
			'chat_id' => $chat_id,
			'message_id' => $message_id
		];
	
		$result = $this->send('deleteMessage', $parameters);
		$result = json_decode( $result, TRUE );
		
		return $result['result'];
	}
	
	
	/** 
	* Editne txt spravu 
	*
	* @param  chat_id, message_id, keyboard
	* @return message_id
	*/
		
	function editMessage($user_id, $message_id, $text)
	{
		$arg_list = func_get_args();
		
		$parameters = [
			'message_id' => $message_id,
			'chat_id' => $user_id ,
			'text' => $text,
			'parse_mode' => 'html'
		];
	
		if(func_num_args() > 3){
			
			$encodedKeyboard = json_encode($arg_list[3]);	//cislovanie ide od 0 tak 2 je 3. v poradi
			$parameters['reply_markup'] = $encodedKeyboard;	//pridam do pola parameter
		}
		
		$result = $this->send('editMessageText', $parameters);
		$result = json_decode( $result, TRUE );
		
		return $result['result']['message_id'];
	}
	
	
	/** 
	* Editne spravu (iba text, obrazok telegram API nedovoluje)
	*
	* @param  chat_id, message_id, link na obrazok, keyboard
	* @return message_id
	*/
		
	function editPhoto($user_id, $message_id, $caption)
	{
		$arg_list = func_get_args();
		
		$parameters = [
			'message_id' => $message_id,
			'chat_id' => $user_id ,
			//'photo' => $photo,
			'caption' => $caption,
			'parse_mode' => 'html'
		];
	
		if(func_num_args() > 3){
			
			$encodedKeyboard = json_encode($arg_list[3]);	//cislovanie ide od 0 tak 2 je 3. v poradi
			$parameters['reply_markup'] = $encodedKeyboard;	//pridam do pola parameter
		}
		
		$result = $this->send('editMessageCaption', $parameters);
		$result = json_decode( $result, TRUE );
		
		return $result['result']['message_id'];
	}
	


	/********************************************************************************************** 
	*	
	*		G E T    U P D A T E S
	*	
	*	- ziskava info o kanali
	************************************************************************************************/
	
	public function getUpdates(){
		
		$result = $this->send('getUpdates', "");
		$result = json_decode( $result, TRUE );
		
		return $result['result'];
	}


	/********************************************************************************************** 
	*	
	*		M E N U   B U I L D
	*
	************************************************************************************************/
	
	
	/*
	*	Buildne riadok keyboard, riadky potom poskladas do build_menu_final
	* @param  
	* @return pole riadkov
	*/
	
	public function build_menu_line(){

		$menu_list = array();

		$numargs = func_num_args();
		$arg_list = func_get_args();

		for ($i = 0; $i < $numargs; $i++) {

			// kazdy druhy argument inak kazdy neparny 1,3,5
			if((($i % 2) == 0)){
				$text = $arg_list[$i];
			}else{
				array_push($menu_list, array('text' => $text, 'callback_data' => $arg_list[$i]));

			}
		}
		return $menu_list = array($menu_list);
	}
	
	
	/*
	*	Buildne finalne menu (keyboard) ktore das ako "parameter" do sendMessage / sendPhoto ...
	* @param  polia riadkov
	* @return array final menu
	*/
	
	function build_menu_final(){

		$menu_list['inline_keyboard'] = array();

		$numargs = func_num_args();
		$arg_list = func_get_args();

		for ($i = 0; $i < $numargs; $i++) {
			foreach($arg_list[$i] as $value){
				array_push($menu_list['inline_keyboard'], $value);
			}


		}
		return $menu_list;
    }
	
}


?>