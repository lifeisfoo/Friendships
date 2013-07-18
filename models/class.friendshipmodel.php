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

/**
 * Manages Friendships
 *
 * @since 0.1
 */
class FriendshipModel extends Gdn_Model {

  /**
  * Class constructor. Defines the related database table name.
  * 
  * @since 0.1
  * @access public
  */
  public function __construct() {
    parent::__construct('Friendship');
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return Object Friendship objects
  */
  public function Get($AUserID, $BUserID) {
    //remove the oldest (if two are present)
    $Friendship = $this->SQL->Select('*')
                            ->From('Friendship f')
                            ->Where(array('f.RequestedBy' => $AUserID, 'f.RequestedTo' => $BUserID))
                            ->OrWhere(array('f.RequestedBy' => $BUserID, 'f.RequestedTo' => $AUserID))
                            ->Get()
                            ->FirstRow();
    return $Friendship;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return Object Friendship or NULL
  */
  public function GetAbsolute($AUserID, $BUserID) {
    $Friendship = $this->SQL->Select('*')
                            ->From('Friendship f')
                            ->Where(array('f.RequestedBy' => $AUserID, 'f.RequestedTo' => $BUserID))
                            ->Get()
                            ->FirstRow();
    return $Friendship;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return array Users friends of $UserID
  */
  public function Friends($UserID) {
    //only accepted
    $FriendsIDs = $this->FriendsIDs($UserID);
    $Friends = $this->SQL->Select('*')
                      ->From('User u')
                      ->WhereIn('u.UserID', $FriendsIDs)
                      ->Get()
                      ->Result();
    return $Friends;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return array of users IDs
  */
  public function FriendsIDs($UserID) {
    //only accepted friendships
    $IDs = array();
    $Res = $this->SQL->Select('f.RequestedTo')
                      ->From('Friendship f')
                      ->Where('f.RequestedBy', $UserID)
                      ->Where('f.Accepted is not null')
                      ->Get()
                      ->Result();
    foreach ($Res as $User) {
      array_push($IDs, $User->RequestedTo);
    }
    $Res2 = $this->SQL->Select('f.RequestedBy')
                      ->From('Friendship f')
                      ->Where('f.RequestedTo', $UserID)
                      ->Where('f.Accepted is not null')
                      ->Get()
                      ->Result();
    foreach ($Res2 as $User) {
      array_push($IDs, $User->RequestedBy);
    }
    return $IDs;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a timestap if a friendship exists or NULL
  */
  public function FriendsFrom($AUserID, $BUserID) {
    return $this->Get($AUserID, $BUserID)->Accepted;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a timestap if a friendship was requested or NULL
  */
  public function FriendshipRequested($AUserID, $BUserID) {
    return $this->GetAbsolute($AUserID, $BUserID)->RequestedOn;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a array of pending request to the user
  */
  public function ReceivedPendingRequests($UserID) {
    $Res = $this->SQL->Select('f.RequestedTo, f.RequestedBy, f.RequestedOn, f.Accepted')
                      ->Select('u.Name', '', 'RequestedByName')
                      ->From('Friendship f')
                      ->Where('f.RequestedTo', $UserID)
                      ->Where('f.Accepted', NULL)
                      ->Join('User u', 'u.UserID = f.RequestedBy')
                      ->Get()
                      ->Result();
    return $Res;
  }

  /**
  * Delete request or complete friendships
  * 
  * @since 0.1
  * @access public
  */
  public function Delete($AUserID, $BUserID) {
    //Delete A->B
    $Del_A_B = $this->SQL->Delete('Friendship',
      array('RequestedBy' => $AUserID, 
            'RequestedTo' => $BUserID
            )
    );
    //Delete B->A
    //if enabled a user can delete also friendships requesto to him
    $Del_B_A = $this->SQL->Delete('Friendship',
      array('RequestedBy' => $BUserID, 
            'RequestedTo' => $AUserID
            )
    );
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  */
  public function Request($AUserID, $BUserID) {
    if($this->GetAbsolute($BUserID, $AUserID)) {
      $Reverse = $this->GetAbsolute($BUserID, $AUserID);
      if(!$Reverse->Accepted) {
        $this->Confirm($BUserID, $AUserID);
      }
    }elseif(!$this->GetAbsolute($AUserID, $BUserID)) {
      $this->SQL->Insert('Friendship', 
        array('RequestedBy' => $AUserID, 
              'RequestedTo' => $BUserID,
              'RequestedOn' => Gdn_Format::ToDateTime()
              )
      );
    }else{
      //exists yet, do nothing
    }
  }

  /**
  * Confirm the friendship request by AUser to BUser
  * 
  * @since 0.1
  * @access public
  */
  public function Confirm($AUserID, $BUserID) {
    if($this->GetAbsolute($AUserID, $BUserID)) {
      $this->SQL->Update('Friendship')
                ->Set('Accepted', Gdn_Format::ToDateTime())
                ->Where('RequestedBy', $AUserID)
                ->Where('RequestedTo', $BUserID)
                ->Put();
    }else{
      //Friendship request doesn't exists
    }
  }

}