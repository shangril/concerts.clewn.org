<?php 
error_reporting(0);
session_start();
jailOldPosts(floatval(60*60*24*38));



$banner='';

if (isset($_GET['unset'])){
	session_unset();
	session_start();
}

if (isset($_SESSION[$_SERVER['REMOTE_ADDR']])){
			$_SESSION['lon']=$_SESSION[$_SERVER['REMOTE_ADDR']]['lon'];
			$_SESSION['lat']=$_SESSION[$_SERVER['REMOTE_ADDR']]['lat'];

}


if (isset($_GET['nod'])&&is_numeric($_GET['nod'])){
		$event_name="Sorry, this event has expired, has been deleted, or never existed";
		$event_lat=0;
		$event_lon=0;
		if (file_exists('./d/'.$_GET['nod'].'.txt')){
			$dat=unserialize(file_get_contents('./d/'.$_GET['nod'].'.txt'));
			$event_name=$dat['event_name'];
			$event_lat=$dat['lat'];
			$event_lon=$dat['lon'];
			
		}
	
}


if (!isset($_SESSION['display'])){
	$_SESSION['display']=10;
}
if (isset($_GET['display'])){
	
	$_SESSION['display']=intval($_GET['display']);

	
	}

if (!isset($_SESSION['sort'])){
	$_SESSION['sort']='distance';
}
if (isset($_GET['sort'])){
	
	$_SESSION['sort']=$_GET['sort'];

	
	}






if (!isset($_SESSION['lat'])&&!isset($_SESSION['lon'])){
		$coord=getGeoIP($_SERVER['REMOTE_ADDR']);
		$_SESSION['lat']=$coord['lat'];
		$_SESSION['lon']=$coord['lon'];


	}

if (isset($_GET['lat'])&&isset($_GET['lon'])){
	$_SESSION['lat']=floatval($_GET['lat']);
	$_SESSION['lon']=floatval($_GET['lon']);
	}

if (isset($_GET['city'])){
	$_SESSION['cityset']=true;
	$apiurl='http://nominatim.openstreetmap.org/search';
	if (isset($_SESSION['lang'])){
		$apiurl.='?accept-language='.urlencode($_SESSION['lang'].'-'.strtoupper($_SESSION['lang']).','.$_SESSION['lang']).'&';
	}
	$apiurl.='q='.urlencode($_GET['city']);
	$apiurl.='&format=xml';
	$apiurl.='&polygon=1';
	$apiurl.='&addressdetails=1';
	$apiurl.='&email=contact@musique-libre.org';

	$lon=0;
	$lat=0;
	
	$apiresult=file_get_contents($apiurl);


	if ($apiresult){
			$dom = new DOMDocument();
		   $dom->loadXML($apiresult);
		   //$dom->preserveWhiteSpace=false;
		   $item = $dom;
		   
		   if ($item->getElementsByTagName('place')->item(0)!==null) {
			   //$item=$itemstop->item(0)->getElementsByTagName('addressparts')->item(0);
			   $lon = $item->getElementsByTagName('place')->item(0)->getAttribute('lon');
				$lat = $item->getElementsByTagName('place')->item(0)->getAttribute('lat');
				
				$_SESSION['lon']=$lon;
				$_SESSION['lat']=$lat;
				$_SESSION[$_SERVER['REMOTE_ADDR']]['lon']=$lon;
				$_SESSION[$_SERVER['REMOTE_ADDR']]['lat']=$lat;
			}
			else {
				$banner=trans('Sorry, the city indicated could not be found');
		
			}
	}
	else {
		$banner=trans('Sorry, the city indicated could not be found');
		
	}
	
}



if (isset($_GET['command'])&&$_GET['command']=='post'){
	//file upload
	$banner=trans('Sorry, there were an error with your uploaded file');
	
	$uid=microtime(true);
	if(mime_content_type($_FILES['poster']['tmp_name'])==='image/jpeg'){
		if(move_uploaded_file($_FILES['poster']['tmp_name'], './post/'.$uid.'.jpg')){
			$dat=Array();
			$dat['event_name']=$_POST['event_name'];
			$dat['lon']=$_SESSION['lon'];
			$dat['lat']=$_SESSION['lat'];
			if (file_put_contents('./d/'.$uid.'.txt', serialize($dat))){
				$banner=trans('Upload has been completed successfully').'<br/>'
				.trans('Please review your image to make sure it is ok: ')
				.'<br/><img src="./post/'.$uid.'.jpg" style="width:25%;"/><br/><em>'
				.trans('If there is a problem you can still ')
				.'<a href="./?emergency_delete=true">'
				.trans('delete it now').'</a>. <br/><span style="font-size:110%">'
				.trans('This is your only and last chance to delete it!').'</span></em>';	
			}
			$_SESSION['emergency_delete']=$uid;
		}
	}
}
if (isset($_GET['emergency_delete'])){
	if (unlink ('./d/'.$_SESSION['emergency_delete'].'.txt')&&unlink ('./post/'.$_SESSION['emergency_delete'].'.jpg')){
		
		$banner=trans('Your post has been deleted.');
	}
	else {
		$banner=trans('There were a problem and your post could not be deleted. Please contact the support as soon as possible at our chatroom, using the link at the top of the page');
	}
}
if (isset($_GET['lang'])){
	$_SESSION['lang']=$_GET['lang'];
	
}
if (!isset($_GET['lang'])&&!isset($_SESSION['lang'])){
	$_SESSION['lang']='fr';
}

function trans($message){
	if (!strstr($_SESSION['lang'], 'fr')){
	
		return $message;
	}
	if ($message==='Event and concert announcements from Musique-Libre.org'){
		return 'Les annnonces concerts de Musique-Libre.org';
	}
	if ($message==='Event and concert announcements from <a href="http://musique-libre.org">Musique-Libre.org</a>'){
		return 'Les annnonces concerts de <a href="http://musique-libre.org">Musique-Libre.org</a>';
	}

	if ($message===' Post an event'){
		return ' Poster un événement';
	}
	if ($message==='Showing concerts near your area: '){
		return 'Affichage des concerts près de chez vous : ';
	}
	if ($message==='Change city'){
		return 'Changer la ville';
	}
	if ($message==='Event place: '){
		return 'Lieu de l\'évènement : ';
	}
	if ($message==='Change'){
		return 'Changer';
	}
	
	if ($message==='Event Name: '){
		return 'Nom de l\'événement : ';
	}
	if ($message==='Picture of the flyer: '){
		return 'Photo du flyer: ';
	}
	if ($message==='Show on map'){
		return 'Voir sur la carte';
	}
	if ($message==='events currently listed'){
		return 'événements actuellement listés';
	}
	if ($message==='event currently listed'){
		return 'événement actuellement listé';
	}
	if ($message==='sort by'){
		return 'tri par';
	}
	if ($message==='A problem or question? '){
		return 'Une question, un problème ? ';
	}
	if ($message==='Join the nonprofit\'s live chat. '){
		return 'rejoignez le salon de discussion de l\'asso. ';
	}
	if ($message==='Non-profit volunteer organisation, editor of the site '){
		return 'Association à but non lucratif, éditrice du site ';
	}
	if ($message==='Sorry, there were an error with your uploaded file'){
		return ('Désolé, le téléversement de votre fichier a rencontré une erreur');
	}
	if ($message==='Forget my location and number of items displayed'){
		return ('Oublier ma localisation et le nombre d\'événements à afficher');
	}
	if ($message==='Upload has been completed successfully'){
		return ('Téléversement correctement effectué');
	}
	if ($message==='Please review your image to make sure it is ok: '){
		return ('Merci de vérifier votre image pour être sûr qu\'il n\'y a pas de problème : ');
	}
	if ($message==='If there is a problem you can still '){
		return ('En cas de soucis vous pouvez encore ');
	}
	if ($message==='delete it now'){
		return ('la supprimer immédiatement');
	}
	if ($message==='This is your only and last chance to delete it!'){
		return ('C\'est votre seule et unique chance de la supprimer !');
	}
	if ($message==='Your post has been deleted.'){
		return ('Votre annonce a été supprimée.');
	}
	if ($message==='There were a problem and your post could not be deleted. Please contact the support as soon as possible at our chatroom, using the link at the top of the page'){
		return ('Il y a eu un problème et votre annonce n\'a pas pu être effacée. Merci de contacter le support dès que possible via le lien vers notre salon de discussion en haut de la page');
	}
	if ($message==='No more posts currently available. Please note that posts are automatically deleted after 38 days'){
		return ('Pas d\'autre événement listé. Merci de noter que les événements sont automatiquement effacés après 38 jours');
	}
	if ($message==='distance'){
		return ('distance');
	}
	if ($message==='post date'){
		return ('date de publication');
	}
	if ($message==='Update your location :'){
		return ('Mettre à jour votre localisation');
	}
	if ($message==='Sorry, the city indicated could not be found'){
		return ('Désolé, la ville que vous avez indiqué n\'a pas pu être trouvée');
	}
	if ($message==='enter city name or address: '){
		return ('Entrez un nom de ville ou une adresse : ');
	}
	if ($message===' -or- Update your location (GPS-style decimals) '){
		return (' -ou- Mettre à jour votre localisation (coordonnées GPS) ');
	}
	if ($message==='to current year'){
		return ('jusqu\'à l\'année en cours');
		
	}
	if ($message==='Any event post is done under the sole responsibility of the individual poster'){
		return ('Toute publication d\'un événement est faite sous la seule responsabilité de la personne qui le publie');
	}

}
?><!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.0.1/dist/leaflet.js"></script>
<script>
var isPosting=false;

</script>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<style>
td  {
	border: solid 1px;
}

</style>
<?php 


if (isset($_GET['nod'])&&is_numeric($_GET['nod'])){

echo '<title>'.htmlspecialchars($event_name).' - Concerts.musique-libre.org</title>';
echo '<meta name="description" value="'.htmlspecialchars($event_name).' - Concerts.musique-libre.org - '.trans('Event and concert announcements from Musique-Libre.org').'" />';

?>
<meta name="ICBM" content="<?php echo htmlentities($event_lat);?>, <?php echo htmlentities($event_lon);?>"/>
<meta name="geo.position" content="<?php echo htmlentities($event_lat);?>;<?php echo htmlentities($event_lon);?>"/>

<?php
}
else {
?>


<title>Concerts Musique-Libre - <?php echo trans('Event and concert announcements from Musique-Libre.org'); ?></title>
<meta name="description" value="<?php echo trans('Event and concert announcements from Musique-Libre.org'); ?>" />

<?php

}

?>

</head>
<body>
<span style="float:left;font-size:78%;"><a href="./?lang=fr">Français</a> <a href="./?lang=en">English</a></span>
<span style="float:right;" id="invite"><?php echo trans('A problem or question? '); ?> <a href="http://www.dogmazic.net/irc"><?php echo trans('Join the nonprofit\'s live chat. '); ?></a>
</span>


<br/>
<?php
if ($banner!==''){
	?>
	<div style="border:solid 1px;border-radius:5px;" id="banner">
		<a style="text-align:right;border:solid 1px;font-size:320%;" href="./" onclick="document.getElementById('banner').style.display='none';">X</a>
		<br/>
		<strong><h1 style="text-align:center;"><?php echo $banner;?></h1></strong>
	
	
	</div>
	
	<?php
	
}
?>

<h1 style="clear:both;float:none;text-align:center;margin-top:0%;padding-top:0%;"><a href="./">Concerts.musique-libre.org</a></h1>
<div style="clear:both;text-align:center;"><strong><?php echo trans('Event and concert announcements from <a href="http://musique-libre.org">Musique-Libre.org</a>');?></strong></div>
<div style="clear:both;text-align:center;"><em><?php echo trans('Non-profit volunteer organisation, editor of the site ');?><a href="http://www.dogmazic.net">Dogmazic.net</a></em></div>

<?php
$mapzoom=1;
$maplon=0;
$maplat=0;
$eventcount=0;
if (isset($_GET['nod'])&&is_numeric($_GET['nod'])){
	$mapzoom=11;
	$maplat=$event_lat;
	$maplon=$event_lon;
	
	echo '<span style="text-align:center;width:100%;">';
	echo '<h1>'.htmlspecialchars($event_name).'</h1>';

	if (file_exists('./post/'.$_GET['nod'].'.jpg')){
		echo '<h1 style="text-align:center;"><a  style="margin:auto;text-align:center;width:100%;"  title="'.htmlspecialchars($event_name).'" href="./post/'.$_GET['nod'].'.jpg"><img style="margin:auto;text-align:center;" alt="'.htmlspecialchars($event_name).'" class="postimg" src="./post/'.$_GET['nod'].'.jpg"/></a></h1>';
	}
	echo '</span>';
}
//Here comes the leaflet map section
?>
<a href="javascript:void(0);" onclick="this.style.display='none';document.getElementById('map').style.display='block';showMap();"><?php

if (isset($_GET['nod'])) {
	
	echo '<h2 style="text-align:center;">';
	
}

 echo trans('Show on map');
 
if (isset($_GET['nod'])) {
	
	echo '</h2>';
	
}
 
 
 
 ?></a>
<span style="text-align:center;">
<div id="map" class="map" style="display:none;margin-left:auto;margin-right:auto;text-align:center;height:212px;"></div>
<script>
function showMap(){
var map = L.map('map').setView([<?php echo floatval($maplat); ?>, <?php echo floatval($maplon);?>], <?php echo floatval($mapzoom);?>);

L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

<?php 
//foreach event : start
$posts=Array();

$filez=array_diff(scandir('./d/'),array('..', '.', '.htaccess'));

$eventcount=count($filez);

foreach ($filez as $file){
	
	$dat=unserialize(file_get_contents('./d/'.$file));
	
		$aneventlon=floatval($dat['lon']);
		
		$aneventlat=floatval($dat['lat']);
		
		$aneventname=$dat['event_name'];
		
		$aneventnod = floatval(str_replace('.txt','',$file));


?>

L.marker([<?php echo floatval($aneventlat);?>, <?php echo floatval($aneventlon);?>]).addTo(map)
    .bindPopup('<?php echo '<a href="./?nod='.floatval($aneventnod).'" title="'
    .str_replace("'","\\'", htmlspecialchars($aneventname)).'">'
    .str_replace("'","\\'", htmlspecialchars($aneventname)).'</a>'; ?>');
<?php
//foreach event : end
}

?>
}//function js showmap
</script>
<span style="text-align:center;font-size:70%;"><?php


if ($eventcount!==1){

	echo intval($eventcount).' '.trans('events currently listed'); 
 
	}
 else {
	echo intval($eventcount).' '.trans('event currently listed'); 
  
	 
 }
 
 
 
 ?></span>
</span>
<br style="clear;both;"/>

<?php //end of the leaflet map section?>
<h2 style="text-align:right;"><a style="color:#373737;background-color:#909090;border-radius:5px;padding:3px;" href="javascript:void(0);" onclick="document.getElementById('create').style.display='block';document.getElementById('postplace').innerHTML=document.getElementById('town').innerHTML;isPosting=true;"><?php echo trans(' Post an event');?></a></h2>



<form style="display:none;" enctype="multipart/form-data" id="create" action="./?command=post" method="post"><?php echo trans('Event Name: ');?>
<input type="text" name="event_name" id="event_name" size="40" 
<?php
if (isset($_GET['eventname'])){
		echo 'value="'.htmlspecialchars($_GET['eventname']).'"';
}

?>
/><br/>
<br/><em style="font-size:110%;"><?php


echo trans('Event place: ');



?>
<span id="postplace"></span>

 <a href="javascript:void(0);" onClick="document.getElementById('create').style.display='none';isPosting=true;changeloc();"><?php echo trans('Change');?></a>
</em><br/>
<?php echo trans('Picture of the flyer: ');?><input type="file" accept="image/jpeg" name="poster"/><br/>
<input type="submit" onclick="document.getElementById('create').style.display='none';document.getElementById('uploading').innerHTML='Uploading, please wait...';"/></form>
<div id="uploading"></div>
<div style="width:100%;">
<script>
function changeloc(){
	var lat=document.getElementById('lat').innerHTML;
	var lon=document.getElementById('lon').innerHTML;
	var town=document.getElementById('town').innerHTML;
	
	document.getElementById('loc').innerHTML='';
	var ret='';
	ret+='<form style="display.inline;" method="get" action="';

	
	ret+='">';

	if (isPosting==true){
		ret+='<input type="hidden" name="isPosting" value="true"/>';
		ret+='<input type="hidden" name="eventname" id="posteventname" value=""/>';

	}

	ret+='<?php echo str_replace ("'", "\\'", trans('Update your location:')); ?><br/><?php echo str_replace ("'", "\\'", trans('enter city name or address: '));?><br/>';
	ret+='<input type="text" size="28" name="city" value=""/><br/><span style="font-size:100%">ex: <em>Paris</em> or <em>Bouches-du-Rhône</em> or <em>Rue de la République, Lyon</em></span>';
	ret+='<br/><input type="submit"/>';
	ret+='</form>';

	ret+='<br/><form style="display.inline;" method="get" action="'
	
	ret+='">';
	
	if (isPosting==true){
		ret+='<input type="hidden" name="isPosting" value="true"/>';
		ret+='<input type="hidden" name="eventname" id="posteventname" value=""/>';

	}
	ret+='<?php echo str_replace ("'", "\\'", trans(' -or- Update your location (GPS-style decimals) '))?>';
	ret+='lat : <input type="text" size="8" name="lat" value="'+lat+'"/>';
	ret+='lon : <input type="text" size="8" name="lon" value="'+lon+'"/>';
	ret+='<input type="submit"/>';
	ret+='</form>';
	document.getElementById('loc').innerHTML=ret;
	if (isPosting){
		document.getElementById('posteventname').value=document.getElementById('event_name').value;
		
	}
	
	
}

</script>

 
		<span id="loc" style="border:solid 1px orange;border-radius:5px;padding:2px;color:#909090;background-color:#F0F0F0;text-align:left;float:left;font-size:118%;"><?php echo trans('Showing concerts near your area: ');?> 
		
<?php
if (!file_exists('./e')){
	//mkdir('./e');
}
$town='Unknown city';
if (false&&file_exists('./e/'.$_SESSION['lat'].'-'.$_SESSION['lon'].'.txt')){//cache can no longer be used since we now use dynamic translation
	//for api results
	$town=file_get_contents('./e/'.$_SESSION['lat'].'-'.$_SESSION['lon'].'.txt');
	
}
	else {

	$apiurl='http://nominatim.openstreetmap.org/reverse?';
	$apiurl.='format=xml';
	if (isset($_SESSION['lang'])){
		$apiurl.='&accept-language='.urlencode($_SESSION['lang'].'-'.strtoupper($_SESSION['lang']).','.$_SESSION['lang']).'&';
	}
	$apiurl.='&lat='.urlencode($_SESSION['lat']);
	$apiurl.='&lon='.urlencode($_SESSION['lon']);
	$apiurl.='&zoom=18';
	$apiurl.='&addressdetails=1';
	$apiurl.='&email=contact@musique-libre.org';

	$apiresult=file_get_contents($apiurl);


	if ($apiresult){
			$dom = new DOMDocument();
		   $dom->loadXML($apiresult);
		   //$dom->preserveWhiteSpace=false;
		   $item = $dom->getElementsByTagName('addressparts');
		   //$item=$itemstop->item(0)->getElementsByTagName('addressparts')->item(0);
		   $city='';
		   $hamlet='';
		   $state='';
		   $country='';
		   $city = $item->item(0)->getElementsByTagName('city')->item(0)->nodeValue;
			$hamlet = $item->item(0)->getElementsByTagName('town')->item(0)->nodeValue;
			$village = $item->item(0)->getElementsByTagName('village')->item(0)->nodeValue;
			$state = $item->item(0)->getElementsByTagName('state')->item(0)->nodeValue;
			$country = $item->item(0)->getElementsByTagName('country')->item(0)->nodeValue;
			$street = $item->item(0)->getElementsByTagName('road')->item(0)->nodeValue;
		  
		  if (isset($hamlet) && $hamlet!==''){
		  $city.=' '.$hamlet;
		 }
		 if (isset($village) && $village!==''){
		  $city.=' '.$village;
		 }
		  if (!isset($_SESSION['cityset'])){
			  $street='';
		  }
		  $town=$street.' '.$city.', '.$state.', '.$country;
		  //file_put_contents('./e/'.$_SESSION['lat'].'-'.$_SESSION['lon'].'.txt',$town);
	}
}
echo '<span id="town" style="font-size:125%;">'.htmlspecialchars($town).'</span><br/>';
?>		
		
		
		 	<span style="font-size:62%">GPS-style decimals : 
			<span id="lat"><?php echo htmlspecialchars($_SESSION['lat']);?></span>, 
			<span id="lon"><?php echo htmlspecialchars($_SESSION['lon']);?></span>
			</span><br/> 
			<a href="javascript:void(0);" id="change_city" onclick="changeloc();"><?php echo trans('Change city');?></a>
		</span>

		
		
</div>
<hr/>
<span style="text-align:right;float:right;"><?php echo trans('sort by'); ?> <a href="./?sort=distance"><?php echo trans('distance');?></a> / <a href="./?sort=postdate"><?php echo trans('post date');?></a></span>
<br style="margin-bottom:1%;clear:both;"/>
<?php
//this is the main display code 

//first, compute the distance of any nonjailed post

$posts=Array();


$filez=array_diff(scandir('./d/'),array('..', '.', '.htaccess'));

foreach ($filez as $file){
	$dat=unserialize(file_get_contents('./d/'.$file));
	
	if ($_SESSION['sort']==='distance'){
		
		$distance=sqrt(
						pow( floatval(floatval($dat['lon'])-floatval($_SESSION['lon']))
						
							,2)
						+
						pow( floatval(floatval($dat['lat'])-floatval($_SESSION['lat']))
						
							,2)
						);
		if (!isset($posts[$distance])){
			$posts[$distance]=Array();
		}


		array_push($posts[$distance], $file);
	}
	else {
		$postdate=floatval(str_replace('.txt','',$file));

		if (!isset($posts[$postdate])){
			$posts[$postdate]=Array();
		}
		array_push($posts[$postdate], $file);
	}
}

ksort($posts, SORT_NUMERIC);

if ($_SESSION['sort']!=='distance'){
	array_reverse($posts);
}
	
$i=0;
foreach ($posts as $posted){
	rsort($posted, SORT_NUMERIC);
	foreach ($posted as $post){
		
		$event_array=unserialize(file_get_contents('./d/'.$post));
		$event_name=$event_array['event_name'];
		echo '<div style="background-color:#333;border-radius: 5px;">';
		echo '<h2 style="text-align:center">'.htmlspecialchars($event_name).'</h2>';
		echo '<h1 style="text-align:center;color:transparent;">';
		echo '<a name="'.htmlspecialchars(intval($i+1)).'" href="./?nod='.htmlspecialchars(str_replace('.txt','',$post)).'" style="margin:auto;">';
		echo '<img class="postimg" src="./post/'.htmlspecialchars(str_replace('.txt', '.jpg', $post)).'" alt="Local event, node id : '.htmlspecialchars(str_replace('.txt','',$post)).'"/>';
		
		
		echo '</a>';
		
		
		echo '</h1>';
		echo '</div>';
		$i++;
		if ($i>=intval($_SESSION['display'])){
			break;
		}
	}
}

if ($i<count($posts)){

	echo '<div style="text-align:center;"><a href="?display='.htmlspecialchars(intval($_SESSION['display']*2)).'#'.htmlspecialchars(intval($_SESSION['display']+1)).'" style="text-align:center;font-size:180%;width:100%;">Show more...</a></div>';

}
else {
	
	echo '<br style="margin-bottom:5%;"/>'.trans('No more posts currently available. Please note that posts are automatically deleted after 38 days');
}

?>

<br/><br/>
 <a href="./?unset=session"><?php echo trans('Forget my location and number of items displayed');?></a> - <a href="./rss">RSS</a>

<br/>
<div>&copy; 2016 <?php echo trans('to current year');?> Association Musique Libre. CNIL: 1208661<br/> <?php echo trans('Any event post is done under the sole responsibility of the individual poster');?>
</div>
<script>

<?php if (isset($_GET['isPosting'])){ ?>
	document.getElementById('create').style.display='block';document.getElementById('postplace').innerHTML=document.getElementById('town').innerHTML;
	
<?php } ?>
</script>
</body>
</html><?php

function jailOldPosts($delay){
	$filez=array_diff(scandir('./d/'),array('..', '.', '.htaccess'));
	foreach ($filez as $file){
		if (!is_dir('./d/'.$file)){
			if (microtime(true)-floatval(str_replace('.txt','',$file))>$delay){
				rename('./d/'.$file, './jail/'.$file);
				rename('./post/'.str_replace('.txt', '.jpg',$file), './jail/'.str_replace('.txt', '.jpg',$file));
				
			}
		}
	}
}
function getGeoIP($IP){
	$ret=Array();
	$ret['lat']=42;
	$ret['lon']=5;
	
	$result=geoip_record_by_name($IP);
	
	$ret['lat']=$result['latitude'];
	$ret['lon']=$result['longitude'];
	
	
	return $ret;
}

?>
