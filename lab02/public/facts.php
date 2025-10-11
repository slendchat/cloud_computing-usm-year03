<?php
    $catFacts = [
        'Cats sleep between 12 and 16 hours every day.',
        'A group of kittens is called a kindle.',
        'Cats use whiskers to sense the world around them.',
        'The pattern on every cat nose is unique, much like a human fingerprint.',
        'Felines can rotate their ears a full 180 degrees.'
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
    <title>Cat Fun Facts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="containerMain">

  <div class="header">
      <a href="index.php">Gallery</a>
      <a href="facts.php" class="active">Fun Facts</a>
      <a href="care.php">Care Tips</a>
  </div>

  <div class="title">
    <h2>#cats</h2>
    <p class="subtext">Fun stories and trivia about cats</p>
  </div>

  <div class="content-card">
      <p>Cats have fascinated people for thousands of years. Here are a few quick facts that show just how special they are:</p>
      <ul class="fact-list">
          <?php foreach ($catFacts as $fact): ?>
              <li><?= htmlspecialchars($fact, ENT_QUOTES) ?></li>
          <?php endforeach; ?>
      </ul>
  </div>

  <div class="footer">
      USM &copy; 2024
  </div>

</div>
</body>
</html>
