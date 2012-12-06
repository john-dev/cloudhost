<?

define('DB_HOST','localhost');//..
define('DB_USER','user');//..
define('DB_PASS','pass');//..
define('DB_NAME','cloudhost');//..

define('SYS_TITLE','cloudhost');//system title
define('DATA_STORAGE','../data/');//we will store the data here.. change that at any time.. (better dont put it inside the documents root.. we stream files, if you dont like it, create symlinks..)
define('COMBINE_LISTINGS',true);//listing will be combined from cloudapp and cloudhost items (requires my.cl.ly as auth server)

#we stream files to "customers" here directly (using mime types..) - dont change this on a live system (once tried, always used..)
#using a nice (not yet good looking) web frontend
define('CLOUDHOST_FILE_FRONTEND',"http://sub.domain.net/view");//set this to something else then my.cl.ly, this will never point to your server as long as ppl do not redirect the dns request (be sure to set up the host of course..)
define('CLOUDHOST_FILE_FRONTEND_DIRECT',"http://sub.domain.net/view/direct");//"
define('CLOUDHOST_FILE_FRONTEND_DOWNLOAD','http://sub.domain/view/download');//"
define('CLOUDHOST_FILE_FRONTEND_THUMBNAIL','http://sub.domain.net/view/thumbnail');//"
define('CLOUDHOST_ACCOUNT_FRONTEND','http://sub.domain.net/account');//"

define('CLOUDAPP_AUTHSERVER','http://auth.domain.net');//used for the authentication; keep this secret, its part of the auth servers nonce (if you want to use an cludapp acc, change this to my.cl.ly => no pushing)

define('USER_MAX_UPLOADSIZE',26214400*4*10*3);//0 is not allowed.. =3 gb (this does NOT evade cloudapps upload size to s3 since the ticket provided is created with the default size.. screw it!)
define('KEY_VALIDATION_TIME',24 * 60 * 60);//=24 hours, the time a key is valid, this key must stay valid until the upload is done!!!
define('USE_SHORTURLS',false);//we need to create them anyway..
define('SHORTURL_SERVICE','https://www.googleapis.com/urlshortener/v1/url');
define('SHORTURLS_APIKEY',false);//supported for google (serverkey!)

//pusher acc
define('PUSHER_app_id',false);//
define('PUSHER_key',false);
define('PUSHER_secret',false);

###dont change anything below this, since those functions are not built-in yet / working yet.. 
define('DO_DEBUG',false);//will cause malfunction..
define('CREATE_FILES_PUBLIC','public-read');// 
define('VERSION','0.4b blueprint');//..
define('CLOUDAPP_SERVER','http://my.cl.ly');//
define('CURL_USER_AGENT','Cloud/1.5.4 CFNetwork/520.5.1 Darwin/11.4.2 (x86_64) (MacBookPro8%2C1)');//the fake useragent we use to be able to authenticate with cloudapp (we overwrite that in handleREUQEST constructor)
