<?php
error_reporting(0);
header('Mime-type: application/rss+xml');
?><?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0">
<channel>
 <title>Concerts.clewn.org</title>
 <description>Concert announcements, provided by the Clewn nonprofit project</description>
 <link>http://concerts.clewn.org</link>
 
<?php

$filez=array_diff(scandir('../d/'),array('..', '.', '.htaccess'));

foreach ($filez as $file){
	$dat=unserialize(file_get_contents('../d/'.$file));
	$event_name=$dat['event_name'];
	$event_guid=str_replace('.txt', '', $file);
	$event_date=date(DATE_RSS, $event_guid);
?>
 
 <item>
  <title><![CDATA[<?php echo htmlentities($event_name);?>]]></title>
  <description><![CDATA[<?php echo htmlentities($event_name);?>]]></description>
  <link>http://concerts.clewn.org/?nod=<?php echo floatval($event_guid);?></link>
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
