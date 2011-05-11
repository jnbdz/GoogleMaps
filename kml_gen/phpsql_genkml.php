<?php
require('class/DB.class.php');

// Selects all the rows in the markers table.

$viewport = explode(',', $_GET['BBOX'], 4);

while($viewport[0] > $viewport[2])
	$viewport[0] -= 360;
	
$north = min(90, (float) $viewport[3]); //44.27667127377516
$south = max(-90, (float) $viewport[1]); //30.372875188118016
$east = min(180, (float) $viewport[2]); //-92.900390625
$west = min(-180, (float) $viewport[0]); //-128.056640625

// SELECT * FROM marker WHERE (lat BETWEEN 30.372875188118016 AND 44.27667127377516) AND (lng BETWEEN -128.056640625 AND -92.900390625) LIMIT 30

$query = "SELECT * FROM `marker` WHERE (`lat` BETWEEN ? AND ?) AND (`lng` BETWEEN ? AND ?) LIMIT 30";

$prepare = DB::$pdo_sqlite->prepare($query);
$prepare->execute(array($south, $north, $west, $east));
$sql_fetch = $prepare->fetchAll();

if($_GET['kjson'] === 'true'){
	
	echo json_encode($sql_fetch);
	
	die();
}

// Creates the Document.
$dom = new DOMDocument('1.0', 'UTF-8');

// Creates the root KML element and appends it to the root document.
$node = $dom->createElementNS('http://earth.google.com/kml/2.1', 'kml');
$parNode = $dom->appendChild($node);

// Creates a KML Document element and append it to the KML element.
$dnode = $dom->createElement('Document');
$docNode = $parNode->appendChild($dnode);

// Creates the two Style elements, one for restaurant and one for bar, and append the elements to the Document element.
$restStyleNode = $dom->createElement('Style');
$restStyleNode->setAttribute('id', 'restaurantStyle');
$restIconstyleNode = $dom->createElement('IconStyle');
$restIconstyleNode->setAttribute('id', 'restaurantIcon');
$restIconNode = $dom->createElement('Icon');
$restHref = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon63.png');
$restIconNode->appendChild($restHref);
$restIconstyleNode->appendChild($restIconNode);
$restStyleNode->appendChild($restIconstyleNode);
$docNode->appendChild($restStyleNode);

$barStyleNode = $dom->createElement('Style');
$barStyleNode->setAttribute('id', 'barStyle');
$barIconstyleNode = $dom->createElement('IconStyle');
$barIconstyleNode->setAttribute('id', 'barIcon');
$barIconNode = $dom->createElement('Icon');
$barHref = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon27.png');
$barIconNode->appendChild($barHref);
$barIconstyleNode->appendChild($barIconNode);
$barStyleNode->appendChild($barIconstyleNode);
$docNode->appendChild($barStyleNode);

// Iterates through the MySQL results, creating one Placemark for each row.
foreach($sql_fetch as $row)
{
  // Creates a Placemark and append it to the Document.
  
  $node = $dom->createElement('Placemark');
  $placeNode = $docNode->appendChild($node);
  
  // Creates an id attribute and assign it the value of id column.
  $placeNode->setAttribute('id', 'placemark' . $row['id']);

  // Create name, and description elements and assigns them the values of the name and address columns from the results.
  $nameNode = $dom->createElement('name',htmlentities($row['name']));
  $placeNode->appendChild($nameNode);
  $descNode = $dom->createElement('description', $row['address']);
  $placeNode->appendChild($descNode);
  $styleUrl = $dom->createElement('styleUrl', '#' . $row['type'] . 'Style');
  $placeNode->appendChild($styleUrl);

  // Creates a Point element.
  $pointNode = $dom->createElement('Point');
  $placeNode->appendChild($pointNode);

  // Creates a coordinates element and gives it the value of the lng and lat columns from the results.
  $coorStr = $row['lng'] . ','  . $row['lat'];
  $coorNode = $dom->createElement('coordinates', $coorStr);
  $pointNode->appendChild($coorNode);
}

$kmlOutput = $dom->saveXML();
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>