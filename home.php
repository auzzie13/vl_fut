<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoiceLove FUT</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header class="header">
        <img src="./assets/vl logo.png" alt="VL Logo">
    </header>

    <!-- Main container to hold everything -->
    <div class="main-container">
        <!-- Left Bookends (4 lines) -->
        <div class="bookend-group">
            <div class="bookend bookend-left1"></div>
            <div class="bookend bookend-left2"></div>
            <div class="bookend bookend-left3"></div>
            <div class="bookend bookend-left4"></div>
        </div>


        <!-- Main Element (e.g., Book a demo section) -->

        <form id="myForm" class="main-element" enctype="multipart/form-data">

     
            <div id="radio-btn-container">
            <div><input type="radio" id="message-data" class="radio" name="radio" value="message-data">
            <label for="type-1">Message Data</label></div>

            <div><input type="radio" id="channel-data" class="radio" name="radio" value="channel-data">
            <label for="type-2">Channel Data</label></div>

            <div><input type="radio" id="unique-senders" class="radio" name="radio" value="unique-senders">
            <label for="type-3">Unique Senders</label></div>
            </div>
     

            <div id="input-container">
                <label for="myFile" id="file-label">Select File</label>
                <input type="file" id="myFile" name="myFile">
            </div>
            <button id="btn" type="submit">Submit</button>
            
        </form>

        <!-- Right Bookends (4 lines) -->
        <div class="bookend-group">
            <div class="bookend bookend-right1"></div>
            <div class="bookend bookend-right2"></div>
            <div class="bookend bookend-right3"></div>
            <div class="bookend bookend-right4"></div>
        </div>

    </div>
</body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="script.js"></script>
</html>




