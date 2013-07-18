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
  * @return Object Friendship
  */
  public function Get($AUserID, $BUserID) {
    //remove the oldest (if two are present)
    $Friendship = $this->SQL->Select('*')
                            ->From('Friendship f')
                            ->Where(array('f.RequestedBy' => $AUserID, 'f.RequestedTo' => $BUserID))
                            ->OrWhere(array('f.RequestedBy' => $BUserID, 'f.RequestedTo' => $AUserID))
                            ->Get()
                            ->Result();
    //echo "<pre>";
    //var_dump($Friendship);
    //exit();
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
                            ->Result();
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
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return array of users IDs
  */
  public static function FriendsIDs($UserID) {
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a timestap if a friendship exists or NULL
  */
  public function FriendsFrom($AUserID, $BUserID) {
    return NULL;
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a timestap if a friendship was requested or NULL
  */
  public function FriendshipRequested($AUserID, $BUserID) {
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  * @return a array of pending request to the user
  */
  public function ReceivedPendingRequests($UserID) {
    //join con username con campo RequestedByName
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  */
  public function Delete($AUserID, $BUserID) {
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  */
  public function RequestFriendship($AUserID, $BUserID) {
  }

  /**
  * 
  * 
  * @since 0.1
  * @access public
  */
  public function ConfirmFriendship($AUserID, $BUserID) {
  }

}