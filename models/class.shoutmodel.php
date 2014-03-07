<?php if(!defined('APPLICATION')) exit();

class ShoutModel extends Gdn_Model {
	public function GetRecent($Limit = 50) {
		$Session = GDN::Session();

		$shouts = $this->SQL
			->Select('*')
			->From('Shoutbox')
			->BeginWhereGroup()
				->Where('MessageTo', 0)
				->OrWhere('MessageTo', $Session->UserID)
			->EndWhereGroup()
			->OrderBy('EventID', 'desc')
			->Limit($Limit)
			->Get()
			->ResultArray();
		return $shouts;
	}

	public function GetRecentByLastEventID($EventID, $Limit = 50) {
		if(!is_numeric($EventID)) return false;
		$Session = GDN::Session();

		$shouts = $this->SQL
			->Select('*')
			->From('Shoutbox')
			->Where('EventID >', $EventID)
			->AndOp()
			->BeginWhereGroup()
				->Where('MessageTo', 0)
				->OrWhere('MessageTo', $Session->UserID)
			->EndWhereGroup()
			->OrderBy('EventID', 'desc')
			->Limit($Limit)
			->Get()
			->ResultArray();

		return $shouts;
	}

	public function GetShout($EventID) {
		if(!is_numeric($EventID)) return false;
		$Session = GDN::Session();

		$shout = $this->SQL
			->Select('*')
			->From('Shoutbox')
			->Where('EventID', $EventID)
			->AndOp()
			->BeginWhereGroup()
				->Where('MessageTo', 0)
				->OrWhere('MessageTo', $Session->UserID)
			->EndWhereGroup()
			->Get()
			->FirstRow();
		return $shout;
	}

	public function IsLatest($EventID) {
		if(!is_numeric($EventID)) return false;
		$Session = GDN::Session();

		$lastShout = $this->SQL
			->Select('EventID')
			->From('Shoutbox')
			->BeginWhereGroup()
				->Where('MessageTo', 0)
				->OrWhere('MessageTo', $Session->UserID)
			->EndWhereGroup()
			->OrderBy('EventID', 'desc')
			->Limit(1)
			->Get()
			->FirstRow();

		return $lastShout->EventID <= $EventID;
	}

	public function AddShout($Content, $MessageTo = 0) {
		$Session = GDN::Session();

		$this->SQL->Insert('Shoutbox', array(
			'UserID' => $Session->UserID,
			'MessageTo' => $MessageTo,
			'Content' => $Content,
			'Timestamp' => time()
		));
		return true;
	}

	public function AddShouts($values) {
		$this->SQL->Insert('Shoutbox', $values);
		return true;
	}

	public function DeleteShout($EventID) {
		if (!is_numeric($EventID)) return false;
		$this->SQL->Delete('Shoutbox', array('EventID' => $EventID));
		return true;
	}

	public function EditShout($EventID, $Content) {
		if (!is_numeric($EventID)) return false;

		$this->SQL
			->Update('Shoutbox')
			->Set('Content', $Content)
			->Where('EventID', $EventID)
			->Put();
		return true;
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

	public function GetColor($String) {
		$ColorArray = C("Shoutbox.Client.Colors");
		if (count($ColorArray) < 1) return "#000000";

		$sum = 0;
		foreach(preg_split('//', $String, -1, PREG_SPLIT_NO_EMPTY) as $chr) {
			$sum += ord($chr);
		}
		return $ColorArray[$sum % count($ColorArray)];
	}
}
