<?php

session_start();

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

if (isset($_GET['id']) && !empty($_GET['id'])) {
  $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer WHERE travel_offer_id = :id");
  $stmt->execute(['id' => $_GET['id']]);
  if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
    $id = $_GET['id'];
  } else {
    header("Location: ../offers/index.php");
  }
} else {
  header("Location: ../offers/index.php");
}

if (isset($_GET["logout"])) {
  session_unset();
  session_destroy();
}

if (isset($_SESSION['authentified'])) {
  $class1 = "show_nav";
  $class2 = "";
  $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = :email");
  $stmt->execute(['email' => $_SESSION['email']]);
  $user_id = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];
  if (isset($_POST['offer_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = :user_id AND travel_offer_id = :travel_offer_id");
    $stmt->execute(['user_id' => $user_id, 'travel_offer_id' => $_POST['offer_id']]);
    if ($stmt->fetch(PDO::FETCH_NUM)[0] == 0) {
      $stmt = $pdo->prepare("SELECT available_seats FROM travel_offer WHERE travel_offer_id = :travel_offer_id");
      $stmt->execute(['travel_offer_id' => $_POST['offer_id']]);
      if ($stmt->fetch(PDO::FETCH_NUM)[0] > 0) {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, travel_offer_id, qte) VALUES (:user_id, :travel_offer_id, 1)");
        $stmt->execute(['user_id' => $user_id, 'travel_offer_id' => $_POST['offer_id']]);
        $added = 1;
      } else {
        $added = -1;
      }
    } else {
      $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer o, cart c WHERE o.travel_offer_id = c.travel_offer_id AND c.travel_offer_id = :id AND c.user_id = :id2 AND qte < available_seats");
      $stmt->execute(['id' => $_POST['offer_id'], 'id2' => $user_id]);
      if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
        $stmt2 = $pdo->prepare("UPDATE cart SET qte = qte + 1 WHERE travel_offer_id = :id AND user_id = :id2");
        $stmt2->execute(['id' => $_POST['offer_id'], 'id2' => $user_id]);
        $added = 2;
      } else {
        $added = -2;
      }
    }
  } else if (isset($_POST['offer-delete-id'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND travel_offer_id = :travel_offer_id");
    $stmt->execute(['user_id' => $user_id, 'travel_offer_id' => $_POST['offer-delete-id']]);
    $added = 0;
  }
} else {
  header("Location: ../authentification/index.php?link=http://localhost/swiftn/book/index.php?id=" . $id);
}

if (isset($_POST["minus"])) {
  $active = true;
  $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer o, cart c WHERE o.travel_offer_id = c.travel_offer_id AND cart_id = :id AND qte > 1");
  $stmt->execute(['id' => $_POST['cart-update-id']]);
  if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
    $stmt2 = $pdo->prepare("UPDATE cart SET qte = qte - 1 WHERE cart_id = :id");
    $stmt2->execute(['id' => $_POST['cart-update-id']]);
  }
}
if (isset($_POST["plus"])) {
  $active = true;
  $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer o, cart c WHERE o.travel_offer_id = c.travel_offer_id AND cart_id = :id AND qte < available_seats");
  $stmt->execute(['id' => $_POST['cart-update-id']]);
  if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
    $stmt2 = $pdo->prepare("UPDATE cart SET qte = qte + 1 WHERE cart_id = :id");
    $stmt2->execute(['id' => $_POST['cart-update-id']]);
  }
}

if (isset($_POST["send"])) {
  $stmt = $pdo->prepare("INSERT INTO contact (email, message) VALUES (:email, :message)");
  $stmt->execute(['email' => $_POST["email"], 'message' => $_POST["message"]]);
  $send = true;
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

<body data-id=<?= $id ?>>
  <?php
  if (isset($_SESSION['authentified'])) {
    if (isset($added) && !empty(isset($added))) {
      if ($added == 1) {
        echo "<script>Swal.fire('Added Succesfuly', '', 'success')</script>";
      } else if ($added == 2) {
        echo "<script>Swal.fire('+1 Seat', '', 'success')</script>";
      } else if ($added == 0) {
        echo "<script>Swal.fire('Deleted From Cart', '', 'warning')</script>";
      } else if ($added == -1) {
        echo "<script>Swal.fire('Offer Expired', '', 'error')</script>";
      } else if ($added == -2) {
        echo "<script>Swal.fire('Maximum Seats Reached', '', 'error')</script>";
      }
      $added = "";
    }
    $stmt = $pdo->prepare("SELECT count(*) as nb FROM payment WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
      echo "<script>Swal.fire('Successful Purchase', '', 'success')</script>";
      $stmt = $pdo->prepare("DELETE FROM payment WHERE user_id = :user_id");
      $stmt->execute(['user_id' => $user_id]);
    }
  } ?>

  <section class="header">
    <div class="menu">
      <div id="menu-btn" class="fas fa-bars"></div>
      <a href="../home/index.php" class="logo">swif<span>tn</span></a>
    </div>

    <nav class="navbar">
      <li><a href="../home/index.php">home</a></li>
      <li><a href="../destinations/index.php">destinations</a></li>
      <li><a href="../about/index.php">about</a></li>
      <li><a href="../offers/index.php">offers</a></li>
      <li><a href="#contact">contact</a></li>
    </nav>

    <div class="cta">
      <div class=<?= $class1 ?>>
        <button id="cart-btn" title="Cart">
          <i class="fa fa-shopping-cart"></i>
        </button>
        <button id="logout-btn" title="Logout">
          <i class="fa fa-sign-out"></i>
        </button>
      </div>
      <div class=<?= $class2 ?>>
        <button id="signup-btn">
          <i class="fa fa-user-plus"></i><span> register</span>
        </button>
        <button id="login-btn">
          <i class="fa fa-sign-in"></i><span> sign in</span>
        </button>
      </div>
      <button id="search-btn" title="Search">
        <i class="fa fa-search"></i>
      </button>
    </div>

    <div id="user" class=<?= $class1 ?>>
      <i class="fa fa-user"></i><span>
        <?= $_SESSION['name'] ?>
      </span>
    </div>
  </section>

  <section class="search" id="search">
    <div class="search-container">
      <button id="close-btn">
        <i class="fa fa-times"></i>
      </button>
      <form method="POST" action="../offers/index.php">
        <fieldset>
          <div class="search-form-name">
            <legend>search for travel offers</legend>
          </div>

          <div>
            <label for="Destination">destination*</label>
            <input id="Destination" list="destinations" type="search" name="destination"
              placeholder="search your desired destination..." required autocomplete="off">
            <datalist id="destinations">
              <?php
              $stmt = $pdo->prepare("SELECT destination_name FROM destination ORDER BY destination_id");
              $stmt->execute();

              while ($destination = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . $destination['destination_name'] . '">' . '</option>';
              }
              ?>
            </datalist>
          </div>

          <div>
            <label for="Dates">duration*</label>
            <input type="date" name="departure_date" id="departure_date" required />
            <input type="date" name="return_date" id="return_date" required />
          </div>

          <?php
          $stmt = $pdo->prepare("SELECT MIN(price) AS min, MAX(price) AS max, AVG(price) AS moy FROM travel_offer");
          $stmt->execute();

          if ($price = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

            <div class="price-container">
              <label for="Price">budget*</label>
              <input id="Price" type="range" name="price" min=<?= $price['min'] ?> max=<?= $price['max'] ?> step="10"
                value=<?= intval($price['moy']) ?> oninput="updatePriceValue()">
              <span id="price-value">
                <?= intval($price['moy']) ?>
              </span>
            </div>

          <?php } ?>

          <script>
            function updatePriceValue() {
              var priceInput = document.getElementById("Price");
              var priceValue = document.getElementById("price-value");
              priceValue.innerHTML = priceInput.value;
            }
          </script>

          <div class="submit-search">
            <span class="order down"><i class="fas fa-sort-amount-down"></i></span>
            <input type="hidden" name="order" id="order" value="" />
            <input type="submit" value="Search" />
            <span class="order up active"><i class="fas fa-sort-amount-up"></i></span>
          </div>

        </fieldset>
      </form>
    </div>
  </section>

  <?php
  if (isset($_SESSION['authentified'])) {
    $stmt3 = $pdo->prepare("SELECT * from cart where user_id = :id");
    $stmt3->execute(['id' => $user_id]);
    if ($stmt3->fetch(PDO::FETCH_ASSOC) == 0) {
      $gap = "0";
    } else {
      $gap = "5rem";
    }
  } ?>

    <section class="cart <?php if (isset($active)) {
      echo "active";
    } ?>" style="gap: <?= $gap ?>;">
      <div class="products">
        <?php

        $stmt = $pdo->prepare("SELECT * FROM user u, cart c, travel_offer o WHERE u.user_id = c.user_id AND o.travel_offer_id = c.travel_offer_id AND u.user_id = :user_id ORDER BY cart_id DESC");
        $stmt->execute(['user_id' => $user_id]);

        while ($cart = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
          <div class="product">
            <img src=<?= $cart['travel_offer_src_image'] ?> alt="" />
            <div>
              <p class="name">
                <?= $cart['travel_offer_name'] ?>
              </p>
              <span class="dates">
                <?= $cart['departure_date'] ?>
              </span>
              <span class="dates">
                <?= $cart['return_date'] ?>
              </span>
              <div class="price">
                <?= $cart['price'] ?>
              </div>
            </div>
            <div class="quantity">
              <form method="POST">
                <input type="hidden" name="cart-update-id" value=<?= $cart['cart_id'] ?> />
                <i class="fa fa-minus"><input type="submit" value="" name="minus"></i>
              </form>
              <span class="nbr">
                <?= $cart['qte'] ?>
              </span>
              <form method="POST">
                <input type="hidden" name="cart-update-id" value=<?= $cart['cart_id'] ?> />
                <i class="fa fa-plus" data-max=<?= $cart['available_seats'] ?>><input type="submit" value="" name="plus"></i>
              </form>
            </div>
            <form method="POST" action="./index.php?id=<?= $id ?>">
              <input type="hidden" name="offer-delete-id" value=<?= $cart['travel_offer_id'] ?> />
              <button type="submit"><i class="fa fa-trash"></i></button>
            </form>
          </div>

        <?php } ?>

      </div>
      <aside class="prices">
        <h1>cart summary</h1>
        <div class="container">
          <div class="value">
            <p>sub total</p>
            <span class="sub-total"></span>
          </div>
          <div class="value">
            <p>discount</p>
            <span class="discount"></span>
          </div>
          <div class="value">
            <p>total</p>
            <span class="total"></span>
          </div>
          <?php
          $stmt2 = $pdo->prepare("SELECT * from cart where user_id = :id");
          $stmt2->execute(['id' => $user_id]);
          if ($stmt2->fetch(PDO::FETCH_ASSOC) == 0) {
            echo "<a href=# onclick=empty()>checkout</a>";
            $errors = true;
          } else {
            echo "<a href=../payment/index.php?link=http://localhost/swiftn/book/index.php?id=" . $id . ">checkout</a>";
          }
          ?>
          <script>
            function empty() {
              Swal.fire('Empty Cart', '', 'warning');
            }
          </script>
        </div>
      </aside>
      </div>
    </section>

  <section class="offer">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM travel_offer o, destination d WHERE o.destination_id = d.destination_id AND o.travel_offer_id = :travel_offer_id");
    $stmt->execute(['travel_offer_id' => $id]);

    $travel_offer = $stmt->fetch(PDO::FETCH_ASSOC);
    $hotel_name = $travel_offer['hotel_name']; ?>

    <div class="airline">
      <img src=<?= $travel_offer['company_airline'] ?> />
      <div class="plane"><i class="fa-solid fa-plane-departure"></i></div>
    </div>
    <div class="container">
      <div class="offer-img">
        <img src=<?= $travel_offer['travel_offer_src_image'] ?> alt="Offer Image" />
        <div class="details">
          <h2>Flight Details</h2>
          <div class="row">
            <div class="label">From:</div>
            <div class="value">
              <?= $travel_offer['departure_date'] ?>
            </div>
          </div>
          <div class="row">
            <div class="label">To:</div>
            <div class="value">
              <?= $travel_offer['return_date'] ?>
            </div>
          </div>
          <div class="row">
            <div class="label">Seats:</div>
            <div class="value">
              <i class="fa-sharp fa-solid fa-chair"></i>
              <span>
                <?= $travel_offer['available_seats'] ?>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="offer-details">
        <h1>
          <?= $travel_offer['travel_offer_name'] ?>
        </h1>
        <p>
          <?= $travel_offer['description'] ?>
        </p>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Price</th>
                <th>Duration</th>
                <th>Location</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>$
                  <?= $travel_offer['price'] ?>
                </td>
                <td>
                  <?php $duration_days = floor((strtotime($travel_offer['return_date']) - strtotime($travel_offer['departure_date'])) / 86400);
                  echo $duration_days . " days" ?>
                </td>
                <td><a href="../dest/index.php?id=<?= $travel_offer['destination_id'] ?>"><?= $travel_offer['destination_name'] ?>,
                    <?= $travel_offer['destination_surname'] ?></a></td>
              </tr>
            </tbody>
          </table>
        </div>
        <form method="POST" action="./index.php?id=<?= $id ?>">
          <input type="hidden" name="offer_id" value=<?= $travel_offer['travel_offer_id'] ?> />
          <button type="submit" name="add_cart" class="offer-btn">
            add to cart
          </button>
        </form>
      </div>
    </div>
  </section>

  <hr />

  <section class="hotel">
    <div class="container">
      <div class="services-container">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM travel_offer o, hotel_service s, hs WHERE o.travel_offer_id = hs.travel_offer_id AND s.service_id = hs.service_id AND o.travel_offer_id = :travel_offer_id");
        $stmt->execute(['travel_offer_id' => $id]);

        while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
          <article class="service">
            <div class="service-icon">
              <span>
                <i class="<?= $service['service_logo'] ?>"></i>
              </span>
            </div>
            <div class="service-content">
              <h2>
                <?= $service['service_name'] ?>
              </h2>
              <p>
                <?= $service['paragraph'] ?>
              </p>
            </div>
          </article>

        <?php } ?>

      </div>

      <div class="about">
        <div class="image">
          <?php
          $stmt = $pdo->prepare("SELECT * FROM travel_offer o, destination d, hotel_image i WHERE o.destination_id = d.destination_id AND d.destination_id = i.destination_id AND o.travel_offer_id = :travel_offer_id ORDER BY image_id");
          $stmt->execute(['travel_offer_id' => $id]);

          while ($image = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
            <img src=<?= $image['image_name'] ?> class="offer-image" />
          <?php } ?>
        </div>
        <div class="hotel-info">

          <h3 class="hotel-name">
            <?= $hotel_name ?> hotel
          </h3>
          <div class="hotel-icon">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM travel_offer WHERE travel_offer_id = :travel_offer_id");
            $stmt->execute(['travel_offer_id' => $id]);
            $travel_offer = $stmt->fetch(PDO::FETCH_ASSOC);
            for ($i = 0; $i < $travel_offer['hotel_rating']; $i++) { ?>
              <i class="fa-solid fa-star"></i>

            <?php } ?>

          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="offers" id="offers">
    <h2 class="offers-title">you may also like</h2>
    <div class="offers-container">

      <?php $stmt = $pdo->prepare("SELECT * FROM travel_offer o, destination d WHERE o.destination_id = d.destination_id AND o.travel_offer_id != :id ORDER BY price desc");
      $stmt->execute(['id' => $id]);
      $i = 0;
      while ($travel_offer = $stmt->fetch(PDO::FETCH_ASSOC) and $i < 3) { ?>
        <div class="offers-card">
          <div class="image">
            <img src=<?= $travel_offer['travel_offer_src_image'] ?> class="offers-image" />
            <span id="date1">
              <?= $travel_offer['departure_date'] ?>
            </span>
            <span id="date2">
              <?= $travel_offer['return_date'] ?>
            </span>
          </div>
          <div class="offers-details">
            <h3 class="offers-name">
              <?= $travel_offer['travel_offer_name'] ?>
            </h3>
            <p class="offers-description">
              <?= $travel_offer['description'] ?>
            </p>
            <div class="offers-destinations">
              <span><i class="fa fa-map-marker"></i>
                <?= $travel_offer['destination_name'] ?>,
                <?= $travel_offer['destination_surname'] ?>
              </span>
            </div>
            <div class="offers-price">
              <p class="offers-price-text">Starting from</p>
              <p class="offers-price-value">
                <?= $travel_offer['price'] ?> €
              </p>
              <p class="offers-price-text">per person</p>
            </div>
            <a href="../book/index.php?id=<?= $travel_offer['travel_offer_id'] ?>" class="offers-btn">Book Now</a>
          </div>
        </div>
        <?php $i++;
      }
      ?>

    </div>
  </section>

  <footer class="footer">

    <?php
    if (isset($send) && !empty(isset($send))) {
      echo "<script>Swal.fire('Message Sent', '', 'success')</script>";
      $added = "";
    } ?>

    <div class="footer-content">
      <div class="container">
        <div class="footer-section about">
          <h1 class="logo-text">Swif<span>tn</span></h1>
          <p>
            We are a full-service travel agency dedicated to providing you
            with personalized travel experiences that will create lasting
            memories.
          </p>
          <div class="contact" id="contact">
            <p><i class="fas fa-phone"></i> &nbsp; +216 74 666 855</p>
            <p><i class="fas fa-envelope"></i> &nbsp; info@Swiftn.tn</p>
          </div>
          <div class="socials">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
          </div>
        </div>
        <div class="footer-section quick-links">
          <h2>Quick Links</h2>
          <ul>
            <li><a href="../home/index.php">Home</a></li>
            <li><a href="../destinations/index.php">Destinations</a></li>
            <li>
              <a href="../about/index.php">About <span>Us</span></a>
            </li>
            <li><a href="../offers/index.php">Offers</a></li>
            <li>
              <a href="#contact">Contact <span>Us</span></a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-section contact-form">
        <h2>Contact Us</h2>
        <form action="./index.php?id=<?= $id ?>#contact" method="post">
          <input type="email" name="email" class="text-input contact-input" placeholder="Your email address" required
            autocomplete="off" />
          <textarea name="message" class="text-input contact-input" placeholder="Your message" required
            autocomplete="off"></textarea>
          <button type="submit" name="send" class="btn btn-big contact-btn">
            <i class="fas fa-envelope"></i> Send
          </button>
        </form>
      </div>
    </div>
    <hr />
    <div class="footer-bottom">
      <span>&copy; Swiftn | Designed by <a href="#">Med Khalil Zrelly</a> &
        <a href="#">Hajer Talbi</a></span>
    </div>
  </footer>

  <button id="scroll-top-btn"><i class="fas fa-chevron-up"></i></button>

  <script src="script.js"></script>
</body>

</html>