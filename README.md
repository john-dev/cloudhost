Cloudhost

cloudhost is a proxy / data-storage for cloudapp build in php. You can use it with an existing account. The main difference is, file uploads are redirected to your own data-storage.
Cloudhost works as proxy until the fileupload ticket has been received / created. This upload ticket will be passed through to the client in a modified version, to redirect it to a different data-storage (instead of amazon s-3). The ticket will further be used to handle the upload at the new data-storage.

#How it works: 
  - Client = Cloudapp-Client 
  - Server = cloudapp 
  - Proxy = cloudhost

Client authenticates with the server, through the proxy. The proxy does nothing in this case, it simply routes the traffic to the targets and stores some information it needs later.
Client ask for an item list, which can be either the one from cloudhost, from cloudapp or a combined list from both.
Client requests an auth code from Server through the proxy. This auth code will be stored by proxy, and redirected to the client.
Client requests a new item ticket, which will be handled by the Server. The proxy then uses this ticket to create valid upload ticket for it's own data-storage and redirect it modified to the client.
The Client now starts a POST upload to the destination, the proxy gave him. The ticket is validated here and the upload is handled by the proxys data-storage.

Thats it!

#Requirements (same as development enviroment):

- apache2
  - mod_rewrite
  - mod_headers (to fix php bug with charset after boundary) 
- php >=5.3.x
- mysqli

#Setup:
- Create database using db_create.sql
- Add a valid (=cloudapp account) email to table "user" (for example with phpmyadmin)
- Change includes/config.php to your needs (it's commented)
- Change the dns reply from my.cl.ly to your server's ip (using hosts or dns-overwrite etc..)
- Setup a vhost for my.cl.ly pointing to cloudhost/ as document root
- Setup a vhost for <fontend vhost> (set in includes/config.php) pointing to frontend/ as document root
- Set chmod 775 for data/
- Done.

#This version ONLY supports:
- cloudapp for mac
- fluffyapp for pc
- cloudette for iphone/ipad
- maybe some other clients, i did not test any others..

#Project status:
-Finished:
  - The handler for supported cloudapp's  are finished
  - The frontend (only able to serve files, the account-frontend isn't written yet)
  

Not finished: 
  - own authentication (requires real user accounts at cloudhost)  
  - account managing for cloudhost/cloudapp
  - statistics from google shorturls  
  - delete / modify in webfrontend
  




