<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    
    public function create()
    {
        return view('upload');

    }

    public function store(Request $request)
    {
        

        $uploadedFile = $request->file('file');
        
        $name = $uploadedFile->getClientOriginalName();

        $image = Image::make($uploadedFile);

        if($request->wmcolor === 'black')
        {
            $waterMarkUrl = Image::make(public_path('watermark/black.png'));
        } elseif($request->wmcolor === 'white')
        {
            $waterMarkUrl = Image::make(public_path('watermark/white.png'));
        }
        
        if ($request->sm === 'facebookhz')
        {
            $width = 1200;
            $height = 630;
        } elseif ($request->sm === 'facebookvt')
        {
            $width = 1080;
            $height = 1920;
        } elseif ($request->sm === 'instagram')
        {
            $width = 1080;
            $height = 1080;
        }

        if ($request->fitlocation === 'left')
        {   
            $fitlocation = 'left';
            
        } elseif ($request->fitlocation === 'right')
        {
            $fitlocation = 'right';  
        } elseif ($request->fitlocation === 'top')
        {
            $fitlocation = 'top';
        } elseif ($request->fitlocation === 'bottom')
        {
            $fitlocation = 'bottom';
        } elseif ($request->fitlocation === 'center')
        {
            $fitlocation = 'center';
        }

        if ($request->greyscale == true)
        {
            $image->greyscale();
        }
        
        $image->fit($width, $height, null, $fitlocation);

        if($request->wmsize === 'big')
        {
            $wmheight = 100;            
        } elseif($request->wmsize === 'small')
        {
            $wmheight = 60;
        }

        $waterMarkUrl->resize(null, $wmheight, function($constraint) {
            $constraint->aspectRatio();
        });

        if($request->wmopacity == true)
        {
            $waterMarkUrl->opacity(45);
        }

        if($request->wmlocation === 'bottom-left')
        {
            $wmlocation = 'bottom-left';
        } elseif($request->wmlocation === 'bottom-right')
        {
            $wmlocation = 'bottom-right';
        } elseif($request->wmlocation === 'top-left')
        {
            $wmlocation = 'top-left';
        } elseif($request->wmlocation === 'top-right')
        {
            $wmlocation = 'top-right';
        }
        $image->insert($waterMarkUrl, $wmlocation, 10, 10);

        if($_POST['imgname'] == true)
        {
            $name = $_POST['imgname'];
        }
        
        $imgpath = public_path('stored/'.$name);
        $image->save($imgpath);
        
        return view('display',['name'=>$name]);
    }
}