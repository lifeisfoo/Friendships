<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2013 Alessandro Miliucci <lifeisfoo@gmail.com>
This file is part of Friendships <https://github.com/lifeisfoo/Friendships>

Friendships is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Friendships is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Friendships. If not, see <http://www.gnu.org/licenses/>.

*/

// Define the plugin:
$PluginInfo['Friendships'] = array(
  'Description' => 'Allows users to being "friends" (send, receive and accept "friendship requests")',
  'Version' => '0.1',
  'RequiredApplications' => array('Vanilla' => '2.0.18.4'),
  'RegisterPermissions' => array(
    'Friendships.Friends.View', 
    'Friendships.Friends.RequestFriendship', 
    'Friendships.Friends.DeleteFriendship'),
  'RequiredTheme' => FALSE, 
  'RequiredPlugins' => FALSE,
  'HasLocale' => FALSE,
  'MobileFriendly' => TRUE,
  'SettingsUrl' => FALSE,//plugin/Friendships
  'SettingsPermission' => 'Garden.AdminUser.Only',
  'Author' => "Alessandro Miliucci",
  'AuthorEmail' => 'lifeisfoo@gmail.com',
  'AuthorUrl' => 'http://forkwait.net'
);

class FriendshipsPlugin extends Gdn_Plugin {

  private $_UrlMapping = array(
    'RequestFriendship' => '/plugin/Friendships/RequestFriendship',
    'ConfirmFriendship' => '/plugin/Friendships/ConfirmFriendship',
    'DeleteFriendship' => '/plugin/Friendships/DeleteFriendship'
  );
  private $_FriendshipModel; //TODO: try to use internal DI like apps
  private $_UserModel;

  public function __construct() {
    parent::__construct();
    //TODO: class not loaded at plugin enabling
    if (class_exists('FriendshipModel')) {
      $this->_FriendshipModel = new FriendshipModel();
    }
    if (class_exists('UserModel')) {
      $this->_UserModel = new UserModel();
    }
  }

  private function _ProfileUrl($UserName, $UserID) {
    $UserNameEnc = rawurlencode($UserName);
    if ($UserNameEnc == $UserName) {
      return $UserNameEnc;
    } else {
      return "$UserID/$UserNameEnc";
    }
  }

  private function _FriendshipAction($Action, $FromUser, $ToUser){
    if($FromUser && $ToUser) {
      $this->_FriendshipModel->$Action($FromUser, $ToUser);
      $UserTo = $this->_UserModel->GetID($ToUser);
      Redirect('/profile/' . $this->_ProfileUrl($UserTo->Name, $ToUser));
    }else{
      Redirect('/');
    }
  }
  
  public function Base_Render_Before($Sender) {
    $Module = new FriendshipsModule($Sender);
    $Sender->AddModule($Module);
  }

  public function PluginController_Friendships_Create($Sender) {
    $this->Dispatch($Sender, $Sender->RequestArgs);
  }

  //default dispatcher http://www.yourforum.com/plugin/Friendships/ action (good for settings page)
  public function Controller_Index($Sender) {}

  //dispatched from http://www.yourforum.com/plugin/Friendships/RequestFriendship
  public function Controller_RequestFriendship($Sender) {
    //The first check is only for pedantic security, since guests can only have View Permission
    if(Gdn::Session()->IsValid() && CheckPermission('Friendships.Friends.RequestFriendship')){
      $this->_FriendshipAction('Request', Gdn::Session()->UserID, $Sender->RequestArgs[1]);
    }
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/ConfirmFriendship
  public function Controller_ConfirmFriendship($Sender) {

    //redirect to current session user profile page
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/DeleteFriendship
  public function Controller_DeleteFriendship($Sender) {
    if(Gdn::Session()->IsValid() && CheckPermission('Friendships.Friends.DeleteFriendship')){
      $this->_FriendshipAction('Delete', Gdn::Session()->UserID, $Sender->RequestArgs[1]);
    }
  }

  public function ProfileController_BeforeRenderAsset_Handler($Sender, $Args) {
    if($Args['AssetName'] == 'Content') {
      //var_dump($this->_FriendshipModel->Get(1,2));
    }
  }

  public function ProfileController_BeforeStatusForm_Handler($Sender) {}
   
  public function Setup() {
    Gdn::Structure()
      ->Table('Friendship')
      ->Column('RequestedBy', 'int(11)', FALSE, 'primary')
      ->Column('RequestedTo', 'int(11)', FALSE, 'primary')
      ->Column('RequestedOn', 'datetime')
      ->Column('Accepted', 'datetime', TRUE) //can be null
      ->Set(FALSE, FALSE);
    /* unused due to vanilla bug https://github.com/vanillaforums/Garden/issues/1631
    foreach ($this->_UrlMapping as $Short => $Real) {
      if(!Gdn::Router()->MatchRoute($Short))  {
        Gdn::Router()->SetRoute($Short, $Real, 'Internal');
      }
    }
    */
  }

  public function OnDisable() {
    /* unused due to vanilla bug https://github.com/vanillaforums/Garden/issues/1631
    foreach ($this->_UrlMapping as $Short => $Real) {
      if(Gdn::Router()->MatchRoute($Short)) {
        Gdn::Router()->DeleteRoute($Short);
      }
    }
    */
  }
   
}
