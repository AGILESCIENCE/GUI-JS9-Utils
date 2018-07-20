<?php

include 'regionCatalog.php';
include 'common.php';
include 'conf.php';

/**
For test:
  AGILE
    php regionCatalogForJS9Converter.php 2agl_cat_4_7_18.reg  0 galactic 92.4053 -10.3623 20
  FERMI
    php regionCatalogForJS9Converter.php 3FGL_gll_psc_v14.reg 1 fk5 92.4053 -10.3623 15
    php regionCatalogForJS9Converter.php 3FGL_gll_psc_v14_ell.reg 1 fk5 92.4053 -10.3623 15
*/

if (defined('STDIN')) {
  $regionCatalogFilePath = $argv[1];
  $format = $argv[2];
  $wcssys = $argv[3];
  $sourceL = $argv[4];
  $sourceB = $argv[5];
  $degreeDistance = $argv[6];
} else {
  $regionCatalogFilePath = $_GET['$regionCatalogFilePath'];
  $format = $_GET['format'];
  $wcssys = $_GET['wcssys'];
  $sourceL = $_GET['sourceL'];
  $sourceB = $_GET['sourceB'];
  $degreeDistance = $_GET['degreeDistance'];
}


if($format == RegionCatalogFormat::AGILE)
  $catalog = new AgileRegionCatalog($regionCatalogFilePath,$wcssys);
else if($format == RegionCatalogFormat::FERMI)
  $catalog = new FermiRegionCatalog($regionCatalogFilePath,$wcssys);
else
  responseError(500, 'Unkown catalog format!! Please use 0 (=AGILE) or 1 (=FERMI)');

if($wcssys === "fk5")
  $sourceCoords = gal2eq(array($sourceL, $sourceB));
else
  $sourceCoords = array($sourceL, $sourceB);


$catalog->extractRegions();

$catalog->filterRegionsBySphericalDistanceFromSource($sourceCoords, $degreeDistance);

$catalog->sanitizeRegionsForJS9();
#print_r($catalog->regions);

$catalog->addRegionsJSONOpts();
#print_r($catalog->regions);



// should be written in tmp/
$fn = split("/",$regionCatalogFilePath);
$fn = split("\.", end($fn));

$outfilename = $fn[0]."_".$outfilenameSuffix;
$outfilename = "tmp/".$outfilename;
$catalog->writeJS9RegionFile($outfilename);




header('Content-Type: application/json');
echo json_encode(array('regionFileName' => $outfilename));

?>
