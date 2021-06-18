<?php

namespace Ofi\steganography;

class makeImage {

    /**
     * To make image from text
     */
    static public function make($text_to_image = null) {
        // Create the image
        $img = imagecreatetruecolor(400, 100);
    
        // Create some colors
        $background  = imagecolorallocate( $img, rand(0, 255),   rand(0, 255),   rand(0, 255) );
        $text_colour = imagecolorallocate( $img, 255, 255, 255 );
        //imagefilledrectangle($img, 0, 0, 319, 59, $lightsky);
    
        // The text to draw

        if(empty($text_to_image)) {
            $text = 'Encrypted data, created at ' . date("d/m/Y");
        } else {
            $text = $text_to_image;
        }
    
        imagestring( $img, 4, 30, 25, $text, $text_colour );
        imagesetthickness ( $img, 5 );
    
        // Using imagepng() results in clearer text compared with imagejpeg() 
    
        $filename = time() .'.jpg';
    
        imagejpeg($img, $filename, 9);
        imagedestroy($img); 
    
        return $filename;
    }
}

?> 