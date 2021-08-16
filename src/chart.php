<?php
function orientChart($orients,$filename){
    /*************Configuration Starts******************/

// Heading
$chartTitle = "Orientamenti";

// Branding / extra label (optional)
$logo = "Dati del ".date("d/m/Y").", ".date("H:i:s");
 
// Data
$arrData=$orients;

/*************************End****************************/

/*****************For Programmers************************/
$scale=2;
$imageWidth = 300*$scale;                             //image width 
$imageHeight = 200*$scale;                            //image height
$diameter = 150*$scale-50;                               //pie diameter 
$centerX = 100*$scale;                                //pie center pixels x
$centerY = 100*$scale;                                //pie center pixels y
$labelWidth = 10*$scale;                              //label width, no need to change 
/*************************End****************************/

createPieChart($arrData, $chartTitle, $logo, $imageWidth, $imageHeight, $diameter, $centerX, $centerY, $labelWidth,$filename);
}


function createPieChart($arrData, $chartTitle, $logo, $imageWidth, $imageHeight, $diameter, $centerX, $centerY, $labelWidth,$filename) {
	
	$item = array_keys($arrData);
	$data = array_values($arrData);
	
	for( $i = 0; $i < count( $data ); $i++ ) {
		$dataTotal += $data[ $i ];
	}

	$im = ImageCreate( $imageWidth, $imageHeight );

	$color[] = ImageColorAllocate( $im, 255, 0, 0 ); //red
	$color[] = ImageColorAllocate( $im, 255, 204, 0 );//yellow
	$color[] = ImageColorAllocate( $im, 153, 204, 0 );//green
	$color[] = ImageColorAllocate( $im, 153, 51, 255 );//purple
	$color[] = ImageColorAllocate( $im, 0, 128, 255 );//blue
	$color[] = ImageColorAllocate( $im, 255, 0, 128 );//pink
	$color[] = ImageColorAllocate( $im, 192, 192, 192 );//grey
	$color[] = ImageColorAllocate( $im, 204, 204, 0 );
	$color[] = ImageColorAllocate( $im, 64, 128, 128 );
	$color[] = ImageColorAllocate( $im, 204, 102, 153 );
	$white = ImageColorAllocate( $im, 255, 255, 255 );
	$black = ImageColorAllocate( $im, 0, 0, 0 );
	$grey = ImageColorAllocate( $im, 215, 215, 215 );

	ImageFill( $im, 0, 0, $white );

	$degree = 0;
	for( $i = 0; $i < count( $data ); $i++ ) {
		$startDegree = round( $degree );
		$degree += ( $data[ $i ] / $dataTotal ) * 360;
		$endDegree = round( $degree );

		$currentColor = $color[ $i % ( count( $color ) ) ];

		ImageArc( $im, $centerX, $centerY, $diameter, $diameter, $startDegree, $endDegree, $currentColor );

		list( $arcX, $arcY ) = circlePoint( $startDegree, $diameter );
		ImageLine( $im, $centerX, $centerY, floor( $centerX + $arcX ), floor( $centerY + $arcY ), $currentColor );

		list( $arcX, $arcY ) = circlePoint( $endDegree, $diameter );
		ImageLine( $im, $centerX, $centerY, ceil( $centerX + $arcX ), ceil( $centerY + $arcY ), $currentColor );

		$midPoint = round( ( ( $endDegree - $startDegree ) / 2 ) + $startDegree );
		list( $arcX, $arcY ) = circlePoint( $midPoint, $diameter / 1.5 );
		ImageFillToBorder( $im, floor( $centerX + $arcX ), floor( $centerY + $arcY ), $currentColor, $currentColor );
		ImageString( $im, 2, floor( $centerX + $arcX ), floor( $centerY + $arcY ), intval( round( $data[ $i ] / $dataTotal * 100 ) ) . "%", $black );
	}

	$labelX = $centerX + $diameter / 2 + 10;
	$labelY = $centerY - $diameter / 4;
	$titleX = $labelX - $diameter / 4;
	$titleY = $centerY - $diameter / 2;
	ImageString( $im, 3, $titleX + 1, $titleY + 1, $chartTitle, $grey );
	ImageString( $im, 3, $titleX, $titleY, $chartTitle, $black );

	for( $i = 0; $i < count( $item ); $i++ ) {
		$currentColor = $color[ $i % ( count( $color ) ) ];
		ImageRectangle( $im, $labelX, $labelY, $labelX + $labelWidth, $labelY + $labelWidth, $black );
		ImageFilledRectangle( $im, $labelX + 1, $labelY + 1, $labelX + $labelWidth, $labelY + $labelWidth, $currentColor );
		ImageString( $im, 2, $labelX + $labelWidth + 5, $labelY, $item[ $i ], $black );
		ImageString( $im, 2, $labelX + $labelWidth + 90, $labelY, $data[ $i ]." %", $black );
		$labelY += $labelWidth + 2;
	}
	
	//ImageString( $im, 3, $labelX, $labelY, "Total:", $black );
	ImageString( $im, 3, $labelX + $labelWidth + 60, $labelY, $dataTotal, $black );
	ImageString( $im, 2, $labelX, $labelY + 15, $logo, $black );
	ImagePNG( $im ,$filename);
	ImageDestroy( $im );
}

function circlePoint( $deg, $dia ) {
	$x = cos( deg2rad( $deg ) ) * ( $dia / 2 );
	$y = sin( deg2rad( $deg ) ) * ( $dia / 2 );	
	return array( $x, $y );
}
