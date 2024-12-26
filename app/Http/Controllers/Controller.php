<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function deleteImage($url) {
        
        // Parse the URL and get the path part
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        // return [$parsedUrl];
        // Remove leading slashes from the path if any
        $parsedUrl = ltrim($parsedUrl, '/');
        // return [$parsedUrl];
        // Construct the full path of the image using public_path
        $fullPath = public_path($parsedUrl);
        gc_collect_cycles();
        // return [$fullPath];
        // Check if the image file exists and delete it
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                return true;
            } else {
                return false; // Failed to delete the file
            }
        } else {
            return false; // File does not exist
        }
    }
    
    public function storeImage( $file,$name){
        $extension = $file->getClientOriginalExtension();
           
        $imageName = uniqid() . '.' .$extension;
        $file->move(public_path($name), $imageName);

        // Get the full path to the saved image
        $imagePath = asset($name.'/' . $imageName);
                
         
       
       return $imagePath;

    }
}