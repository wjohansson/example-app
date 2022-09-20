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
    <a href="/stored/{{$name}}" download class="download-btn">Download
        <i class="fa fa-download"></i>
    </a>
    <br>
    <img src="/stored/{{$name}}" height="648px" class="center">
    <br>
    <form method = "POST">
        <button onclick=history.back() class="button">Go Back</button>
    </form>

</body>
</html>