<?php
    $careTips = [
        [
            'title' => 'Create cozy spaces',
            'description' => 'Offer a quiet corner with a soft blanket so your cat always has a safe retreat.'
        ],
        [
            'title' => 'Play a little every day',
            'description' => 'Short play sessions with a feather wand or laser pointer keep cats active and engaged.'
        ],
        [
            'title' => 'Keep routines predictable',
            'description' => 'Feed and groom at roughly the same times to reduce stress and build trust.'
        ]
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cat Care Tips</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="containerMain">

  <div class="header">
      <a href="index.php">Gallery</a>
      <a href="facts.php">Fun Facts</a>
      <a href="care.php" class="active">Care Tips</a>
  </div>

  <div class="title">
    <h2>#cats</h2>
    <p class="subtext">Simple habits for a happy cat</p>
  </div>

  <div class="content-card">
      <p>Daily care does not have to be complicated. Focus on comfort, play, and consistency to help your companion thrive.</p>
  </div>

  <div class="info-grid">
      <?php foreach ($careTips as $tip): ?>
          <div class="info-card">
              <h3><?= htmlspecialchars($tip['title'], ENT_QUOTES) ?></h3>
              <p><?= htmlspecialchars($tip['description'], ENT_QUOTES) ?></p>
          </div>
      <?php endforeach; ?>
  </div>

  <div class="footer">
      USM &copy; 2024
  </div>

</div>
</body>
</html>
