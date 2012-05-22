<?php

include("geoip.inc");

$gi = geoip_open("GeoIP.dat",GEOIP_STANDARD);
//$_SERVER[ 'REMOTE_ADDR' ]
echo geoip_country_code_by_addr($gi, "24.24.24.24");

geoip_close($gi);

?>
