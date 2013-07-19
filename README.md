# Friendships plugin for Vanilla Forums
This plugin allows users to being "friends" (send, receive and accept "friendship requests").
Other plugins can be developed using this plugin data (e.g. "Show Friends Post", "Friends Activity", etc).

##Features

* Display friends list in user's profile page
* Display "Send friendship request" button on every user's profile page
* Send an email when a friendship is requested

Requires Vanilla >= 2.0.18.4

##Customization
In order to properly display users without avatar you need to create a ```/conf/bootstrap.before.php``` file (if not exists yet) in your vanilla installation directory and define a function inside it.

```php
if (!function_exists('UserPhotoDefaultUrl')) {
function UserPhotoDefaultUrl($User){
   return 'http://img841.imageshack.us/img841/6117/placeholdermale.png';
}
}
``` 

Vanilla will reuse this function every time an user don't have an avatar.


##Translation
Just define these strings in your main vanilla's localization file /locales/yourlang/definitions.php

```
'[%1$s] %2$s wants to be your friend'

'Hi %1$s,

%2$s wants to be your friend. Visit your profile page to confirm this friendship request:

%3$s'

'Request friendship'

'Confirm %1$s friendship'

'Confirm friendship'

'Friends list'

'Pending requests'

'Friendships'

'Delete friendship'

'Delete friendship request'

'Friendship request sent'

'Error during friendship request'

'Friendships requested'

'Error during friendship request'

'Error during friendship request. Please reload the page'
```

##Sponsor
Thanks to [szarak](http://vanillaforums.org/profile/45649/szarak) for making this happen.

##Author and License
Alessandro Miliucci, GPL v3. Icon by [VisualPharm](http://www.visualpharm.com/)
