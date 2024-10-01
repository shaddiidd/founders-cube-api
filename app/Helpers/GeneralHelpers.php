<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class GeneralHelpers
{

	const WEB_APP_URL = 'https://community.thefounderscube.com';

	public static function generate_random_string($length = 32)
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	      $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function uploadFile($file)
	{
	    $name = md5(time() . '_' . GeneralHelpers::generate_random_string()) . '.' . $file->getClientOriginalExtension();
	    $filePath = 'founderscubeapp/' . $name;
	    $wasUploaded = Storage::disk('s3')->put($filePath, file_get_contents($file));
	    if ($wasUploaded) {
	        return $filePath;
	    } else {
	        return false;
	    }
	}

	public static function uploadProfilePic($file)
	{
		$str = md5(time() . '_' . GeneralHelpers::generate_random_string());
	    $name = $str . '.' . $file->getClientOriginalExtension();
	    $thumb_name = $str . '.thumb.' . $file->getClientOriginalExtension();
	    $thumb = Image::make($file)->crop(400, 400);
	    $filePath = 'founderscubeapp/' . $name;
	    $wasUploaded = Storage::disk('s3')->put($filePath, file_get_contents($file)) && Storage::disk('s3')->put('founderscubeapp/' . $thumb_name, $thumb->stream());
	    if ($wasUploaded) {
	        return $filePath;
	    } else {
	        return false;
	    }
	}
}