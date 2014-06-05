<?php if (!defined('APPLICATION')) exit();

$PluginInfo['Incognito'] = array(
   'Name' => 'Incognito',
   'Description' => 'Allows users to post discussion and comments anonymously',
   'Version' => '0.0.1',
   'MobileFriendly' => TRUE,
   'Author' =>' Robin',
   'AuthorUrl' => 'http://vanillaforums.org/profile/44046/R_J',
   'License' => 'MIT'
);

class IncognitoPlugin extends Gdn_Plugin {
   public $IncognitoUserID;

   public function __construct() {
      $this->IncognitoUserID = C('Plugins.Incognito.UserID', Gdn::UserModel()->GetSystemUserID());
   }
   public function Setup() {
      if (!C('Plugins.Incognito.UserID')) {
         SaveToConfig('Plugins.Incognito.UserID', Gdn::UserModel()->GetSystemUserID());
      }
   }
   public function PostController_DiscussionFormOptions_Handler($Sender) {
      $Sender->EventArguments['Options'] .= Wrap($Sender->Form->CheckBox('Incognito', 'Hide your name'), 'li');

   }
   public function DiscussionController_AfterBodyField_Handler($Sender) {
      echo '<ul class="List Inline PostOptions><li>'.$Sender->Form->CheckBox('Incognito', 'Hide your name').'</li></ul>';
   }
   public function CommentModel_BeforeSaveComment_Handler($Sender) {
      if ($Sender->EventArguments['FormPostValues']['Incognito'] == '1') {
         $Sender->EventArguments['FormPostValues']['InsertUserID'] = $this->IncognitoUserID;
      }
   }
   public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
      if ($Sender->EventArguments['FormPostValues']['Incognito'] == '1') {
         $Sender->EventArguments['FormPostValues']['InsertUserID'] = $this->IncognitoUserID;
      }
   }

}
