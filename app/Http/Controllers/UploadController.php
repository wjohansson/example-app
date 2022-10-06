<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use GDText\Box;
use GDText\Color;

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

    public function apiStore(Request $request) 
    {      
        /*
        Användning
        url:                localhost/photos

        Variabler
        photo:              (obligatorisk) välj en bildfil
        width:              (obligatorisk) skriv ett positivt tal
        height:             (obligatorisk) skriv ett positivt tal
        logo_color:         (valfri) skriv antingen 'white' eller 'black' eller lämna tom för att få standard färg vit
        logo_position:      (valfri) skriv antingen 'bottom_left', 'bottom_right', 'top_left', eller 'top_right' eller lämna tom för att få standard
                                position nere till vänster
        text:               (valfri) skriv en text som du vill ska skrivas ut på bilden
        text_color:         (valfri) skriv antingen 'white' eller 'black' eller lämna tom för att få standardfärg svart
        text_box_width:     (valfri) skriv ett positivt tal som inte får vara större än bredden på bilden eller lämna tom för att få en standardbredd på 
                                60% av bredden på bilden
        text_position_x:    (valfri) skriv antingen 'right', 'center', eller 'left' eller lämna tom för att få standard position centrerad
        text_position_y:    (valfri) skriv antingen 'top', 'center', eller 'bottom' eller lämna tom för att få standard position centrerad
        font_size:          (valfri) skriv ett positivt tal eller lämna tom för att få font size default på 48px
        brighten_darken:    (valfri) skriv ett tal mellan -100 och 100, skriver man ingenting blir det ingen justering på ljusheten på bilden
        */
        $error = []; 

        if($request->bearerToken() !== 'apa')
        {
            return response()->json(['error'=>'invalid token'], 401); //returnerar felmeddelande för att fel token används
        }

        //hämtar in alla värden från requesten
        $photo = $request->photo;
        $width = $request->width;
        $height = $request->height;
        $logo_color = $request->logo_color;
        $logo_position = $request->logo_position;
        $text = $request->text;
        $text_color = $request->text_color;
        $text_box_width = $request->text_box_width;
        $text_position_x = $request->text_position_x;
        $text_position_y = $request->text_position_y;
        $font_size = $request->font_size;
        $brighten_darken = $request->brighten_darken;
        
        //skapar error meddelanden
        if ($photo == null)
        {
            array_push($error, "Must choose an image");
        }

        if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0)
        {          
            array_push($error, "Must choose a width and a height. Value must also be a positive integer"); 
        }

        if ($logo_color != null && $logo_color !== 'black' && $logo_color !== 'white')
        {
            array_push($error, "Must choose color of logo or leave it empty for a standard color of white. Possible values: 'black' or 'white'.");
        } elseif ($logo_color == null)
        {
            $logo_color = 'white'; //standardvärde
        }

        if ($logo_position != null && $logo_position !== 'bottom-right' && $logo_position !== 'bottom-left' && $logo_position !== 'top-right' && $logo_position !== 'top-left')
        {
            array_push($error, "Must choose position of logo or leave empty for a standard poistion of bottom-left. Possible values: 'bottom-right', 'bottom-left', 'top-right' or 'top-left'.");
        } elseif ($logo_position == null)
        {
            $logo_position = 'bottom-left'; //standardvärde
        }

        if ($text_box_width != null && (!is_numeric($text_box_width) || $text_box_width <= 0 || $text_box_width >= $width) && $width > 0 && $width != null && $text != null)
        {
            array_push($error, "Text box width can't be bigger than image width and must be a positive integer or left empty for a standard size as 60% of image width.");
        } elseif ($text == null || $width == null || $width <= 0)
        {
            $text_box_width = null;
        } elseif ($text_box_width == null && is_numeric($width))
        {
            $text_box_width = $width * 0.6; //standardvärde
        }

        if ($text_color !=null && $text_color !== 'white' && $text_color !== 'black' && $text != null)
        {
            array_push($error, "Text color must be either 'white' or 'black'.");
        } elseif ($text == null)
        {
            $text_color = null;
        } elseif($text_color == null)
        {
            $text_color = 'black'; //standardvärde
        }

        if ($text_position_x != null && $text_position_x !== 'right' && $text_position_x !== 'center' && $text_position_x !== 'left' && $text != null) 
        {
            array_push($error, "Must choose x-position of text or leave empty. Possible values: 'right', 'center', 'left.");
        } elseif ($text == null)
        {
            $text_position_x = null;
        } elseif ($text_position_x == null)
        {
            $text_position_x = 'center'; //standardvärde
        }

        if ($text_position_y != null && $text_position_y !== 'top' && $text_position_y !== 'center' && $text_position_y !== 'bottom' && $text != null) 
        {
            array_push($error, "Must choose y-position of text or leave empty. Possible values: 'top', 'center', 'bottom'.");
        } elseif ($text == null)
        {
            $text_position_y = null;
        } elseif ($text_position_y == null)
        {
            $text_position_y = 'center'; //standardvärde
        }

        if ($font_size != null && (!is_numeric($font_size) || $font_size <= 0))
        {
            array_push($error, "Font size must be a positive integer or empty.");
        } elseif ($font_size == null)
        {
            $font_size = '48'; //standardvärde
        }

        if ($brighten_darken != null && (!is_numeric($brighten_darken) || $brighten_darken < -100 || $brighten_darken > 100))
        {
            array_push($error, "Brighten or darken must an integer between the values of -100 and 100 or empty.");
        } elseif ($brighten_darken == null)
        {
            $brighten_darken = '0'; //standardvärde
        }

        //kollar om det finns några errormeddelanden och skriver ut dom
        if ($error !== [])
        {
            return response()->json([
                'error' => $error,
            ], 400);
        } 

        //här börjar redigeringen av bilden
        $image = Image::make($request->photo);

        $name = $photo->getClientOriginalName();

        $logo_size = $width/12;

        //tar bort eventuella decimaltecken på variabler som ska vara int
        $width = (int) $width;
        $height = (int) $height;

        $text_box_width = (int) $text_box_width;
        $brighten_darken = (int) $brighten_darken;
        

        $image->fit($width, $height);
        
        $image->colorize($brighten_darken, $brighten_darken, $brighten_darken);

        if ($logo_color === 'black')
        {
            $logo = Image::make(public_path('watermark/black.png'));
        } elseif($logo_color === 'white')
        {
            $logo = Image::make(public_path('watermark/white.png'));
        }

        $logo->resize(null, $logo_size, function($constraint) {
            $constraint->aspectRatio();
        });

        $logo_padding = $width/100;
        $image->insert($logo, $logo_position, $logo_padding, $logo_padding); //lägger in den omskalade loggan på rätt plats på bilden

        //här börjar skapandet och redigeringen av textrutan och texten i den
        $core_image = $image->getCore();
        $box = new Box($core_image);
        $box->setFontFace(storage_path('app/public/GT-Cinetype-Light.ttf'));

        $box->setFontSize($font_size);

        if ($text_color === 'white')
        {
            $box->setFontColor(new Color(255, 255, 255));
        } elseif ($text_color === 'black')
        {
            $box->setFontColor(new Color(0, 0, 0));
        }

        $text_box_padding = $width / 50;

        if ($text_position_x === 'left')
        {
            $text_padding_x = $text_box_padding;
        } elseif ($text_position_x === 'center')
        {
            $text_padding_x = ($width - $text_box_width) / 2;
        } elseif ($text_position_x === 'right')
        {
            $text_padding_x = ($width - $text_box_width) - $text_box_padding;
        }

        if ($text_position_y === 'top')
        {
            $text_padding_y = $text_box_padding;
        } elseif ($text_position_y === 'center')
        {
            $text_padding_y = null;
        } elseif ($text_position_y === 'bottom')
        {
            $text_padding_y = -1 * $text_box_padding;
        }
        
        $box->setBox($text_padding_x, $text_padding_y, $text_box_width, $height);
        $box->setTextAlign($text_position_x, $text_position_y);
        $box->draw($text);

        //sparar bilden
        $image_path = storage_path('app/public/'.$name);
        $image->save($image_path);
        
        //skriver ut en url till den redigerade bilden
        return response()->json([
            'url' => ['image' => asset('storage').'/'.$name],
        ]);
    }
}