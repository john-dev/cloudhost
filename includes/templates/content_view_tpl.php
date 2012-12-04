
   <? if($res) { ?>
    <header id="header">

    <h2><? echo $res['name']." (".binary_multiples($res['filesize']).")";?></h2>
    <a class="embed" href="<? echo $res['download_url']; ?>">Download</a>
    <?/*<a class="embed" href="<? echo $res['content_url']; ?>">Direct</a>*/?>
    </header>
    
    <section id="content">
      <img alt="<? echo $res['name'];?>" src="<? echo $res['thumbnail_url']; ?>">
    </section>
   <? } else { ?>
    
    <header id="header">
  

    <h2>file does not exist</h2>

    
    </header>
    
    <section id="content">
    
    </section>
    <? } ?>

