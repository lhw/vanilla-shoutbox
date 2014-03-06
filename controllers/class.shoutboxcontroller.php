<?php if (!defined('APPLICATION')) exit();

/**
 * TODO: A brief description of the controller.
 *
 * @since 0.1
 * @package Shoutbox 
 */
class ShoutboxController extends Gdn_Controller {

	public $Uses = array('UserModel', 'RoleModel', 'Database');

   /**
    * @since 0.1
    * @access public
    */
   public function Initialize() {
/*
  		$this->DeliveryMethod(DELIVERY_METHOD_JSON);
		$this->DeliveryType(DELIVERY_TYPE_DATA);
		header('Content-Type: application/json; charset=utf-8');
*/
      parent::Initialize();
   }

	public function Get($LastEventId = 0) {
		$Session = GDN::Session();

		if(!$Session->CheckPermission('Shoutbox.View'))
			return;

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
		for($i = 0; $i < ob_get_level(); $i++){
			ob_end_flush();
		}
		ob_implicit_flush(1);

		//Loop parameters
		$SLEEP_TIME = C('Shoutbox.SSE.SleepTime', 3); //microseconds
		$EXEC_LIMIT = C('Shoutbox.SSE.ExecLimit', 60); //seconds
		$RECONNECT = C('Shoutbox.SSE.ClientReconnect', 5) * 1000; //miliseconds
		$KEEP_ALIVE_TIME = C('Shoutbox.SSE.KeepAliveTime', 30); //seconds

		$start = time();
		printf("retry: %s\n", $RECONNECT);
		$i = $LastEventId;

		while(true) {
			//No updates needed, send a comment to keep the connection alive.
			if(!((time() - $start) % $KEEP_ALIVE_TIME))
				printf(": %s\n\n", sha1(mt_rand()));

			//TODO: output data here if there is new
			printf("id: %d\n", ++$i);
			printf("data: %d\n\n", time());
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
