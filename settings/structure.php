<?php if (!defined('APPLICATION')) exit(); // Make sure this file can't get accessed directly
// Use this file to do any database changes for your application.

if (!isset($Drop))
   $Drop = FALSE; // Safe default - Set to TRUE to drop the table if it already exists.
   
if (!isset($Explicit))
   $Explicit = FALSE; // Safe default - Set to TRUE to remove all other columns from table.

$Database = Gdn::Database();
$SQL = $Database->SQL(); // To run queries.
$Construct = $Database->Structure(); // To modify and add database tables.
$Validation = new Gdn_Validation(); // To validate permissions (if necessary).
$PluginManager = Gdn::PluginManager();

$Construct->Table('Shoutbox');
$old_data = array();

//Remove old tables and insert the existing data into the new one
if($Construct->TableExists()) {
	//Check for Van2Shout
	if(array_key_exists('Van2Shout', $PluginManager->EnabledPlugins())
	 && $SQL->Query('SHOW COLUMNS FROM GDN_Shoutbox LIKE "UserName"')->NumRows() > 0) {
		$tmp = $SQL->Select("*")->From("Shoutbox")->Get()->ResultArray();
		$um = new UserModel();
		foreach($tmp as $msg) {
			//Parse old usernames to actual userids
			$old_user = $um->GetByUsername($msg["UserName"]);
			$userid = isset($old_user)? $old_user->UserID: null;

			if($msg["PM"] != '') {
				$old_user = $um->GetByUsername($msg["PM"]);
				$messageto = isset($old_user)? $old_user->UserID: null;
			}
			else $messageto = null;

			$old_data[] = array(
				"UserID" => $userid,
				"MessageTo" => $messageto,
				"Timestamp" => $msg["Timestamp"],
				"Content" => $msg["Content"]
			);
		}
		$PluginManager->DisablePlugin('Van2Shout');
		$Drop = true;
		$Explicit = true;
	}
}

//Create basis table for the shoutbox application
$Construct->PrimaryKey('EventID')
	->Column('UserID', 'int', FALSE)
	->Column('MessageTo', 'int', TRUE)
	->Column('Timestamp', 'int(11)')
	->Column('Content', 'text')
	->Set($Drop, $Explicit);


//Insert old shoutbox data as good as we can with the remaining information
if($Drop && count($old_data) > 0) {
	new ShoutModel()->AddShouts($old_data);
}

// Example: Add column to existing table.
/* 
$Construct->Table('User')
   ->Column('NewColumnNeeded', 'varchar(255)', TRUE) // Always allow for NULLs unless it's truly required.
   ->Set(); 
*/  
   
/**
 * Column() has the following arguments:
 *
 * @param string $Name Name of the column to create.
 * @param string $Type Data type of the column. Length may be specified in parenthesis.
 *    If an array is provided, the type will be set as "enum" and the array's values will be assigned as the column's enum values.
 * @param string $NullOrDefault Default is FALSE. Whether or not nulls are allowed, if not a default can be specified.
 *    TRUE: Nulls allowed. FALSE: Nulls not allowed. Any other value will be used as the default (with nulls disallowed).
 * @param string $KeyType Default is FALSE. Type of key to make this column. Options are: primary, key, or FALSE (not a key).
 *
 * @see /library/database/class.generic.structure.php
 */
