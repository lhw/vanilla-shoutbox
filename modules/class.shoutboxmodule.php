<?php if(!defined('APPLICATION')) exit();

class ShoutboxModule extends Gdn_Module {
	public function __construct($Sender = '') {
		parent::__construct($Sender);
	}

	public function AssetTarget() {
		return 'Content';
	}

	public function ToString() {
		$Session = Gdn::Session();
		if(!$Session->CheckPermission('Shoutbox.View')) {
			return "";
		}

		$String = '';

		ob_start();
		require_once(PATH_APPLICATIONS.DS.'shoutbox'.DS.'views'.DS.'shoutbox.php');
		$String = ob_get_contents();
		@ob_end_clean();

		return $String;
	}
}
