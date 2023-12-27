
<?php
echo "Processing...\n";

function generateCsv( $filePath, $header, $body ){


    if( !is_dir('csv') ) mkdir('csv');

    $file = fopen($filePath, 'w');
    $BomInsert = "\xEF\xBB\xBF";
    fwrite($file, $BomInsert);
    fputcsv($file, $header, ';');

    foreach(  $body as $bitem ) {

        $csvBody  = [

            # Csv data set
             $bitem['id'],
            $bitem['college_name'],
            $bitem['pin_code'],
            $bitem['city'],
            $bitem['state'],
            (string)($bitem['lattitude']),
            (string)$bitem['longitude']
        ];

        fputcsv($file, $csvBody, ';');
    }

    fclose($file);

}


function myErrorHandler( $errno, $errstr, $errfile, $errline ) {
    

    if( !( error_reporting() && $errno ) ) {
        return false;
    } else {


        return true;
    }

}

