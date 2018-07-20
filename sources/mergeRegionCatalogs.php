<?php

include 'regionCatalog.php';
include 'common.php';
include 'conf.php';

/**
Input:
*/
/*
global color=red
fk5;point(   0.0377, 65.7517)# point=cross text={3FGL J0000.1+6545}
fk5;point(   0.0612,-37.6484)# point=cross text={3FGL J0000.2-3738}
fk5;point(   0.2535, 63.2440)# point=cross text={3FGL J0001.0+6314}
fk5;point(   0.3209, -7.8159)# point=cross text={3FGL J0001.2-0748}

global color=yellow
fk5;ellipse(   0.0377, 65.7517,  0.1019,  0.0780,131.0300)
fk5;ellipse(   0.0612,-37.6484,  0.0731,  0.0676,  1.4500)
fk5;ellipse(   0.2535, 63.2440,  0.2475,  0.1598, 24.6800)
*/

/**
Output:
*/
/*
global
fk5:point(0.0377, 65.7517) # point=cross color=yellow
fk5:ellipse(0.0377, 65.7517,0.1019,0.0780,131.0300) # color=red text={3FGL J0000.1+6545}
*/

if (defined('STDIN')) {
  $fermiCatalog1NameWithTextProperty = $argv[1];
  $fermiCatalog2Name = $argv[2];
} else {
  $fermiCatalog1NameWithTextProperty = $_GET['fermiCatalog1NameWithTextProperty'];
  $fermiCatalog2Name = $_GET['fermiCatalog2Name'];
}

$catalog1 = new FermiRegionCatalog($fermiCatalog1NameWithTextProperty,"");
$catalog2 = new FermiRegionCatalog($fermiCatalog2Name,"");



/**
  Move region catalaog1 global property like 'global color=red' after each hashtag
  like 'fk5;point( 330.6872, 42.2835)# point=cross text={3FGL J2202.7+4217} color=red'
*/
$catalog1->catalogContent = split("\n",$catalog1->catalogContent);
foreach ($catalog1->catalogContent as $key => $row)
{
  if ($key==0)
    $catalog1->catalogContent[$key] = "global";
  else if(!empty($row))
    $catalog1->catalogContent[$key] = $row." color=red";
}

/**
  Move region catalaog2 global property like 'global color=red' after each hashtag
  like 'fk5;point( 330.6872, 42.2835)# point=cross text={3FGL J2202.7+4217} color=red'
*/
$catalog2->catalogContent =  split("\n",$catalog2->catalogContent);
foreach ($catalog2->catalogContent as $key => $row)
{
  if ( $key == 0 )
    $catalog2->catalogContent[$key] = "global";
  else if(!empty($row))
    $catalog2->catalogContent[$key] = $row."# color=yellow";
}

/*
print_r($catalog1->catalogContent);
print_r($catalog2->catalogContent);
print_r("\n\n\n*******\n\n\n");
*/


/**
  extract coords from region catalog 1
*/
$coords1 = array();
foreach ($catalog1->catalogContent as $key => $row){
  if($key>0)
    $coords1[$key] = $catalog1->extractShapeParamsFromRegionString($row);
}


/**
  extract coords from region catalog 2
*/
$coords2 = array();
foreach ($catalog2->catalogContent as $key => $row){
  if($key>0)
    $coords2[$key] = $catalog2->extractShapeParamsFromRegionString($row);
}



/**
Per ogni coord del catalogo 1 vado a vedere se esiste la corrispettiva coord nel
catalogo2. In tal caso sposto la proprietà text dalla region del catalogo 1
alla corrispettiva region del catalogo 2
*/

foreach ($coords1 as $key1 => $coord1Arr){
  foreach ($coords2 as $key2 => $coord2Arr){

    if ( count($coord1Arr) > 1 &&  count($coord2Arr) > 1 )
    {

      if ( $coord1Arr[0]==$coord2Arr[0] && $coord1Arr[1]==$coord2Arr[1])
      {

        // find & remove text label from region catalog 1
        $pos1 = strpos($catalog1->catalogContent[$key1], "text=");
        $catalog1->catalogContent[$key1] = str_replace("text=","",$catalog1->catalogContent[$key1]);


        // extracting text property
        $pos1 = strpos($catalog1->catalogContent[$key1], "{");
        $pos2 = strpos($catalog1->catalogContent[$key1], "}");
        $textValue = substr($catalog1->catalogContent[$key1],$pos1+1,$pos2-$pos1-1);


        // removing text value from from region catalog 1
        $catalog1->catalogContent[$key1] = substr($catalog1->catalogContent[$key1],0,$pos1).substr($catalog1->catalogContent[$key1],$pos2+1,strlen($catalog1->catalogContent[$key1]));


        // add text property and value to region catalog 2
        $catalog2->catalogContent[$key2] = $catalog2->catalogContent[$key2]." text={".$textValue."}";

        

      }
    }
  }
}


/**
  Merging of the catalogs
*/
$mergedCatalog = "global\n";

foreach ($catalog2->catalogContent as $key => $row){
  if($key>0 && !empty($row))
    $mergedCatalog .= $row."\n";
}
foreach ($catalog1->catalogContent as $key => $row){
  if($key>0 && !empty($row))
    $mergedCatalog .= $row."\n";
}


// output

$n1 = split("/",$fermiCatalog1NameWithTextProperty);
$n1 = split("\.", end($n1));



$n2 = split("/",$fermiCatalog2Name);
$n2 = split("\.",end($n2));

$outfilename = "tmp/".$n1[0]."_".$n2[1]."_fermiCatalogsmerged.reg";



// Write
$mergedFile = fopen($outfilename, "w");
if ( !$mergedFile ) {
  responseError(500, 'File '.$outfilename.' open failed');
}

// Add content
fwrite($mergedFile,$mergedCatalog);

// Close file
fclose($mergedFile);

header('Content-Type: application/json');
echo json_encode(array('mergedCatalogFileName' => $outfilename));

/**
DEBUG
*/
/*
$myfile = fopen($outfilename, "r");
if ( !$myfile ) {
  responseError(500, 'File open failed: '.$outfilename);
}
else {

  $content = fread($myfile,filesize($outfilename));
  fclose($myfile);
  print_r("\n\n".$content."\n\n");
}
*/

?>
