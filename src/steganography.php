<?php

namespace Ofi\steganography;

use Ofi\steganography\makeImage;
use Exception;

class steganography {

    /**
     * To encrypt data an store to image
     */
    public static function encrypt(String $message, String $text_to_image = null) {
        $file = makeImage::make($text_to_image);

        // Encode the message into a binary string.
        $binaryMessage = '';
        for ($i = 0; $i < mb_strlen($message); ++$i) {
          $character = ord($message[$i]);
          $binaryMessage .= str_pad(decbin($character), 8, '0', STR_PAD_LEFT);
        }
       
        // Inject the 'end of text' character into the string.
        $binaryMessage .= '00000011';
       
        // Load the image into memory.
        $img = imagecreatefromjpeg($file);
       
        // Get image dimensions.
        $width = imagesx($img);
        $height = imagesy($img);
       
        $messagePosition = 0;
       
        for ($y = 0; $y < $height; $y++) {
          for ($x = 0; $x < $width; $x++) {
       
            if (!isset($binaryMessage[$messagePosition])) {
              // No need to keep processing beyond the end of the message.
              break 2;
            }
       
            // Extract the colour.
            $rgb = imagecolorat($img, $x, $y);
            $colors = imagecolorsforindex($img, $rgb);
       
            $red = $colors['red'];
            $green = $colors['green'];
            $blue = $colors['blue'];
            $alpha = $colors['alpha'];
       
            // Convert the blue to binary.
            $binaryBlue = str_pad(decbin($blue), 8, '0', STR_PAD_LEFT);
       
            // Replace the final bit of the blue colour with our message.
            $binaryBlue[strlen($binaryBlue) - 1] = $binaryMessage[$messagePosition];
            $newBlue = bindec($binaryBlue);
       
            // Inject that new colour back into the image.
            $newColor = imagecolorallocatealpha($img, $red, $green, $newBlue, $alpha);
            imagesetpixel($img, $x, $y, $newColor);
       
            // Advance message position.
            $messagePosition++;
          }
        }
       
        $pathinfo = pathinfo($file);
    
        // Remove image first if available
        $newImage = $pathinfo['filename'] . '.jpg';
    
        if (is_file($newImage)) {
          unlink($newImage);
        }
    
        // Save the image to a file.
        imagepng($img, $newImage, 9);
       
        // Destroy the image handler.
        imagedestroy($img);
    
        // Download file and remove temp file
        header('Content-Disposition: attachment; filename='. $pathinfo['filename'] .'.png');
        header('Content-Type: application/octet-stream'); // Downloading on Android might fail without this
        ob_clean();
        readfile($newImage);
        unlink($newImage);
    }

    /**
     * To decrypt data from image file
     */
    static public function decrypt(String $file) {

        if(!is_file($file)) {
          throw new \Exception("File " . $file . ' not found');
        }
        
        try {
          // Read the file into memory.
          $img = imagecreatefrompng($file);
          
          // Read the message dimensions.
          $width = imagesx($img);
          $height = imagesy($img);
          
          // Set the message.
          $binaryMessage = '';
          
          // Initialise message buffer.
          $binaryMessageCharacterParts = [];
          
          for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
            
              // Extract the colour.
              $rgb = imagecolorat($img, $x, $y);
              $colors = imagecolorsforindex($img, $rgb);
            
              $blue = $colors['blue'];
            
              // Convert the blue to binary.
              $binaryBlue = decbin($blue);
            
              // Extract the least significant bit into out message buffer..
              $binaryMessageCharacterParts[] = $binaryBlue[strlen($binaryBlue) - 1];
            
              if (count($binaryMessageCharacterParts) == 8) {
                // If we have 8 parts to the message buffer we can update the message string.
                $binaryCharacter = implode('', $binaryMessageCharacterParts);
                $binaryMessageCharacterParts = [];
                if ($binaryCharacter == '00000011') {
                  // If the 'end of text' character is found then stop looking for the message.
                  break 2;
                }
                else {
                  // Append the character we found into the message.
                  $binaryMessage .= $binaryCharacter;
                }
              }
            }
          }
        
          // Convert the binary message we have found into text.
          $message = '';
          for ($i = 0; $i < strlen($binaryMessage); $i += 8) {
            $character = mb_substr($binaryMessage, $i, 8);
            $message .= chr(bindec($character));
          }
      
          return $message;
      
        } catch (\Throwable $th) {
          throw new Exception("Encrypted message inccorect, failed to decrypt");
        }
      
    }
}