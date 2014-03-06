<?php if (!defined('APPLICATION')) exit(); // Make sure this file can't get accessed directly
/**
 * A special function that is automatically run upon enabling your application.
 *
 * Remember to rename this to FooHooks, where 'Foo' is you app's short name.
 */
class ShoutboxHooks implements Gdn_IPlugin {
   /**
    * Special function automatically run upon clicking 'Enable' on your application.
    * Change the word 'skeleton' anywhere you see it.
    */
   public function Setup() {
      // You need to manually include structure.php here for it to get run at install.
      //include(PATH_APPLICATIONS . DS . 'shoutbox' . DS . 'settings' . DS . 'structure.php'); //TODO: db stuff in that file

      // Stores a value in the config to indicate it has previously been installed.
      // You can use if(C('Skeleton.Setup', FALSE)) to test whether to repeat part of your setup.
      SaveToConfig('Shoutbox.Setup', TRUE);
   }
   
   /**
    * Special function automatically run upon clicking 'Disable' on your application.
    */
   public function OnDisable() {
      // Optional. Delete this if you don't need it.
   }
   
   /**
    * Special function automatically run upon clicking 'Remove' on your application.
    */
   public function CleanUp() {
      // Optional. Delete this if you don't need it.
   }
}
