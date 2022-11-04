<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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

        $request->wmcolor === 'white' ? $waterMarkUrl = Image::make(public_path('watermark/white.png')) : $waterMarkUrl = Image::make(public_path('watermark/black.png'));

        if ($request->sm === 'facebookhz') {
            $width = 1200;
            $height = 630;
        }
        if ($request->sm === 'facebookvt') {
            $width = 1080;
            $height = 1920;
        }
        if ($request->sm === 'instagram') {
            $width = 1080;
            $height = 1080;
        }

        $request->greyscale == true ? $image->greyscale() : $image = $image;

        $image->fit($width, $height, null, $request->fitlocation);

        $request->wmsize === 'big' ? $wmheight = 100 : $wmheight = 60;

        $waterMarkUrl->resize(null, $wmheight, function ($constraint) {
            $constraint->aspectRatio();
        });

        $request->wmopacity == true ? $waterMarkUrl->opacity(45) : $waterMarkUrl->opacity(100);

        $image->insert($waterMarkUrl, $request->wmlocation, 10, 10);

        $_POST['imgname'] == true ? $name = $_POST['imgname'] : $name = $name;

        $imgpath = public_path($name);
        $image->save($imgpath);

        return view('display', ['name' => $name]);
    }

    public function apiStore(Request $request)
    {
        /*
        Användning
        url:                localhost/photos

        Variabler
        photo:              (obligatorisk) välj en bildfil, format: jpg, png, jpeg, gif, svg
        width:              (obligatorisk) skriv ett positivt heltal
        height:             (obligatorisk) skriv ett positivt heltal
        logo_color:         (valfri) skriv antingen 'white' eller 'black' eller lämna tom för att få standard färg vit
        logo_position:      (valfri) skriv antingen 'bottom-left', 'bottom-right', 'top-left', eller 'top-right' eller lämna tom för att få standard
                                position nere till vänster
        text:               (valfri) skriv en text som du vill ska skrivas ut på bilden
        text_color:         (valfri) skriv antingen 'white' eller 'black' eller lämna tom för att få standardfärg svart
        text_box_width:     (valfri) skriv ett positivt heltal som inte får vara större än bredden på bilden eller lämna tom för att få en standardbredd på 
                                60% av bredden på bilden
        text_position_x:    (valfri) skriv antingen 'right', 'center', eller 'left' eller lämna tom för att få standard position centrerad
        text_position_y:    (valfri) skriv antingen 'top', 'center', eller 'bottom' eller lämna tom för att få standard position centrerad
        font_size:          (valfri) skriv ett positivt heltal eller lämna tom för att få font size default på 48px
        brighten_darken:    (valfri) skriv ett heltal mellan -100 och 100, skriver man ingenting blir det ingen justering på ljusheten på bilden
        */

        if ($request->bearerToken() !== 'apa') {
            return response()->json(['error' => 'invalid token'], 401); //returnerar felmeddelande för att fel token används
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
        $validation = Validator::make(
            $request->all(),
            [
                "photo" => ["required", "image"],
                "width" => ["required", "integer", "gt:0"],
                "height" => ["required", "integer", "gt:0"],
                "logo_color" => ["nullable", "string", Rule::in(["white", "black"])],
                "logo_position" => ["nullable", "string", Rule::in(["bottom-left", "bottom-right", "top-left", "top-right"])],
                "text" => ["nullable", "string"],
                "text_color" => ["nullable", "string", Rule::in(["white", "black"])],
                "text_box_width" => ["nullable", "integer", "lte:width"],
                "text_position_x" => ["nullable", "string", Rule::in(["left", "center", "right"])],
                "text_position_y" => ["nullable", "string", Rule::in(["top", "center", "bottom"])],
                "font_size" => ["nullable", "integer", "gt:0"],
                "brighten_darken" => ["nullable", "integer", "gte:-100", "lte:100"],
            ],
            [
                "photo" => "Must choose an image",
                "width" => "Must choose a width, value must be a positive integer",
                "height" => "Must choose a height, value must be a positive integer",
                "logo_color" => "Value must be either 'black' or 'white' or left empty",
                "logo_position" => "Value must be 'bottom-left', 'bottom-right', 'top-left', 'top-right' or left empty",
                "text" => "Value must be a string or left empty",
                "text_color" => "Value must be 'white' or 'black' or left empty",
                "text_box_width" => "Value must be a positive integer that is less than 'width' or left empty",
                "text_position_x" => "Value must be either 'left', 'center', 'right' or left empty",
                "text_position_y" => "Value must be either 'top', 'center', 'bottom' or left empty",
                "font_size" => "Value must be a positive integer or left empty",
                "brighten_darken" => "Value must be an integer between -100 and 100 or left empty",
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'Validation Errors' => $validation->errors(),
            ]);
        }
        
        $logo_color == null ? $logo_color = 'white': $logo_color = $logo_color; //standardvärde

        $logo_position == null ? $logo_position = 'bottom-left' : $logo_position = $logo_position; //standardvärde
        
        $text_box_width == null ? $text_box_width = $width * 0.6 : $text_box_width = $text_box_width; //standardvärde

        $text_color == null ? $text_color = 'black' : $text_color = $text_color; //standardvärde

        $text_position_x == null ? $text_position_x = 'center' : $text_position_x = $text_position_x; //standardvärde

        $text_position_y == null ? $text_position_y = 'center' : $text_position_y = $text_position_y; //standardvärde

        $font_size == null ? $font_size = '48' : $font_size = $font_size; //standardvärde

        $brighten_darken == null ? $brighten_darken = '0' : $brighten_darken = $brighten_darken; //standardvärde

        //här börjar redigeringen av bilden
        $image = Image::make($request->photo);

        $name = $photo->getClientOriginalName();

        $logo_size = (int) ($width / 12);

        $image->fit($width, $height);

        $image->colorize($brighten_darken, $brighten_darken, $brighten_darken);

        $logo_color === 'white' ? $logo = Image::make(public_path('watermark/white.png')) : $logo = Image::make(public_path('watermark/black.png'));

        $logo->resize(null, $logo_size, function ($constraint) {
            $constraint->aspectRatio();
        });

        $logo_padding = (int) ($width / 100);
        $image->insert($logo, $logo_position, $logo_padding, $logo_padding); //lägger in den omskalade loggan på rätt plats på bilden

        //här börjar skapandet och redigeringen av textrutan och texten i den
        $core_image = $image->getCore();
        $box = new Box($core_image);
        $box->setFontFace(storage_path('app/public/GT-Cinetype-Light.ttf'));

        $box->setFontSize($font_size);

        $text_color === 'black' ? $box->setFontColor(new Color(0, 0, 0)) : $box->setFontColor(new Color(255, 255, 255));

        $text_box_padding = (int) ($width / 50);

        if ($text_position_x === 'left') {
            $text_padding_x = $text_box_padding;
        }
        if ($text_position_x === 'center') {
            $text_padding_x = (int) (($width - $text_box_width) / 2);
        }
        if ($text_position_x === 'right') {
            $text_padding_x = ($width - $text_box_width) - $text_box_padding;
        }

        if ($text_position_y === 'top') {
            $text_padding_y = $text_box_padding;
        }
        if ($text_position_y === 'center') {
            $text_padding_y = null;
        }
        if ($text_position_y === 'bottom') {
            $text_padding_y = -1 * $text_box_padding;
        }

        $box->setBox($text_padding_x, $text_padding_y, $text_box_width, $height);
        $box->setTextAlign($text_position_x, $text_position_y);
        $box->draw($text);

        //sparar bilden
        $image_path = storage_path('app/public/' . $name);
        $image->save($image_path);

        //skriver ut en url till den redigerade bilden
        return response()->json([
            'url' => ['image' => asset('storage') . '/' . $name],
        ]);
    }
}
