<?php
$dir = dir('.');
while( $nom = $dir->read() ) {
	$dot_place = strrpos( $nom, '.' );
	$file_ext = strtolower( substr( $nom, $dot_place+1 ) );
	if ($file_ext == 'gif') {
		$image = imagecreatefromgif($nom);
		$interlaced = imageinterlace($image);
		if ( $interlaced)
		{
			imageinterlace($image,0);
			imagegif($image,$nom);
			echo $nom, "<br/>";
		}
		imagedestroy($image);
	}
}
$dir->close();
