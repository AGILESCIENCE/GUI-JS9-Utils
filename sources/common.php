<?php
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


function gal2eq($gal)
{
	$l = deg2rad((float)$gal[0]);
	$b = deg2rad((float)$gal[1]);

	// North galactic pole (J2000) -- according to Wikipedia
	$pole_ra = deg2rad(192.859508);
	$pole_dec = deg2rad(27.128336);
	$posangle = deg2rad(122.932-90.0);

	// North galactic pole (B1950)
	//pole_ra = radians(192.25)
	//pole_dec = radians(27.4)
	//posangle = radians(123.0-90.0)

	$ra = atan2( (cos($b)*cos($l-$posangle)), (sin($b)*cos($pole_dec) - cos($b)*sin($pole_dec)*sin($l-$posangle)) ) + $pole_ra;
	$dec = asin( cos($b)*cos($pole_dec)*sin($l-$posangle) + sin($b)*sin($pole_dec) );

	$ra = rad2deg($ra);
	$dec = rad2deg($dec);
	if($ra>360)
			$ra = $ra-360;

	return array($ra, $dec);
}



function distance($ll, $bl, $lf, $bf)
{
	$distance = 0;

	//verify parameters
	if( $ll < 0 || $ll > 360 || $lf < 0 || $lf > 360)
	{
		$distance = -2;
		return $distance;
	}


	else
		if( $bl < -90 || $bl > 90 || $bf < -90 || $bf > 90)
		{
			$distance = -2;
			return $distance;
		}
		else
		{
			$d1 = $bl - $bf;
			$d2 = $ll - $lf;

			$bl1 = pi() / 2.0 - ($bl * pi() / 180.0);
			$bf1 = pi() / 2.0 - ($bf * pi()  / 180.0);
			$m4 = cos($bl1) * cos($bf1)  + sin($bl1) * sin($bf1) * cos($d2 * pi()  / 180.0);
			if ($m4 > 1)
				$m4 = 1;

			//puts "DEBUG " + m4.to_s
			$d4 = acos($m4) *  180.0 / pi();
			$distance = $d4;

			return $distance;

		}

}

?>
