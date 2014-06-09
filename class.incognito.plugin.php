<?php if (!defined('APPLICATION')) exit();

$PluginInfo['Incognito'] = array(
   'Name' => 'Incognito',
   'Description' => 'Allows users to post discussion and comments anonymously',
   'Version' => '0.1',
   'RegisterPermissions' => array('Plugins.Incognito.Allow'),
   'RequiredApplications' => array('Vanilla' => '2.1'),
   'MobileFriendly' => TRUE,
   'Author' =>' Robin Jurinka',
   'AuthorUrl' => 'http://vanillaforums.org/profile/44046/R_J',
   'License' => 'MIT'
);

/**
 * Incognito plugin
 * 
 * Allows users to post under one user id so that the real identy is hidden.
 *
 * @author Robin Jurinka
 * @license http://opensource.org/licenses/MIT
 * @package Incognito
 */
class IncognitoPlugin extends Gdn_Plugin {

   /**
    * Initialy set SystemUserID as config value Plugins.Incognito.UserID
    * and set up category permissions
    *
    */
   public function Setup() {
      // set config value
      if (!is_numeric(C('Plugins.Incognito.UserID'))) {
         $SystemUserID = Gdn::UserModel()->GetSystemUserID();
         SaveToConfig('Plugins.Incognito.UserID', $SystemUserID);
      }
      // add category permissions
      $PermissionModel = Gdn::PermissionModel();
      $PermissionModel->Define(
         array(
            'Vanilla.Discussions.Incognito' => 0,
            'Vanilla.Comments.Incognito' => 0),
         'tinyint',
         'Category',
         'PermissionCategoryID'
      );
   }
   
   /**
    * Check permissions and
    * add CheckBox to new discussions
    *
    * @param PostController $Sender 
    *
    */
   public function PostController_DiscussionFormOptions_Handler($Sender) {
      if(Gdn::Session()->CheckPermission('Plugins.Incognito.Allow')) {
         $Sender->EventArguments['Options'] .= Wrap($Sender->Form->CheckBox('Incognito', 'Hide your name'), 'li');
      }
   }
   
   /**
    * Check permissions and
    * add CheckBox to comment entry textbox
    *
    * @param DiscussionController $Sender 
    *
    */
   public function DiscussionController_AfterBodyField_Handler($Sender) {
      $Session = Gdn::Session();
      // check for  user permission
      if ($Session->CheckPermission('Plugins.Incognito.Allow')) {
         // get PermissionCategoryID of current discussion
         $CategoryModel = new CategoryModel();
         $CategoryID = $Sender->CategoryID;
         $Category = $CategoryModel->GetID($CategoryID);
         $PermissionCategoryID = $Category->PermissionCategoryID;
         
         //check for category permission
         if ($Session->CheckPermission(array('Vanilla.Comments.Incognito'), TRUE, 'Category', $PermissionCategoryID)) {
            // add CheckBox
            echo '<ul class="List Inline PostOptions><li>'.$Sender->Form->CheckBox('Incognito', 'Hide your name').'</li></ul>';
         }
      }
   }
   
   /**
    * Call _HideUser before comment save
    *
    * @param CommentController $Sender 
    *
    */
   public function CommentModel_BeforeSaveComment_Handler($Sender) {
      $this->_HideUser($Sender);
   }
   
   /**
    * Call _HideUser before discussion save
    *
    * @param DiscussionController $Sender 
    *
    */
   public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
      $this->_HideUser($Sender);
   }
   
   /**
    * Checks role permissions as well as category permissions
    * and changes InsertUserID to IncognitoUserID
    *
    * @param VanillaController $Sender    Either CommentController or DiscussionController
    *
    */
   private function _HideUser($Sender) {
      $Session = Gdn::Session();
      // return if checkbox not send or missing role permissions 
      if ($Sender->EventArguments['FormPostValues']['Incognito'] !== '1' || !$Session->CheckPermission('Plugins.Incognito.Allow')) {
         return;
      }
      
      if (get_class($Sender) == 'CommentModel' ) {
         // get CategoryID of comment
         $DiscussionModel = new DiscussionModel();
         $DiscussionID = $Sender->EventArguments['FormPostValues']['DiscussionID'];
         $Discussion = $DiscussionModel->GetID($DiscussionID);
         $CategoryID = $Discussion->CategoryID;
         $PostType = 'Comments';
      } else {
         // ... or of discussion
         $CategoryID = $Sender->EventArguments['FormPostValues']['CategoryID'];
         $PostType = 'Discussions';
      }
      
      // get PermissionCategoryID of CategoryID
      $CategoryModel = new CategoryModel();
      $Category = $CategoryModel->GetID($CategoryID);
      $PermissionCategoryID = $Category->PermissionCategoryID;
      
      // check for permissions and change InsertUserID to IncognitoUserID...
      if ($Session->CheckPermission(array('Vanilla.Comments.Add', "Vanilla.{$PostType}.Incognito"), TRUE, 'Category', $PermissionCategoryID)
      ) {
         $IncognitoUserID = C('Plugins.Incognito.UserID', Gdn::UserModel()->GetSystemUserID());
         $Sender->EventArguments['FormPostValues']['InsertUserID'] = $IncognitoUserID;
      } else {
         // ... or stop with error message if no permissions
         $Sender->Validation->AddValidationResult('CategoryID', 'You do not have permission to hide your name in this category');
      }
   }
}
