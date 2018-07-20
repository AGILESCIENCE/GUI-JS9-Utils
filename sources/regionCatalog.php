<?php

abstract class RegionCatalogFormat
{
    /*
      INVARIANTS AGILE:
        - each spare row describes wcssys
        - each pair row describes a region with properties after the #
    */
    const AGILE = 0;

    /*
      INVARIANTS FERMI:
        - the first line describe the global properties (MANDATORY)
        - each row describe the wcssys; and the region right after with properties after the #
    */
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
      responseError(500, '[__construct()] File open failed: ('.$filename.')');
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
  //public function returnCatalogContentInAgileFormat() { }

  public function sanitizeRegionsForJS9() {
    if( empty($this->wcssys))
    {
      responseError(500, '[sanitizeRegionsForJS9()] $wcssys is empty!!');
    }
  }


  public function getGlobalProperties()
  {
    return $this->globalProperties;
  }

  public function extractShapeParamsFromRegionString($regionString)
  {
    //ellipse( 305.2700, 40.5200,  0.6300,  0.6300, 90.0000)
    if( ! empty($regionString))
    {
      $splitLeft = split("\(" , $regionString);
      $splitRight = split("\)", $splitLeft[1]);
      $paramsArr = split("\," , $splitRight[0]);
      return $paramsArr;
    }
    return array();
  }

  public function filterRegionsBySphericalDistanceFromSource($sourceCoords, $degreeDistance)
  {
    $tmpRegions = $this->regions;
    $this->regions = array();

     foreach ($tmpRegions as $regionString)
     {
       $shapeParams = $this->extractShapeParamsFromRegionString($regionString);
       if( count($shapeParams)>1 )
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

      $catalogTmp = $this->regions;
      $this->regions = array();
      foreach ($catalogTmp as $regionString)
      {

        $opts = array();

        /** global opts */
        if(count($this->globalProperties) > 0)
        {
          foreach($this->globalProperties as $x => $x_value)
            $opts[$x]=$x_value;
        }

        /** other opts */
        $opts['changeable']='false';



        /** opts hardcoded in regionStrings */

        $regionStringOnlyOpts = split("#",$regionString);

        $regionStringOnlyShape = $regionStringOnlyOpts[0];

        if( count($regionStringOnlyOpts) > 1 )
        {

          $regionStringOnlyOpts = $regionStringOnlyOpts[1];


          // find & remove text property
          $pos1 = strpos($regionStringOnlyOpts, "text=");
          if($pos1===false){   }
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
            foreach ($regionStringOnlyOpts as $key => $x)
            {
              if(strlen(trim($x))>0)
              {
                $properties = split("=", $x);
                $opts[$properties[0]]=$properties[1];
              }
            }
          }


        }



        if (array_key_exists("text",$opts))
        {
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

        }

        array_push($this->regions, $regionStringOnlyShape." ".json_encode($opts));
      }
    }

    public function writeJS9RegionFile($regionFileName)
    {

      // OPEN FILE
      $regionFile = fopen($regionFileName, "w");
      if ( !$regionFile ) {
        responseError(500, '[writeJS9RegionFile] File '.$regionFileName.' open failed');
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
  /*public function returnCatalogContentInAgileFormat()
  {
    return $this->catalogContent;
  }*/

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
    if (count($prop) > 2 && !empty($prop[2]))
      responseError(500, "[extractRegions()] More than one global property is not supported yet!");


    if (count($prop) > 1 && !empty($prop[1]))
    {
      $this->globalProperties[str_replace(' ', '', $prop[0])]=$prop[1];  // [color] => red
    }


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
/*
  public function returnCatalogContentInAgileFormat()
  {
    $catalogContentInAgileFormat = "";

    $catalogRows = split("\n",$this->catalogContent);

    //get global properties from first line ('global' first line is mandatory)
    //$properties = split(" ",$catalogRows[0]);
    //if($properties[0] == "global")
    //{
    //  array_shift($properties);
    //}
    //else
    //{
    //  responseError(500, 'FERMI region catalog format does not have global properties in the first row');
    //}


    // if(count($properties)==0) --> no global properties

    // get wcssys
    $wcssys = split(";",$catalogRows[1]);
    $this->wcssys = $wcssys[0];

    // extract regions
    $this->extractRegions();

    //  remove wcssys and not supported properties
    $this->sanitizeRegionsForJS9();

    // add global property after the #
    $this->addRegionsJSONOpts(); // NO!! I HAVE TO USE addRegionHashtagOpts()

    print_r($this->regions);

    return $catalogContentInAgileFormat;
  }
*/
}

?>
