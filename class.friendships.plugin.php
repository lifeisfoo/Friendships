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
  private $_FriendshipModel;

  public function __construct() {
    parent::__construct();
    //TODO: class not loaded at plugin enabling
    $this->_FriendshipModel = new FriendshipModel();
  }
   
  /**
  * Base_Render_Before Event Hook
  *
  * This is a common hook that fires for all controllers (Base), on the Render method (Render), just 
  * before execution of that method (Before). It is a good place to put UI stuff like CSS and Javascript 
  * inclusions. Note that all the Controller logic has already been run at this point.
  *
  * @param $Sender Sending controller instance
  */
  public function Base_Render_Before($Sender) {
    //$Sender->AddCssFile('example.css', 'plugins/Example');
    //$Sender->AddJsFile('example.js', 'plugins/Example');
    $Module = new FriendshipsModule($Sender);
    $Sender->AddModule($Module);
  }

  public function PluginController_Friendships_Create($Sender) {
    $this->Dispatch($Sender, $Sender->RequestArgs);
  }

  //default dispatcher http://www.yourforum.com/plugin/Friendships/ action (good for settings page)
  public function Controller_Index($Sender) {}

  //dispatched from http://www.yourforum.com/plugin/Friendships/RequestFriendship
  public function Controller_RequestFriendship($Sender, $Args) {
    echo "<pre>";;
    var_dump($Args);
    print_r($Sender);
    exit();
    //redirect to profile user page
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/ConfirmFriendship
  public function Controller_ConfirmFriendship($Sender) {

    //redirect to current session user profile page
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/DeleteFriendship
  public function Controller_DeleteFriendship($Sender) {}

  public function ProfileController_BeforeRenderAsset_Handler($Sender, $Args) {
    if($Args['AssetName'] == 'Content') {
      var_dump($this->_FriendshipModel->Get(1,2));
    }
  }

  public function ProfileController_BeforeStatusForm_Handler($Sender) {
    echo "HI!";
  }
   
  public function Setup() {
    Gdn::Structure()
      ->Table('Friendship')
      ->Column('RequestedBy', 'int(11)', FALSE, 'primary')
      ->Column('RequestedTo', 'int(11)', FALSE, 'primary')
      ->Column('RequestedOn', 'datetime')
      ->Column('Accepted', 'datetime', TRUE) //can be null
      ->Set(FALSE, FALSE);

    foreach ($this->_UrlMapping as $Short => $Real) {
      if(!Gdn::Router()->MatchRoute($Short))  {
        Gdn::Router()->SetRoute($Short, $Real, 'Internal');
      }
    }
  }

  public function OnDisable() {
    foreach ($this->_UrlMapping as $Short => $Real) {
      if(Gdn::Router()->MatchRoute($Short)) {
        Gdn::Router()->DeleteRoute($Short);
      }
    }
  }
   
}
