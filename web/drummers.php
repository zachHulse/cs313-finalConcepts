<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
    $dbUrl = getenv('DATABASE_URL');
  if (empty($dbUrl)) {
    $dbUrl = "postgres://postgres:doublebass21@localhost:5432/mydb";
  }
  $dbopts = parse_url($dbUrl);
  $dbHost = $dbopts["host"];
  $dbPort = $dbopts["port"];
  $dbUser = $dbopts["user"];
  $dbPassword = $dbopts["pass"];
  $dbName = ltrim($dbopts["path"],'/');
  try {
    $db = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
  }
  catch (PDOException $ex) {
    print "<p>error: $ex->getMessage() </p>\n\n";
    die();
  }
?>

<!doctype html>
<html>
  <head>
    <title>Drummers</title>
    <link rel="stylesheet" type="text/css" href="hello.css">
  </head> 
  <body>
    <h1>Drummers</h1>
    <i>Select a drummer to see their gear</i>
    <?php
    $stmt = $db->prepare('SELECT * FROM drummer');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <form action="drummers.php" method="post" id="select_form">
    <select onchange="document.getElementById('select_form').submit()" name="drummers">
      <option value=" "> </option>
    <?php  
    foreach($rows as $person)
         {
           echo '<option value="' . $person['name'] . '">' . $person['name'] . '</option>' . '<br/>';
         }
     ?>    
    </select>
    </form>
    <?php
    if(isset($_POST['drummers'])) // user selected a drummer
    {
      $name = $_POST['drummers']; // get drummers info
      $stmt2 = $db->prepare('SELECT * FROM drummer WHERE NAME=:name');
      $stmt2->bindValue(':name', $name, PDO::PARAM_STR);
      $stmt2->execute();
      $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
      $cymbal_id = $rows2[0]['cymabl_id'];
      $drum_id = $rows2[0]['drum_id'];

      $stmt3 = $db->prepare('SELECT * FROM drum_config WHERE id=:drum_id'); // get drummers drums
      $stmt3->bindValue(':drum_id', $drum_id, PDO::PARAM_INT);
      $stmt3->execute(); 
      $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
      $Dcompany = $rows3[0]['company'];
      $model = $rows3[0]['kit_model'];
      $kick = $rows3[0]['kick'];
      $snare = $rows3[0]['snare'];
      $toms = $rows3[0]['toms'];

      $stmt4 = $db->prepare('SELECT * FROM cymbal_config WHERE id=:cymbal_id'); // get drumers cymbal set-up
      $stmt4->bindValue(':cymbal_id', $cymbal_id, PDO::PARAM_INT);
      $stmt4->execute();
      $rows4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
      $ride_id = $rows4[0]['ride_id'];
      $hats_id = $rows4[0]['hats_id'];
      $crash_id = $rows4[0]['crash_id'];
      $Ccompany = $rows4[0]['company'];

      $stmt5 = $db->prepare('SELECT * FROM cymbal WHERE id=:ride_id OR id=:hats_id OR id=:crash_id'); // get drummers individual cymbals
      $stmt5->bindValue(':ride_id', $ride_id, PDO::PARAM_INT);
      $stmt5->bindValue(':hats_id', $hats_id, PDO::PARAM_INT);
      $stmt5->bindValue(':crash_id', $crash_id, PDO::PARAM_INT);
      $stmt5->execute();
      $rows5 = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    }

    echo '<h2>' . $name . '</h2>'; // display info 
    echo '<p>' . "Company: ". $Dcompany . '</p>';
    echo '<p>' . "Kit Model: " . $model . '</p>';
    echo '<p>' . "Kick Drum Size: " . $kick . '</p>';
    echo '<p>' . "Tom Sizes: " . $toms . '</p>';
    echo '<p>' . "Snare: " .  $snare . '</p>';
    echo '<p>' . "Cymbal Company: " .  $Ccompany . '<p>';
    echo '<p>' . "Cymbals: " . '<p>';
     foreach ($rows5 as $unit) {
       echo '<p>' . $unit['size'] . 'in. ' . $unit['name'] . '</p>';
     }
    ?>
  </body>
</html>