<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <? if(isset($res)) {
        echo "<title>".$res['name']." // ".SYS_TITLE."</title>";
        echo '<meta property="og:title" content="'.$res['name'].' - '.SYS_TITLE.'" />';
        echo '<meta property="og:description" content="'.$res['name'].' - '.SYS_TITLE.'" />';
        echo '<meta property="og:site_name" content="'.$res['name'].' - '.SYS_TITLE.'" />';
        echo '<meta property="og:image:type" content="image/'.$res['ext'].'" />';
        echo '<meta property="og:image" content="'.$res['thumbnail_url'].'" />';
        echo '<meta property="og:image:width" content="250" />';
        echo '<meta property="og:image:height" content="150" />';
     } else {
        echo "<title>".SYS_TITLE."</title>";
     }
     ?>
     <link rel="stylesheet" href="/css/style.css" type="text/css" media="all" />
</head>
<body id="image">

