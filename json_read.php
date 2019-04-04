<?php

$folderPath = __DIR__ . '/data/json/';
function readFolder( $path ) {
    
    // Open the folder
    if ( !( $dir = opendir( $path ) ) ) die( "Can't open $path" );

    // Read the contents of the folder, ignoring '.' and '..', and
    // appending '/' to any subfolder names. Add all the files and
    // subfolders to the $filenames array.
    
    while ( $filename = readdir( $dir ) ) {
        if ( $filename != '.' && $filename != '..' ) {
            if ( is_dir( "$path/$filename" ) ) $filename .= '/';
            yield $filename;
        }
    }   
    closedir ( $dir );
}

$output = array();

foreach (readFolder($folderPath) as $item) {
    $jsonString = file_get_contents($folderPath . $item);
    $jsonObject = json_decode($jsonString);
    unset( $jsonObject -> friends );
    $output[] = $jsonObject ;
}
 
echo json_encode($output) ;