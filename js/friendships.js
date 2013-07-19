$(function(){
   /* Do some javascripts on page load! */
   $('a.RequestFriendship').on("click", function(event){
   		var action = $(this).attr('href');
   		//disable the button
   		$(this).unbind('click');
   		$(this).addClass('disabled');
   		$(this).attr('href','#');
   		var button = $(this);

   		action = action.replace("Friendships","Friendships.json");
		$.ajax({
        	type: "GET",
         	url: action,
         	dataType: 'json',
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$.popup({}, XMLHttpRequest.responseText);
			},
			success: function(data) {
				data = $.postParseJson(data);
				if(data.FriendshipRequested == true){
					gdn.informMessage(data.Message);
					button.replaceWith('<h6>' + gdn.definition('Friendships requested') + '</h6>');
				}else{
					gdn.informMessage(gdn.definition('Error during friendship request'));
					button.replaceWith('<p>' + gdn.definition('Error during friendship request. Please reload the page') + '</p>');
				}
			}
		});
		return false;
	});
});