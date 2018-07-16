<?php

include '../utils.php';

/**
  Global variables and configurations
*/
$catalogsPath = "/ANALYSIS3/catalogs/";
#$agileCatalogPath = "/ANALYSIS3/catalogs/";
#$outfilePrefix = getcwd()."/tmp/";
$outfilenameSuffix = "catalogForJS9.reg";

/**
  Filters
*/
function isRegionLine($regionString)
{
  $pos1 = strpos($regionString, 'ellipse');
  $pos2 = strpos($regionString, 'point');
  // .. add more shapes..

  if($pos1 === false && $pos2 === false)
    return false;
  else
    return true;
}

/**
  Utilities
*/
function euclideanDistance($sourceCoords,$regionCoords)
{
  $dist = sqrt( pow($sourceCoords[0]-$regionCoords[0],2) + pow($sourceCoords[1]-$regionCoords[1],2) );
  return $dist;
}

function waitForKey() {
  print "\n\nAre you sure you want to do this?  Type 'yes' to continue: ";
  $handle = fopen ("php://stdin","r");
  $line = fgets($handle);
  if(trim($line) != 'yes'){
      print "ABORTING!\n";
      exit;
  }
  fclose($handle);
  print "\n";
  print "Thank you, continuing...\n";
}

function responseError($code,$errorMsg) {
  header('HTTP/1.1 '.$code.' Internal Server Error');
  header('Content-Type: application/json; charset=UTF-8');
  die(json_encode(array('message' => $errorMsg, 'code' => $code)));
}

/**
  Interfaces and Classes
*/
abstract class RegionCatalogFormat
{
    const AGILE = 0;
    const FERMI = 1;
    // etc.
}
abstract class RegionCatalog
{
  public $catalogContent;
  public $globalProperties;
  public $regions;
  public $wcssys;

  function __construct($filename,$wcssys)
  {

    $myfile = fopen($filename, "r");
    if ( !$myfile ) {
      responseError(500, 'File open failed: '.$filename);
    }
    else {

      $this->catalogContent = fread($myfile,filesize($filename));
      fclose($myfile);

      $this->wcssys = $wcssys;
      $this->globalProperties = array();
      $this->regions = array();
    }
  }

  public function getFormat() { }
  public function extractRegions() { }
  public function addDegreeSizeArgument()
  {
    $catalogTmp = $this->regions;
    $this->regions = array();
    foreach ($catalogTmp as $regionString)
    {
      if(! empty($regionString) )
      {
        $shapeParams = $this->extractShapeParamsFromRegionString($regionString);

        $regionString = str_replace($shapeParams[0],$shapeParams[0]."d",$regionString);
        $regionString = str_replace($shapeParams[1],$shapeParams[1]."d",$regionString);

        if(count($shapeParams) > 2)
        {
          $regionString = str_replace($shapeParams[2],$shapeParams[2]."d",$regionString);
          $regionString = str_replace($shapeParams[3],$shapeParams[3]."d",$regionString);
        }
        array_push($this->regions, $regionString);

      }
    }
  }

  public function sanitizeRegionsForJS9()
  {

  }



  public function getGlobalProperties()
  {
    return $this->globalProperties;
  }

  public function extractShapeParamsFromRegionString($regionString)
  {
    //ellipse( 305.2700, 40.5200,  0.6300,  0.6300, 90.0000)
    $splitLeft = split("\(" , $regionString);
    $splitRight = split("\)", $splitLeft[1]);
    $paramsArr = split("\," , $splitRight[0]);
    return $paramsArr;
  }

  public function filterRegionsBySphericalDistanceFromSource($sourceCoords, $degreeDistance)
  {
    $tmpRegions = $this->regions;
    $this->regions = array();

     foreach ($tmpRegions as $regionString)
     {
       $shapeParams = $this->extractShapeParamsFromRegionString($regionString);
       $regionCoords = array($shapeParams[0],$shapeParams[1]);

/*
       print_r($sourceCoords);
       print_r($regionCoords);
       print_r(distance($sourceCoords[0],$sourceCoords[1],$regionCoords[0],$regionCoords[1]));
       waitForKey();
*/
       if(distance($sourceCoords[0],$sourceCoords[1],$regionCoords[0],$regionCoords[1]) <= $degreeDistance)
       {
         array_push($this->regions, $regionString);
       }
     }
   }

   public function addRegionsJSONOpts()
   {
      $opts = array();

      /** global opts */
      if(count($this->globalProperties) > 0)
      {
        foreach($this->globalProperties as $x => $x_value)
          $opts[$x]=$x_value;
      }

      /** other opts */
      $opts['changeable']=false;



      $catalogTmp = $this->regions;
      $this->regions = array();
      foreach ($catalogTmp as $regionString)
      {
        /** opts hardcoded in regionStrings */
        $regionStringOnlyOpts = split("#",$regionString);

        $regionStringOnlyShape = $regionStringOnlyOpts[0];
        $regionStringOnlyOpts = $regionStringOnlyOpts[1];

        // find & remove text property
        $pos1 = strpos($regionStringOnlyOpts, "text=");
        if($pos1===false){}
        else
        {
          $regionStringOnlyOpts = str_replace("text=","",$regionStringOnlyOpts);

          // extracting text property
          $pos1 = strpos($regionStringOnlyOpts, "{");
          $pos2 = strpos($regionStringOnlyOpts, "}");
          $textValue = substr($regionStringOnlyOpts,$pos1+1,$pos2-$pos1-1);
          // removing text property from regionString
          $regionStringOnlyOpts = substr($regionStringOnlyOpts,0,$pos1).substr($regionStringOnlyOpts,$pos2+1,strlen($regionStringOnlyOpts));
          // set text opt value
          $opts['text']=$textValue;
        }



        // extracting others properties (if they exist)
        if (strlen(trim($regionStringOnlyOpts)) > 0)
        {
          $regionStringOnlyOpts = split(" ",$regionStringOnlyOpts);
          foreach ($regionStringOnlyOpts as $x)
          {
            if(strlen(trim($x))>0)
            {
              $properties = split("=", $x);
              $opts[$properties[0]]=$properties[1];
            }
          }
        }


        // text opts (color and RA DEC)
        $shapeParams = $this->extractShapeParamsFromRegionString($regionString);
        if(count($shapeParams) > 2)
        {
          $translationTerm = 2*max($shapeParams[2],$shapeParams[3]);
          $opts['textOpts'] = array('color'=>$opts['color'],'ra'=>$shapeParams[0], 'dec'=>$shapeParams[1]+$translationTerm);
        }
        else
        {
          $opts['textOpts'] = array('color'=>$opts['color']);
        }






        array_push($this->regions, $regionStringOnlyShape." ".json_encode($opts));
      }
    }

    public function writeJS9RegionFile($regionFileName)
    {

      // OPEN FILE
      $regionFile = fopen($regionFileName, "w");
      if ( !$regionFile ) {
        responseError(500, 'File '.$regionFileName.' open failed');
      }

      // ADD WCSSYS PARAMETER
      fwrite($regionFile, $this->wcssys."\n");

      // ADD REGIONS
      foreach ($this->regions as $x => $x_value)
      {
          fwrite($regionFile, $x_value."\n");
      }

      // CLOSE FILE
      fclose($regionFile);
    }



 }

/**
AGILE REGION CATALOG
  - the wcssys keyword is repeated
*/
/*
example:

galactic
ellipse(92.4053,-10.3623,0.274123,0.294406,19.096) #color=green width=2 text={(1)2AGL0249 sqrt(ts)=7.81183 r=0.28}
galactic
ellipse(352.523,-8.38252,0.267133,0.342645,36.8129) #color=green width=2 text={(1)2AGL0236 sqrt(ts)=9.9049 r=0.3}
.....
*/
class AgileRegionCatalog extends RegionCatalog
{
  public function getFormat()
  {
    return RegionCatalogFormat::AGILE;
  }
  public function extractRegions()
  {
    $this->regions = array_filter(split("\n",$this->catalogContent), "isRegionLine");
  }
  public function sanitizeRegionsForJS9()
  {
    parent::sanitizeRegionsForJS9();
  }
}



/**
FERMI REGION CATALOG:
  - first row describes global properties
*/
/*
example:

global color=red
fk5;point(   0.0377, 65.7517)# point=cross text={3FGL J0000.1+6545}
fk5;point(   0.0612,-37.6484)# point=cross text={3FGL J0000.2-3738}
...

*/
class FermiRegionCatalog extends RegionCatalog
{

  public function getFormat()
  {
    return RegionCatalogFormat::FERMI;
  }
  public function extractRegions()
  {
    $catalogTmp = split("\n",$this->catalogContent);
    $firstRow = str_replace("global","",array_shift($catalogTmp));
    $this->regions = $catalogTmp;

    // extracting global properties from first line
    $prop = split("=",$firstRow);
    if (count($prop) > 2)
      die("\nMore than one global property is not supported yet!");

    $this->globalProperties[str_replace(' ', '', $prop[0])]=$prop[1];  // [color] => red
  }
  /*
    Remove '<wcssys>;' before the shape name.
    Remove 'point=cross' if present
  */
  public function sanitizeRegionsForJS9()
  {
    parent::sanitizeRegionsForJS9();

    $catalogTmp = $this->regions;
    $this->regions = array();
    foreach ($catalogTmp as $regionString)
    {
      if(! empty($regionString))
      {

        $sanitized = str_replace($this->wcssys,"",$regionString);
        $sanitized = str_replace(";","",$sanitized);

        $pos1 = strpos($regionString, "point=cross");
        if($pos1 === false){ }
        else
          $sanitized = str_replace("point=cross","",$sanitized);

        array_push($this->regions, $sanitized);
      }
    }
  }
}

/**
For test:
  AGILE
    php regionCatalogForJS9Converter.php 2agl_cat_4_7_18.reg  0 galactic 92.4053 -10.3623 20
  FERMI
    php regionCatalogForJS9Converter.php 3FGL_gll_psc_v14.reg 1 fk5 92.4053 -10.3623 15
    php regionCatalogForJS9Converter.php 3FGL_gll_psc_v14_ell.reg 1 fk5 92.4053 -10.3623 15

*/
if (defined('STDIN')) {
  $regionCatalogFileName = $argv[1];
  $format = $argv[2];
  $wcssys = $argv[3];
  $sourceL = $argv[4];
  $sourceB = $argv[5];
  $degreeDistance = $argv[6];
} else {
  $regionCatalogFileName = $_GET['regionFileName'];
  $format = $_GET['format'];
  $wcssys = $_GET['wcssys'];
  $sourceL = $_GET['sourceL'];
  $sourceB = $_GET['sourceB'];
  $degreeDistance = $_GET['degreeDistance'];
}


if($format == RegionCatalogFormat::AGILE)
  $catalog = new AgileRegionCatalog($catalogsPath.$regionCatalogFileName,$wcssys);
else if($format == RegionCatalogFormat::FERMI)
  $catalog = new FermiRegionCatalog($catalogsPath.$regionCatalogFileName,$wcssys);
else
  responseError(500, 'Unkown catalog format!! Please use 0 (=AGILE) or 1 (=FERMI)');

if($wcssys === "fk5")
  $sourceCoords = gal2eq(array($sourceL, $sourceB));
else
  $sourceCoords = array($sourceL, $sourceB);


$catalog->extractRegions();

$catalog->filterRegionsBySphericalDistanceFromSource($sourceCoords, $degreeDistance);

$catalog->sanitizeRegionsForJS9();

$catalog->addRegionsJSONOpts();

$catalog->addDegreeSizeArgument();

//print_r($catalog->regions);


// should be written in tmp/
$fn = split("\.",$regionCatalogFileName);

$outfilename = $fn[0]."_".$outfilenameSuffix;
$outfilename = "tmp/".$outfilename;
$catalog->writeJS9RegionFile($outfilename);




header('Content-Type: application/json');
echo json_encode(array('regionFileName' => $outfilename));
