<?php if (!defined('APPLICATION')) exit();
/**
* Renders a list of friends or show a "Request friendship" button
*/
class FriendshipsModule extends Gdn_Module {

	protected $_Friends;
	protected $_FriendshipModel;

	public function __construct($Sender = '') {
		parent::__construct($Sender);
		$this->_FriendshipModel = new FriendshipModel();
	}

	public function AssetTarget() {
		return 'Panel';
	}

	private function _RequestLink($UserID) {
		$Return = '';
		$Return .= '<a class="Button Friendship RequestFriendship" href="' . Url("/plugin/Friendships/RequestFriendship/".$UserID) . '">';
		$Return .= T('Request friendship');
		$Return .= '</a>';
		return $Return;
	}

	private function _ConfirmLinkWhitName($Friendship) {
		$Return = '';
		$Return .= '<a class="Button Friendship ConfirmFriendshipName" href="' . Url("/plugin/Friendships/ConfirmFriendship/".$Friendship->RequestedBy) . '">';
		$Return .= sprintf(T('Confirm %1$s friendship'), $Friendship->RequestedByName);
		$Return .= '</a>';
		return $Return;
	}

	private function _ConfirmLink($UserID) {
		$Return = '';
		$Return .= '<a class="Button Friendship ConfirmFriendship" href="' . Url("/plugin/Friendships/ConfirmFriendship/".$UserID) . '">';
		$Return .= T('Confirm friendship');
		$Return .= '</a>';
		return $Return;
	}

	private function _DeleteLink($UserID, $Text) {
		$Return = '';
		$Return .= '<a class="Button Friendship DeleteFriendship" href="' . Url("/plugin/Friendships/DeleteFriendship/". $UserID) . '">';
		$Return .= T($Text);
		$Return .= '</a>';
		return $Return;
	}

	private function _FriendsList($UserID) {
		$Friends = $this->_FriendshipModel->Friends($UserID);
		$Return = '';
		if(sizeof($Friends) > 0) {
			$Return .= '<h5>' . T('Friends list') . '</h5>';
			$Return .= '<div class="Avatars">';
			foreach ($Friends as $Friend) {
				$Return .= UserPhoto($Friend);
			}
			$Return .= '</div>';
		}
		return $Return;
	}

	private function _ReceivedFriendshipRequests() {
		$Return = '';
		$PendingRequests = $this->_FriendshipModel->ReceivedPendingRequests(Gdn::Session()->UserID);
		if(sizeof($PendingRequests) > 0) {
			$Return .= '<h5>' . T('Pending requests') . '</h5>';
			$Return .= '<ul class="FriendshipRequests">';
			foreach ($PendingRequests as $Friendship) {
				$Return .= $this->_ConfirmLinkWhitName($Friendship);
			}
			$Return .= '</ul>';
		}
		return $Return;
	}

	private function _RequestFriendshipButton($UserID) {
		return $this->_RequestLink($UserID);
	}

	private function _DeleteFriendshipButton($UserID) {
		return $this->_DeleteLink($UserID, 'Delete friendship');
	}

	private function _DeleteFriendshipRequestButton($UserID) {
		return $this->_DeleteLink($UserID, 'Delete friendship request');
	}

	private function _ConfirmFriendshipButton($UserID) {
		return $this->_ConfirmLink($UserID);
	}


	public function ToString() {
		if($this->_Sender instanceof ProfileController) {
			if(CheckPermission('Friendships.Friends.View')){
				$ProfileOwnerID = $this->_Sender->User->UserID;
				$String = '<div class="Box FriendshipsBox">';
				$String .= '<h4>' . T('Friendships') . '</h4>';
				if(Gdn::Session()->IsValid()){ //a logged user
					$SessionUserID = Gdn::Session()->UserID;
					//check if current user is on his page -> shows only his friend
					if($ProfileOwnerID == $SessionUserID){
						//this is my profile page (AND obviously I'm NOT a guest)
						$String .= $this->_ReceivedFriendshipRequests();
					}else{
						//this is NOT my profile page
						//Check if a friendship exists or a friendship request exist: 'request' or 'confirm'
						if($this->_FriendshipModel->FriendsFrom($SessionUserID, $ProfileOwnerID)){
							if(CheckPermission('Friendships.Friends.DeleteFriendship')){
								$String .= $this->_DeleteFriendshipButton($ProfileOwnerID);
							}	
						}elseif($this->_FriendshipModel->Get($SessionUserID, $ProfileOwnerID)){
							$Out = $this->_FriendshipModel->GetAbsolute($SessionUserID, $ProfileOwnerID);
							$In = $this->_FriendshipModel->GetAbsolute($ProfileOwnerID, $SessionUserID);
							if($Out){ //is a friendship request from me
								$String .= $this->_DeleteFriendshipRequestButton($Out->RequestedTo);
							}else{ //is an incoming friendship request
								$String .= $this->_ConfirmFriendshipButton($In->RequestedBy);
							}
						}else{ //show the "Request friendship" button
							if(CheckPermission('Friendships.Friends.RequestFriendship')){
								$String .= $this->_RequestFriendshipButton($ProfileOwnerID);
							}
						}
					}
					$String .= $this->_FriendsList($ProfileOwnerID);
				}else{//I'm guest -> I can have only view permission (internal vanilla security rule)
					//show friends list
					$String .= $this->_FriendsList($ProfileOwnerID);
				}	
				$String .= '</div>';
				return $String;
			}
		}
	}
}