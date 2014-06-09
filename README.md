Incognito
=========

Plugin for Vanilla forum that allows logged in users to post as one dedicated and as such, anonymous user account.   
   
This plugin is fully customizable regarding user roles, category permission and the user account that is used for the anonymous posts. By default a) no one is allowed to post anonymous and b) posting anonymous is allowed in no category at all. So you **have to** go to www.yourforum.com/dashboard/role and allow incognito posts for one or more roles in one or more categories.  
   
You can also specify which user account to use. You can create a user such as "Ghostwriter", "Anonymous" or "Cyrano" or whatever you like and set the line `$Configuration['Plugins']['Incognito']['UserID'] = '2';` accordingly in `/conf/config.php`
