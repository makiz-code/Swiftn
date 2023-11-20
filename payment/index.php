<?php

session_start();

if (!isset($_SESSION['authentified'])) {
  header('Location: ../authentification/index.php');
}

if (isset($_GET['link']) && strpos($_GET['link'], 'localhost/swiftn/')) {
  $link = $_GET['link'];
} else {
  $link = 'http://localhost/swiftn/home/index.php';
}

$db_host = 'localhost';
$db_name = 'Swiftn';
$db_username = 'root';
$db_password = '7102';

try {
  $options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false
  ];
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password, $options);
} catch (PDOException $e) {
  echo 'Error connecting to database: ' . $e->getMessage();
  exit;
}



if (isset($_POST["pay"])) {
  $stmt1 = $pdo->prepare("SELECT user_id FROM user WHERE email = :email");
  $stmt1->execute(['email' => $_SESSION['email']]);
  $user_id = $stmt1->fetch(PDO::FETCH_ASSOC)['user_id'];

  $stmt1 = $pdo->prepare("INSERT INTO payment(user_id) VALUES (:user_id)");
  $stmt1->execute(['user_id' => $user_id]);

  $stmt2 = $pdo->prepare("SELECT * from cart where user_id = :id");
  $stmt2->execute(['id' => $user_id]);
  while ($cart = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $stmt = $pdo->prepare("UPDATE travel_offer SET available_seats = available_seats - :qte WHERE travel_offer_id = :id");
    $stmt->execute(['qte' => $cart['qte'], 'id' => $cart['travel_offer_id']]);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = :id AND user_id = :id2");
    $stmt->execute(['id' => $cart['cart_id'], 'id2' => $cart['user_id']]);
  }
  header('Location: ' . $link);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Swiftn</title>
  <link rel="shortcut icon" href="../img/airplane.png" type="image/x-icon">
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="../fa/css/all.css" />
  <script src="../cdnjs/sweetalert2@11.js"></script>
</head>

<body>
  <div class="container">
    <form method="POST">
      <div class="row">

        <div class="col">
          <h3 class="title">billing address</h3>
          <div class="inputBox">
            <span>full name :</span>
            <input type="text" placeholder="Med khalil Zrelly" required autocomplete="off" />
          </div>
          <div class="inputBox">
            <span>email :</span>
            <input type="email" placeholder="example@example.com" required autocomplete="off" />
          </div>
          <div class="inputBox">
            <span>address :</span>
            <input type="text" placeholder="room - street - locality" required autocomplete="off" />
          </div>
          <div class="inputBox">
            <span>city :</span>
            <input type="text" placeholder="Ezzahra" required autocomplete="off" />
          </div>
          <div class="flex">
            <div class="inputBox">
              <span>state :</span>
              <input type="text" placeholder="Tunis" required autocomplete="off" />
            </div>
            <div class="inputBox">
              <span>zip code :</span>
              <input type="text" placeholder="2034" required autocomplete="off" />
            </div>
          </div>
        </div>

        <div class="col">
          <h3 class="title">payment</h3>
          <div class="inputBox">
            <span>cards accepted :</span>
            <img src="../img/card_img.png" alt="" />
          </div>
          <div class="inputBox">
            <span>name on card :</span>
            <input type="text" placeholder="mr. Med Khalil Zrelly" required autocomplete="off" />
          </div>
          <div class="inputBox">
            <span>credit card number :</span>
            <input type="number" placeholder="1111-2222-3333-4444" required autocomplete="off" />
          </div>
          <div class="inputBox">
            <span>exp month :</span>
            <input type="text" placeholder="january" required autocomplete="off" />
          </div>
          <div class="flex">
            <div class="inputBox">
              <span>exp year :</span>
              <input type="number" placeholder="2022" required autocomplete="off" />
            </div>
            <div class="inputBox">
              <span>CVV :</span>
              <input type="text" placeholder="1234" required autocomplete="off" />
            </div>
          </div>
        </div>
      </div>

      <input type="submit" value="proceed to checkout" name="pay" class="submit-btn" />
    </form>
  </div>
</body>

</html>