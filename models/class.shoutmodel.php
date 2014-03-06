<?php if(!defined('APPLICATION')) exit();

class ShoutModel extends Gdn_Model {
	public function GetRecent($Limit = 50) {
		$Session = GDN::Session();

		$shouts = $this->SQL
			->Select('*')
			->From('Shoutbox')
			->BeginWhereGroup()
				->OrWhere('ReplyTo', '')
				->OrWhere('ReplyTo', $Session->UserID)
			->EndWhereGroup()
			->OrderBy('EventID', 'desc')
			->Limit($Limit)
			->Get()
			->ResultArray();
		return $shouts;
	}

	public function GetShout($EventID) {
		$Session = GDN::Session();

		$shout = $this->SQL
			->Select('*')
			->From('Shoutbox')
			->BeginWhereGroup()
				->OrWhere('ReplyTo', '')
				->OrWhere('ReplyTo', $Session->UserID)
				->AndWhere('EventID', $EventID)
			->EndWhereGroup()
			->Get()
			->FirstRow();
		return $shout;
	}

	public function IsLatest($EventID) {
		$Session = GDN::Session();

		$lastShout = $this->SQL
			->Select('EventID')
			->From('Shoutbox')
			->BeginWhereGroup()
				->OrWhere('ReplyTo', '')
				->OrWhere('ReplyTo', $Session->UserID)
			->EndWhereGroup()
			->OrderBy('EventID', 'desc')
			->Limit(1)
			->Get()
			->FirstRow();

		return $lastShout->EventID == $EventID;
	}

	public function AddShout($Content, $ReplyTo = -1) {
		$Session = GDN::Session();

		$values = array(
			'UserID' => $Session->UserID,
			'Content' => $Content,
			'Timestamp' => time()
		);
		if($ReplyTo != -1) $values['ReplyTo'] = $ReplyTo;

		$this->SQL->Insert('Shoutbox', $values);
	}

	public function DeleteShout($EventID) {
		$this->SQL->Delete('Shoutbox', array('EventID' => $EventID));
	}

	public function EditShout($EventID, $Content) {
		$this->SQL
			->Update('Shoutbox')
			->Set('Content', $Content)
			->Where('EventID', $EventID)
			->Put();
	}

	public function PrepareText($text) {
		$searchstring = array("\\\\", "\\n", "\\r", "\\Z", "\\'", '\\"');
		$replacestring = array("\\", "\n", "\r", "\x1a", "'", '"');
		$urlregex = '/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/i';
		$urlreplacement = "<a href='\0' target='blank'>\0</a>";

		$text = str_replace($searchstring, $replacestring, $text);
		$text = htmlspecialchars($text, null, 'UTF-8');
		$text = preg_replace($urlregex, $urlreplacement, $text);

		return $text;
	}
}
