<?php

    /**
     * Recursively scan the directory and its subdirectories for
     * images with the extension .jpg and display them.
     *
     * @param string $dir The directory to be scanned.
     *
     * @return void
     */
    function placeImages(string $dir): void
    {
        $files = scandir($dir);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'jpg') {
                    $path = $dir . $file;
                    echo "<img src='$path' alt='Cat'>";
                }
            }
        } else {
            echo "<p>No images found.</p>";
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cat Gallery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="containerMain">


  <!-- Header with navigation -->
  <div class="header">
      <a href="index.php" class="active">Gallery</a>
      <a href="facts.php">Fun Facts</a>
      <a href="care.php">Care Tips</a>
  </div>

  <!-- Title -->
  <div class="title">
    <h2>#cats</h2>
    <p class="subtext">Explore a world of cats</p>
  </div>

  <!-- Gallery -->
  <div class="gallery">
      <?php
        placeImages("./image/");
      ?>
  </div>

  <!-- Footer -->
  <div class="footer">
      USM &copy; 2024
  </div>

</div>
</body>
</html>
