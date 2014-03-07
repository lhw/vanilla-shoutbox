<?php
/**
 * An associative array of information about this application.
 */
$ApplicationInfo['Shoutbox'] = array(
   'Description' => "A simple and elegant shoutbox for vanilla forums",
   'Version' => '0.1',
	 'SettingsPermission' => array('Shoutbox.View', 'Shoutbox.Post', 'Shoutbox.Delete'),
	 'RegisterPermissions' => array('Shoutbox.View', 'Shoutbox.Post', 'Shoutbox.Delete'),
   'SetupController' => 'setup',
   'Author' => "Lennart Weller",
   'AuthorEmail' => 'lhw@ring0.de',
   'AuthorUrl' => 'http://lhw.ring0.de',
   'License' => 'GPL2+'
);
