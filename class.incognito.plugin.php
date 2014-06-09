<?php if (!defined('APPLICATION')) exit();

$PluginInfo['Incognito'] = array(
   'Name' => 'Incognito',
   'Description' => 'Allows users to post discussion and comments anonymously',
   'Version' => '0.1',
   'RegisterPermissions' => array(
      'Plugins.Incognito.Allow',
      'Vanilla.Discussions.Incognito',
      'Vanilla.Comments.Incognito'
   ),
   'MobileFriendly' => TRUE,
   'Author' =>' Robin Jurinka',
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
      if(Gdn::Session()->CheckPermission('Plugins.Incognito.Allow')) {
         $Sender->EventArguments['Options'] .= Wrap($Sender->Form->CheckBox('Incognito', 'Hide your name'), 'li');
      }
   }
   
   public function DiscussionController_AfterBodyField_Handler($Sender) {
      if(Gdn::Session()->CheckPermission('Plugins.Incognito.Allow')) {
         echo '<ul class="List Inline PostOptions><li>'.$Sender->Form->CheckBox('Incognito', 'Hide your name').'</li></ul>';
      }
   
   }

   public function CommentModel_BeforeSaveComment_Handler($Sender) {
      $Session = Gdn::Session();
      $DiscussionModel = new DiscussionModel();
      $CategoryModel = new CategoryModel();
      $DiscussionID = $Sender->EventArguments['FormPostValues']['DiscussionID'];
      $Discussion = $DiscussionModel->GetID($DiscussionID);
      $CategoryID = $Discussion->CategoryID;
      $Category = $CategoryModel->GetID($CategoryID);
      $PermissionCategoryID = $Category->PermissionCategoryID;
      
      if (
         $Session->CheckPermission('Plugins.Incognito.Allow') && 
         $Sender->EventArguments['FormPostValues']['Incognito'] == '1' &&
         $Session->CheckPermission('Vanilla.Comments.Add', TRUE, 'Category', $PermissionCategoryID)
      ) {
         $Sender->EventArguments['FormPostValues']['InsertUserID'] = $this->IncognitoUserID;
      }
   }
   
   public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
      $Session = Gdn::Session();
      $CategoryModel = new CategoryModel();
      $CategoryID = $Sender->EventArguments['FormPostValues']['CategoryID'];
      $Category = $CategoryModel->GetID($CategoryID);
      $PermissionCategoryID = $Category->PermissionCategoryID;   
   
      if (
         $Session->CheckPermission('Plugins.Incognito.Allow') && 
         $Sender->EventArguments['FormPostValues']['Incognito'] == '1' &&
         $Session->CheckPermission('Vanilla.Comments.Add', TRUE, 'Category', $PermissionCategoryID)
      ) {
         $Sender->EventArguments['FormPostValues']['InsertUserID'] = $this->IncognitoUserID;
      }
   }
}
