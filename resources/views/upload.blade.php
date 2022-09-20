
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lundqvist Trä</title>
    <link rel="stylesheet" href="/css/style.css">
<body>
    <div class="header">
    <h1>Upload image</h1>
    </div>
    <br>
    <form method="POST" action="/upload" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file"><br>

        <p><strong>Position of the image to keep:</strong></p>

        <select id="fitlocation" name="fitlocation">
            <option value="0">Select position:</option>
            <option value="center">Center</option>
            <option value="left">Left</option>
            <option value="right">Right</option>
            <option value="top">Top</option>
            <option value="bottom">Bottom</option>
        </select>

        <p><strong>Color of watermark:</strong></p>

        <input type="radio" id="white" name="wmcolor" value="white">
        <label for="white">White</label><br>
        <input type="radio" id="black" name="wmcolor" value="black">
        <label for="black">Black</label><br>

        <p><strong>Location of watermark:</strong></p>

        <select id="wmlocation" name="wmlocation">
            <option value="0">Select location:</option>
            <option value="top-left">Top left</option>
            <option value="top-right">Top right</option>
            <option value="bottom-left">Bottom left</option>
            <option value="bottom-right">Bottom right</option>
        </select>

        <p><strong>Size of watermark:</strong></p>

        <input type="radio" id="big" name="wmsize" value="big">
        <label for="big">Big</label><br>
        <input type="radio" id="small" name="wmsize" value="small">
        <label for="small">Small</label><br>

        <p><strong>Social media platform:</strong></p>

        <input type="radio" id="facebookhz" name="sm" value="facebookhz">
        <label for="facebookhz">Facebook Horizontal</label><br>
        <input type="radio" id="facebookvt" name="sm" value="facebookvt">
        <label for="facebookvt">Facebook Vertical</label><br>
        <input type="radio" id="instagram" name="sm" value="instagram">
        <label for="instagram">Instagram</label><br>

        <p><strong>Choices:</strong></p>

        <input type="checkbox" id="wmopacity" name="wmopacity">
        <label for="wmopacity">Transparent watermark</label><br>
        <input type="checkbox" id="greyscale" name="greyscale">
        <label for="greyscale">Greyscale image</label><br>
        <br>
        <input type="image" name="Upload" src="/stored/monkeythink.png" height="100px">
        <strong>Click the monkey to submit</strong>
    </form>

</body>
</html>