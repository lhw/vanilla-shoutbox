<?php if (!defined('APPLICATION')) exit(); // Make sure this file can't get accessed directly
/**
 * A special function that is automatically run upon enabling your application.
 */
class ShoutboxHooks implements Gdn_IPlugin {
   /**
    * Special function automatically run upon clicking 'Enable' on your application.
    * Change the word 'skeleton' anywhere you see it.
    */
   public function Setup() {
			if(!C('Skeleton.Setup', FALSE)) {
				include(PATH_APPLICATIONS.DS.'shoutbox'.DS.'settings'.DS.'structure.php');
			}
      SaveToConfig('Shoutbox.Setup', TRUE);
   }
   
   /**
    * Special function automatically run upon clicking 'Disable' on your application.
    */
   public function OnDisable() {
      // Optional.Delete this if you don't need it.
   }
   
   /**
    * Special function automatically run upon clicking 'Remove' on your application.
    */
   public function CleanUp() {
      // Optional. Delete this if you don't need it.
   }

	public function Base_Render_Before(&$Sender) {
      $Session = Gdn::Session();
		$Controller = $Sender->ControllerName;
		$ShowOnController = array(
			'discussionscontroller',
//			'categoriescontroller',
//			'profilecontroller',
//			'activitycontroller'
		);

		if($Session->IsValid() && InArrayI($Controller, $ShowOnController))
		{
			require_once(PATH_APPLICATIONS.DS.'shoutbox'.DS.'modules'.DS.'class.shoutboxmodule.php');
			$ShoutboxModule = new ShoutboxModule($Sender);
			$Sender->AddModule($ShoutboxModule);
		}
   }
}
