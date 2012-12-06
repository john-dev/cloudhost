<?
function get_headers_from_curl_response($response)
{
    $headers = array();
    
    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);
            if(key_exists($key,$headers)) { //we have Set-Cookie twice.. moo
                $key=$key."_engine";
            }
            $headers[$key] = $value;
        }

    return $headers;
}
function get_body_from_curl_response($response)
{   
    return substr($response, strpos($response, "\r\n\r\n")+4);
}

function json_custom_encode($a=false)  {
    return str_replace('\\/', '/', json_encode($a));
}
function split_cookie($cookie) {
    $cookie=explode(";",$cookie);
    if(substr($cookie[0],0,7)=="_engine") {
        return $cookie[0];
    } else {
        return (isset($cookie[1]))?$cookie[1]:false;
    }
}

function sort2d ($array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE) 
{
    if(is_array($array) && count($array)>0) 
    {
       foreach(array_keys($array) as $key) 
           $temp[$key]=$array[$key][$index];
           if(!$natsort) 
               ($order=='asc')? asort($temp) : arsort($temp);
          else 
          {
             ($case_sensitive)? natsort($temp) : natcasesort($temp);
             if($order!='asc') 
                 $temp=array_reverse($temp,TRUE);
       }
       foreach(array_keys($temp) as $key) 
           (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
       return $sorted;
  }
  return $array;
} 
function get_mime_type($ext) {
//stolen from the web
$arr=array("323" => "text/h323",
"acx" => "application/internet-property-stream",
"ai" => "application/postscript",
"aif" => "audio/x-aiff",
"aifc" => "audio/x-aiff",
"aiff" => "audio/x-aiff",
"asf" => "video/x-ms-asf",
"asr" => "video/x-ms-asf",
"asx" => "video/x-ms-asf",
"au" => "audio/basic",
"avi" => "video/x-msvideo",
"axs" => "application/olescript",
"bas" => "text/plain",
"bcpio" => "application/x-bcpio",
"bin" => "application/octet-stream",
"bmp" => "image/bmp",
"c" => "text/plain",
"cat" => "application/vnd.ms-pkiseccat",
"cdf" => "application/x-cdf",
"cer" => "application/x-x509-ca-cert",
"class" => "application/octet-stream",
"clp" => "application/x-msclip",
"cmx" => "image/x-cmx",
"cod" => "image/cis-cod",
"cpio" => "application/x-cpio",
"crd" => "application/x-mscardfile",
"crl" => "application/pkix-crl",
"crt" => "application/x-x509-ca-cert",
"csh" => "application/x-csh",
"css" => "text/css",
"dcr" => "application/x-director",
"der" => "application/x-x509-ca-cert",
"dir" => "application/x-director",
"dll" => "application/x-msdownload",
"dms" => "application/octet-stream",
"doc" => "application/msword",
"dot" => "application/msword",
"dvi" => "application/x-dvi",
"dxr" => "application/x-director",
"eps" => "application/postscript",
"etx" => "text/x-setext",
"evy" => "application/envoy",
"exe" => "application/octet-stream",
"fif" => "application/fractals",
"flr" => "x-world/x-vrml",
"gif" => "image/gif",
"gtar" => "application/x-gtar",
"gz" => "application/x-gzip",
"h" => "text/plain",
"hdf" => "application/x-hdf",
"hlp" => "application/winhlp",
"hqx" => "application/mac-binhex40",
"hta" => "application/hta",
"htc" => "text/x-component",
"htm" => "text/html",
"html" => "text/html",
"htt" => "text/webviewhtml",
"ico" => "image/x-icon",
"ief" => "image/ief",
"iii" => "application/x-iphone",
"ins" => "application/x-internet-signup",
"isp" => "application/x-internet-signup",
"jfif" => "image/pipeg",
"jpe" => "image/jpeg",
"jpeg" => "image/jpeg",
"jpg" => "image/jpeg",
"js" => "application/x-javascript",
"latex" => "application/x-latex",
"lha" => "application/octet-stream",
"lsf" => "video/x-la-asf",
"lsx" => "video/x-la-asf",
"lzh" => "application/octet-stream",
"m13" => "application/x-msmediaview",
"m14" => "application/x-msmediaview",
"m3u" => "audio/x-mpegurl",
"man" => "application/x-troff-man",
"mdb" => "application/x-msaccess",
"me" => "application/x-troff-me",
"mht" => "message/rfc822",
"mhtml" => "message/rfc822",
"mid" => "audio/mid",
"mny" => "application/x-msmoney",
"mov" => "video/quicktime",
"movie" => "video/x-sgi-movie",
"mp2" => "video/mpeg",
"mp3" => "audio/mpeg",
"mpa" => "video/mpeg",
"mpe" => "video/mpeg",
"mpeg" => "video/mpeg",
"mpg" => "video/mpeg",
"mpp" => "application/vnd.ms-project",
"mpv2" => "video/mpeg",
"ms" => "application/x-troff-ms",
"mvb" => "application/x-msmediaview",
"nws" => "message/rfc822",
"oda" => "application/oda",
"p10" => "application/pkcs10",
"p12" => "application/x-pkcs12",
"p7b" => "application/x-pkcs7-certificates",
"p7c" => "application/x-pkcs7-mime",
"p7m" => "application/x-pkcs7-mime",
"p7r" => "application/x-pkcs7-certreqresp",
"p7s" => "application/x-pkcs7-signature",
"pbm" => "image/x-portable-bitmap",
"pdf" => "application/pdf",
"pfx" => "application/x-pkcs12",
"pgm" => "image/x-portable-graymap",
"pko" => "application/ynd.ms-pkipko",
"pma" => "application/x-perfmon",
"pmc" => "application/x-perfmon",
"pml" => "application/x-perfmon",
"pmr" => "application/x-perfmon",
"pmw" => "application/x-perfmon",
"pnm" => "image/x-portable-anymap",
"pot" => "application/vnd.ms-powerpoint",
"ppm" => "image/x-portable-pixmap",
"pps" => "application/vnd.ms-powerpoint",
"ppt" => "application/vnd.ms-powerpoint",
"prf" => "application/pics-rules",
"ps" => "application/postscript",
"pub" => "application/x-mspublisher",
"qt" => "video/quicktime",
"ra" => "audio/x-pn-realaudio",
"ram" => "audio/x-pn-realaudio",
"ras" => "image/x-cmu-raster",
"rgb" => "image/x-rgb",
"rmi" => "audio/mid",
"roff" => "application/x-troff",
"rtf" => "application/rtf",
"rtx" => "text/richtext",
"scd" => "application/x-msschedule",
"sct" => "text/scriptlet",
"setpay" => "application/set-payment-initiation",
"setreg" => "application/set-registration-initiation",
"sh" => "application/x-sh",
"shar" => "application/x-shar",
"sit" => "application/x-stuffit",
"snd" => "audio/basic",
"spc" => "application/x-pkcs7-certificates",
"spl" => "application/futuresplash",
"src" => "application/x-wais-source",
"sst" => "application/vnd.ms-pkicertstore",
"stl" => "application/vnd.ms-pkistl",
"stm" => "text/html",
"svg" => "image/svg+xml",
"sv4cpio" => "application/x-sv4cpio",
"sv4crc" => "application/x-sv4crc",
"t" => "application/x-troff",
"tar" => "application/x-tar",
"tcl" => "application/x-tcl",
"tex" => "application/x-tex",
"texi" => "application/x-texinfo",
"texinfo" => "application/x-texinfo",
"tgz" => "application/x-compressed",
"tif" => "image/tiff",
"tiff" => "image/tiff",
"tr" => "application/x-troff",
"trm" => "application/x-msterminal",
"tsv" => "text/tab-separated-values",
"txt" => "text/plain",
"uls" => "text/iuls",
"ustar" => "application/x-ustar",
"vcf" => "text/x-vcard",
"vrml" => "x-world/x-vrml",
"wav" => "audio/x-wav",
"wcm" => "application/vnd.ms-works",
"wdb" => "application/vnd.ms-works",
"wks" => "application/vnd.ms-works",
"wmf" => "application/x-msmetafile",
"wps" => "application/vnd.ms-works",
"wri" => "application/x-mswrite",
"wrl" => "x-world/x-vrml",
"wrz" => "x-world/x-vrml",
"xaf" => "x-world/x-vrml",
"xbm" => "image/x-xbitmap",
"xla" => "application/vnd.ms-excel",
"xlc" => "application/vnd.ms-excel",
"xlm" => "application/vnd.ms-excel",
"xls" => "application/vnd.ms-excel",
"xlt" => "application/vnd.ms-excel",
"xlw" => "application/vnd.ms-excel",
"xof" => "x-world/x-vrml",
"xpm" => "image/x-xpixmap",
"xwd" => "image/x-xwindowdump",
"z" => "application/x-compress",
"zip" => "application/zip");

return (key_exists($ext, $arr))?$arr[$ext]:'application/octet-stream';

}
//stolen from the web
function create_thumbnail_otf($image,$max_w=0,$max_h=0) {
$image_file = $image;
$MAX_WIDTH  = $max_w;
$MAX_HEIGHT = $max_h;
global $img;    


$image_path = $image_file;

$img = null;
$ext = strtolower(end(explode('.', $image_path)));
if ($ext == 'jpg' || $ext == 'jpeg')
{
  $img = @imagecreatefromjpeg($image_path);
}
else if ($ext == 'png')
{
    //fix pngs
  $img = imagecreatefromstring(file_get_contents($image_path));
  #$img = @imagecreatefrompng($image_path);
}
else if ($ext == 'gif')
{
  $img = @imagecreatefromgif($image_path);
}

if ($img)
{
  $width = imagesx($img);
  $height = imagesy($img);
  if($MAX_WIDTH==0) {
      $MAX_WIDTH=$width;
  }
  if($MAX_HEIGHT==0) {
      $MAX_HEIGHT=$height;
  }
  $scale = min($MAX_WIDTH/$width, $MAX_HEIGHT/$height);
  if ($scale < 1)
  {
    $new_width = floor($scale*$width);
    $new_height = floor($scale*$height);
    $tmp_img = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($tmp_img, $img, 0, 0, 0, 0,$new_width, $new_height, $width, $height);
    imagedestroy($img);
    $img = $tmp_img;        
  }    
}
if (!$img)
{
    return false;
}
header("Content-type: image/jpeg");
imagejpeg($img,'',500); 
}
function binary_multiples($size, $praefix=true, $short= true)
{
    if($praefix === true)
    {
        if($short === true)
        {
            $norm = array('B', 'kB', 'MB', 'GB', 'TB', 'PB','EB', 'ZB', 'YB');
        }
    else
    {
        $norm = array('Byte',
            'Kilobyte',
            'Megabyte',
            'Gigabyte',
            'Terabyte',
            'Petabyte',
            'Exabyte',
            'Zettabyte',
            'Yottabyte'
        );
    }
    $factor = 1000;
    }
    else
    {
    if($short === true)
    {
        $norm = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB','EiB', 'ZiB', 'YiB');
    }
    else
    {
    $norm = array('Byte',
        'Kibibyte',
        'Mebibyte',
        'Gibibyte',
        'Tebibyte',
        'Pebibyte',
        'Exbibyte',
        'Zebibyte',
        'Yobibyte'
        );
    }
    $factor = 1024;
    }
    $count = count($norm) -1;
    $x = 0;
    while ($size >= $factor && $x < $count)
    {
    $size /= $factor;
    $x++;
    }
    $size = sprintf("%01.2f", $size) . ' ' . $norm[$x];
    return $size;
}
