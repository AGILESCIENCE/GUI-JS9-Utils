<?php

/*
galactic
ellipse(92.4053,-10.3623,0.274123,0.294406,19.096) #color=green width=2 text={(1)2AGL0249 sqrt(ts)=7.81183 r=0.28}
galactic
ellipse(352.523,-8.38252,0.267133,0.342645,36.8129) #color=green width=2 text={(1)2AGL0236 sqrt(ts)=9.9049 r=0.3}
galactic
ellipse(348.897,13.4383,0.402999,0.350166,19.0827) #color=green width=2 text={(7)2AGL0211 sqrt(ts)=5.48269 r=0.38}
galactic
ellipse(357.38,-2.00809,0.670685,0.386526,10.6102) #color=green width=2 text={(9)B10285I00 sqrt(ts)=6.92482 r=0.51}


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

//propertyName,fromCatalogUrl,toCatalogUrl

if (defined('STDIN')) {
  $catalog1Url = $argv[1];
  $catalog1Format = $argv[2];
  $catalog2Url = $argv[3];
  $catalog2Format = $argv[4];
  $outputFormat = $argv[5];
} else {
  $catalog1Url = $_GET['catalog1Url'];
  $catalog1Format = $_GET['catalog1Format'];;
  $catalog2Url = $_GET['catalog2Url'];
  $catalog2Format = $_GET['catalog1Format'];
  $outputFormat = $_GET['outputFormat'];
}


// open first catalog -> read content
  // split on \n
  // filter out the rows that are not regions

// open second catalog -> read content
  // split on \n
  // filter out the rows that are not regions

// split on \n



// create mergeCatalog


?>
