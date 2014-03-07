<?php if (!defined('APPLICATION')) exit();

/**
 * TODO: A brief description of the controller.
 *
 * @since 0.1
 * @package Shoutbox 
 */
class ShoutboxController extends Gdn_Controller {

	public $Uses = array('ShoutModel', 'UserModel');

	public function Get($LastEventID = 0) {
		$Session = GDN::Session();

		//Error handling as per sse standard. no further requests from the client
		if(!$Session->CheckPermission('Shoutbox.View')) {
			header("HTTP/1.1 404 Not Found");
			return;
		}

		@set_time_limit(0);
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		//Allow cross-origin access
		if(C('Shoutbox.SSE.CORS', true)) {
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Credentials: true');
		}

		//Allow chunked encoding
		if(C('Shoutbox.SSE.ChunkedEncoding', false))
			header('Transfer-encoding: chunked');

		//Deactivate compression for this endpoint only
		if(function_exists('apache_setenv'))
			@apache_setenv('no-gzip',1);
		@ini_set('zlib.output_compression',0);
		@ini_set('implicit_flush',1);

		//Stop all levels of output buffering
		for($i = 0; $i < ob_get_level(); ++$i){
			ob_end_flush();
		}
		ob_implicit_flush(1);

		//Loop parameters
		$SLEEP_TIME = C('Shoutbox.SSE.SleepTime', 3); //seconds
		$EXEC_LIMIT = C('Shoutbox.SSE.ExecLimit', 60); //seconds
		$RECONNECT = C('Shoutbox.SSE.ClientReconnect', 5) * 1000; //miliseconds
		$KEEP_ALIVE_TIME = C('Shoutbox.SSE.KeepAliveTime', 30); //seconds

		//Got a an event id from the browser.
		$init = true;
		if(isset($_SERVER["HTTP_LAST_EVENT_ID"]) && $_SERVER["HTTP_LAST_EVENT_ID"] > $LastEventID) {
			$LastEventID = $_SERVER["HTTP_LAST_EVENT_ID"];
			$init = false;
		}

		$start = time();
		printf("retry: %s\n", $RECONNECT);

		while(true) {
			//No updates needed, send a comment to keep the connection alive.
			if(!((time() - $start) % $KEEP_ALIVE_TIME))
				printf(": %s\n\n", sha1(mt_rand()));

			if(!$this->ShoutModel->IsLatest($LastEventID)) {
				foreach($this->ShoutModel->GetRecentByLastEventID($LastEventID) as $msg) {
					printf("id: %d\n", $msg["EventID"]);
					printf("event: %s\n", $init? "init": "update");
					$msg["UserName"] = $this->UserModel->GetID($msg["UserID"])->Name;
					$msg["NameColor"] = $this->ShoutModel->GetColor($msg["UserName"]);
					if ($msg["MessageTo"] != 0)
						$msg["MessageToName"] = $this->UserModel->GetID($msg["MessageTo"])->Name;
					$msg["Content"] = $this->ShoutModel->PrepareText($msg["Content"]);
					printf("data: %s\n\n", json_encode($msg));
					$LastEventID = $msg["EventID"];
				}
			}

			@ob_flush();
			@flush();
				
			//Exit script if execution time limit is exceeded
			if($EXEC_LIMIT != 0 && (time() - $start) > $EXEC_LIMIT)
				break;
			sleep($SLEEP_TIME);
		}
	}

	public function Post() {
		$Session = GDN::Session();

		if(!$Session->CheckPermission('Shoutbox.Post'))
			return;
	}

	public function Edit($EventID) {
		$Session = GDN::Session();

		if(!$Session->CheckPermission('Shoutbox.Edit') || !is_numeric($EventID))
			return;

		$shouts = new ShoutModel();

		$shout = $shouts->GetShout($EventID);
		$shouts->Edit($shout->EventID, null);
	}

	public function Delete() {
		$Session = GDN::Session();

		if(!$Session->CheckPermission('Shoutbox.Delete'))
			return;
	}
}
