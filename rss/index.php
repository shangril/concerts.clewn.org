<?php
error_reporting(0);
header('Mime-type: application/rss+xml');
?><?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
 <title>Concerts Musique Libre</title>
 <description>Concert announcements, provided by the Musique Libre nonprofit organisation</description>
 <link>http://concerts.musique-libre.org</link>
 
<?php

$filez=array_diff(scandir('../d/'),array('..', '.', '.htaccess'));

foreach ($filez as $file){
	$dat=unserialize(file_get_contents('../d/'.$file));
	$event_name=$dat['event_name'];
	$event_guid=str_replace('.txt', '', $file);
	$event_date=date(DATE_RSS, $event_guid);
?>
 
 <item>
  <title><?php echo htmlentities($event_name);?></title>
  <description><?php echo htmlentities($event_name);?></description>
  <link>http://concerts.musique-libre.org/?nod=<?php echo floatval($event_guid);?></link>
  <guid isPermaLink="true"><?php echo floatval($event_guid);?></guid>
  <pubDate><?php echo htmlentities($event_date);?></pubDate>
  <icbm:latitude><?php echo htmlentities($dat['lat']);?></icbm:latitude>
  <icbm:longitude><?php echo htmlentities($dat['lon']);?></icbm:longitude>
 </item>

<?php

}
?>

</channel>
</rss>
