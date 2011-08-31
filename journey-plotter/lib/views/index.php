<?php

/**
 * Journey Plotter
 *
 * indexView
 *
 **/

class indexView extends plotterView
{
    public function getHtml($params = NULL)
    {
        return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 

		<meta property="og:description" content="Dewey Beach is about to get crazy come Memorial Day Weekend 2011. Are you going?" />
		<meta property="og:title" content="Dewey Beach Memorial Day Countdown" />
		<meta property="og:image" content="http://wadehammes.com/dewey-beach/map-thumb.JPG" />
		
		<link href="/css/style.css" rel="stylesheet" type="text/css" />
		<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css' />	
			
        <!--// saulhoward.com -->
<!--
		<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAAzaeDyWdwzvtsY05ARniHxxRMhHXaGBoIpk3yNv-sJWNTadMIuBT2p5aye3bFsmPKpKFVC9WkJ7tbWg" type="text/javascript"></script>
-->
        <!--// journey-plotter -->
		<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAAzaeDyWdwzvtsY05ARniHxxQqivliiJUNaU5kwoCXSQRQkgDwiBTufHyNxsCyWRL-yAlg-TAEKeQ76Q" type="text/javascript"></script>

		<script language="Javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script language="Javascript" type="text/javascript" src="/js/lib/underscore-min.js"></script>
		<script language="Javascript" type="text/javascript" src="/js/plotter.js"></script>

		<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>

		<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>



		<style type="text/css" media="screen">
        iframe { vertical-align: top; }
		</style>
		
	</head>
	
	<body>
		<div id="map_canvas" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; z-index: 0;"></div>
	</body>
</html>
HTML;

    }


}


