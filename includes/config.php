<?

define('DB_HOST','localhost');//..
define('DB_USER','user');//..
define('DB_PASS','pass');//..
define('DB_NAME','cloudhost');//..

define('SYS_TITLE','cloudhost');//system title
define('DATA_STORAGE','../data/');//we will store the data here.. change that at any time.. (better dont put it inside the documents root.. we stream files, if you dont like it, create symlinks..)
define('COMBINE_LISTINGS',true);//listing will be combined from cloudapp and cloudhost items

#set your frontend here
#using a nice (not yet good looking) web frontend
define('CLOUDHOST_FILE_FRONTEND',"http://sub.domain.net/view");//set this to something else then my.cl.ly, this will never point to your server as long as ppl do not redirect the dns request (be sure to set up the host of course..)
define('CLOUDHOST_FILE_FRONTEND_DIRECT',"http://sub.domain.net/view/direct");//"
define('CLOUDHOST_FILE_FRONTEND_DOWNLOAD','http://sub.domain.net/view/download');//"
define('CLOUDHOST_FILE_FRONTEND_THUMBNAIL','http://sub.domain.net/view/thumbnail');//"
define('CLOUDHOST_ACCOUNT_FRONTEND','http://sub.domain.net/account');//"

define('CLOUDAPP_SERVER','http://my.cl.ly');//used for the authentication; you can build your own auth-server here..


define('USER_MAX_UPLOADSIZE',26214400*4*10*3);//0 is not allowed.. =3 gb (you might even use this to evade cloudapps 250mb upload limit but still usign their amazon s3 storage.. ill try that later -> this does NOT work since the ticket provided is created with the default size.. screw it!)
define('KEY_VALIDATION_TIME',24 * 60 * 60);//=24 hours, the time a key is valid, this key must stay valid until the upload is done!!!
define('USE_SHORTURLS',true);//we need to create them anyway..
define('SHORTURL_SERVICE','https://www.googleapis.com/urlshortener/v1/url');
 

###dont change anything below this, since those functions are not built-in yet / working yet.. 
define('IS_PUBLIC',false);//is this system public? if true, everyone with a cloudapp acc can use your host as data storage.. better dont enable it..//not finished!!
define('DO_DEBUG',false);//will cause malfunction..
define('CREATE_FILES_PUBLIC','public-read');//public-read / priavte-read = ppl need to authenticate to see the file later (something cloudapp doesn't provide for free accounts btw..) //not finished!! 
define('SHORTURLS_APIKEY','000000');//not supported yet.. (add api key in includes/class_SHORTURL.php if you like);
define('VERSION','0.2a blueprint');//..
define('PROXY_MODE',false);//not ready yet..
define('CURL_USER_AGENT','Cloud/1.5.4 CFNetwork/520.5.1 Darwin/11.4.2 (x86_64) (MacBookPro8%2C1)');//the fake useragent we use to be able to authenticate with cloudapp (we overwrite that in handleREUQEST contructor)
