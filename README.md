Incognito (alpha!)
=========

Plugin for Vanilla forum that allows logged in users to post as anonymous user.

By default all comments and discsussions are done in the name of the system user. So before using that plugin, you should add a new user "anonymous" or something like that, look up the user id (eg 123) and add `$Configuration['Plugins']['Incognito']['UserID'] = 123;` to your /conf/config.php.

The plugin adds a checkbox below the text input field that allows users to post as the specified user.

TODO:
fix ajax post back problem
add role permissions
check permissions for posting into that category!
add prefix line to comments and discussion?
