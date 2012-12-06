# cloudhost

cloudhost is either a clone from cloudapp or a proxy / data-storage build in php.
The main difference is, you can host it on your own server completely appart from cloudapp or you can use it as a proxy until the file-upload starts.
You can use various cloudapp apps like the nativ os x client, fluffyapp, cloudette or clouddrop.

Cloudhost supports anything cloudapp does (except their fancy web-interface, at least not yet). Even pusherapp to update changes on all your clients in realtime.

----------

#Requirements (same as development enviroment):

- apache2
  - mod_rewrite
  - mod_headers (to fix php bug with charset after boundary) 
- php >=5.3.x
- mysqli
- curl

#Setup:
- Create database using db_create.sql
- Change includes/config.php to your needs (it's commented)
- Change the dns reply from my.cl.ly to your server's ip (using hosts or dns-overwrite etc..)
- Setup a vhost for my.cl.ly pointing to cloudhost/ as document root
- Setup a vhost for your frontend (set in includes/config.php) pointing to frontend/ as document root
- Setup either a vhost for the auth-server (set in includes/config.php) pointing to cloudauth/ as document root or set CLOUDAPP_AUTHSERVER (includes/config.php) to http://my.cl.ly to use an existing cloudapp acc
- Set chmod 775 for data/
- Add a new user "user" using: php includes/add_user.php user password after you setup your system!!
  - leave password empty to use an existing cloudapp account
  - be sure you set CLOUDAPP_AUTHSERVER (config.php) to http://my.cl.ly if you dont provide a password
- Done.
 
#Note:
When using cloudapp (http://my.cl.ly) as auth-server instead of cloudhost, pushing will be disabled!

#This version supports:
- cloudapp for mac
- fluffyapp for pc
- cloudette for iphone/ipad
- cloudrop (amazing app)
- maybe some other clients, i did not test any others..

#Project status:
Finished:
  - The handler for supported cloudapp's is complete
  - Own authentication backend to be completely apart from cloudapp
  - The frontend (is only able to serve files, the account-frontend isn't written yet)
  

Not finished: 
  - account managing for cloudhost/cloudapp
  - statistics from google shorturls  
  - delete / modify in webfrontend
  




