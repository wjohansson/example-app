<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lundqvist Trä</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="header">
        <h1>Image</h1>
    </div>
    <br>
    <div class="container">
        <img src="/stored/{{$name}}" height="648px" class="center">
        <div class="middle">
            <a href="/stored/{{$name}}" download class="download-btn">Download</a>
        </div>
    </div>   
    <p class="downloaddisp"><strong>Hover image to download</strong></p>
    <br><br>
    <form method = "POST">
        <button onclick=history.back() class="button">Go Back</button>
    </form>

</body>
</html>