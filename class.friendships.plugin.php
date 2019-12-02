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

Gdn::FactoryInstall('FriendshipModel', 'FriendshipModel', __DIR__ . DS . 'models', Gdn::FactoryInstance);

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

  private function _FriendshipAction($Action, $FromUser, $ToUser, $Redirect = TRUE){
    if($FromUser && $ToUser) {
      $this->_FriendshipModel->$Action($FromUser, $ToUser);
      $UserTo = $this->_UserModel->GetID($ToUser);
      $RedirectUrl = '/profile/' . $this->_ProfileUrl($UserTo->Name, $ToUser);
      if($Redirect) {
        Redirect($RedirectUrl);
      }else{
        return $RedirectUrl;
      }
    }else{
      $RedirectUrl = '/';
      if($Redirect) {
        Redirect($RedirectUrl);
      }else{
        return $RedirectUrl;
      }
    }
  }

  public function Base_Render_Before($Sender) {
    // $Sender->AddJsFile('friendships.js', 'plugins' . DS . 'Friendships');
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
    if(Gdn::Session()->IsValid() && CheckPermission('Plugins.Friendships.Add')){
      $RedirectUrl = $this->_FriendshipAction('Request', Gdn::Session()->UserID, $Sender->RequestArgs[1], FALSE);
      if($RedirectUrl != '/'){
        $User = $this->_UserModel->GetID($Sender->RequestArgs[1]);
        $Email = new Gdn_Email();
        $Email->Subject(sprintf(T('[%1$s] %2$s wants to be your friend', C('Garden.Title'), $User->Name)));
        $Email->To($User->Email);
        $Email->Message(
          sprintf(
             T('Hi %1$s,

  %2$s wants to be your friend. Visit your profile page to confirm this friendship request:

  %3$s'),
             $User->Name,
             Gdn::Session()->User->Name,
             ExternalUrl('/profile/' . $this->_ProfileUrl(Gdn::Session()->User->Name, Gdn::Session()->UserID))
          )
        );
        $Email->Send();
      }
      if($Sender->DeliveryMethod() == 'JSON') {
        $Sender->InformMessage(Gdn::Translate('Friendship request sent'));
        $Sender->JsonTarget(
          ".Button.RequestFriendship",
          (new FriendshipsModule)->DeleteFriendshipRequestButton($User->UserID),
          'ReplaceWith'
        );
        $Sender->Render('Blank', 'Utility', 'Dashboard');
      }else {
        Redirect($RedirectUrl);
      }
    }
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/ConfirmFriendship
  public function Controller_ConfirmFriendship($Sender) {
    if(Gdn::Session()->IsValid()){
      $this->_FriendshipAction('Confirm', $Sender->RequestArgs[1], Gdn::Session()->UserID, FALSE);
    }
    $Sender->InformMessage(Gdn::Translate('Friendship confirmed'));
    if(is_numeric($Sender->RequestArgs[2] ?? false)){
      $FriendsListUserID = $Sender->RequestArgs[2];
    }else{
      $FriendsListUserID = $Sender->RequestArgs[1];
    }
    $Sender->JsonTarget(
      ".Button.ConfirmFriendship",
      (new FriendshipsModule)->FriendsList($FriendsListUserID),
      'ReplaceWith'
    );
    $Sender->Render('Blank', 'Utility', 'Dashboard');
  }

  //dispatched from http://www.yourforum.com/plugin/Friendships/DeleteFriendship
  public function Controller_DeleteFriendship($Sender) {
    if(Gdn::Session()->IsValid() && CheckPermission('Plugins.Friendships.Delete')){
      $this->_FriendshipAction('Delete', Gdn::Session()->UserID, $Sender->RequestArgs[1], FALSE);
    }
    $Sender->InformMessage(Gdn::Translate('Friendship request deleted'));
    $Sender->JsonTarget(
      ".FriendshipsBox .FriendsList",
      (new FriendshipsModule)->RequestFriendshipButton($Sender->RequestArgs[1]),
      'ReplaceWith'
    );
    $Sender->JsonTarget(".Button.DeleteFriendship", '', 'Remove');
    $Sender->Render('Blank', 'Utility', 'Dashboard');
  }

  public function ProfileController_BeforeRenderAsset_Handler($Sender, $Args) {
    if($Args['AssetName'] == 'Content') {}
  }

  public function Setup() {
    $this->Structure();
    /* unused due to vanilla bug https://github.com/vanillaforums/Garden/issues/1631
    foreach ($this->_UrlMapping as $Short => $Real) {
      if(!Gdn::Router()->MatchRoute($Short))  {
        Gdn::Router()->SetRoute($Short, $Real, 'Internal');
      }
    }
    */
  }

  public function Structure() {
    Gdn::Structure()
      ->Table('Friendship')
      ->Column('RequestedBy', 'int(11)', FALSE, 'primary')
      ->Column('RequestedTo', 'int(11)', FALSE, 'primary')
      ->Column('RequestedOn', 'datetime')
      ->Column('Accepted', 'datetime', TRUE) //can be null
      ->Set(FALSE, FALSE);
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
