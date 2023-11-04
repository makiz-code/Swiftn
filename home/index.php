<?php

session_start();

$db_host = 'localhost';
$db_name = 'travel_agency';
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
  if (isset($_POST['offer-delete-id'])) {
    $travel_offer_id = $_POST['offer-delete-id'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND travel_offer_id = :travel_offer_id");
    $stmt->execute(['user_id' => $user_id, 'travel_offer_id' => $travel_offer_id]);
    $added = 0;
  }
} else {
  $class1 = "";
  $class2 = "show_nav";
}

if (isset($_POST['cart-update-id'])) {
  $active = true;
  if (isset($_POST["minus"])) {
    $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer o, cart c WHERE o.travel_offer_id = c.travel_offer_id AND cart_id = :id AND qte > 1");
    $stmt->execute(['id' => $_POST['cart-update-id']]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
      $stmt2 = $pdo->prepare("UPDATE cart SET qte = qte - 1 WHERE cart_id = :id");
      $stmt2->execute(['id' => $_POST['cart-update-id']]);
    }
  } else if (isset($_POST["plus"])) {
    $stmt = $pdo->prepare("SELECT count(*) as nb FROM travel_offer o, cart c WHERE o.travel_offer_id = c.travel_offer_id AND cart_id = :id AND qte < available_seats");
    $stmt->execute(['id' => $_POST['cart-update-id']]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
      $stmt2 = $pdo->prepare("UPDATE cart SET qte = qte + 1 WHERE cart_id = :id");
      $stmt2->execute(['id' => $_POST['cart-update-id']]);
    }
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

<body>

  <?php
  if (isset($_SESSION['authentified'])) {
    if (isset($added)) {
      echo "<script>Swal.fire('Deleted From Cart', '', 'warning')</script>";
    }
    $stmt = $pdo->prepare("SELECT count(*) as nb FROM payment WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['nb'] != 0) {
      $stmt = $pdo->prepare("DELETE FROM payment WHERE user_id = :user_id");
      $stmt->execute(['user_id' => $user_id]);
      echo "<script>Swal.fire('Successful Purchase', '', 'success')</script>";
    }
  }
  if (isset($send)) {
    echo "<script>Swal.fire('Message Sent', '', 'success')</script>";
  } ?>

  <section class="header">
    <div class="menu">
      <div id="menu-btn" class="fas fa-bars"></div>
      <a href="#" class="logo">swif<span>tn</span></a>
    </div>

    <nav class="navbar">
      <li><a href="#home"><i class="fa fa-home" aria-hidden="true"></i><span class="actual">home</span></a></li>
      <li><a href="#featured-destinations"><i class='fa fa-archway'></i><span>destinations</span></a></li>
      <li><a href="#about"><i class="fa fa-info-circle" aria-hidden="true"></i><span>about</span></a></li>
      <li><a href="#offers"><i class="fa fa-tag"></i><span>offers</span></a></li>
      <li><a href="#contact"><i class="fa fa-address-book"></i><span>contact</span></a></li>
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
      <img src="../img/img-1.jpg" id="img" alt="">
      <span>
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
      if ($stmt->rowCount() == 0) {
        $count = 0;
      }
      while ($cart = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="product">
          <img src=<?= $cart['travel_offer_src_image'] ?> alt="" />
          <div>
            <a class="name" href="../book/index.php?id=<?= $cart['travel_offer_id'] ?>">
              <?= $cart['travel_offer_name'] ?>
            </a>
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
          <form method="POST">
            <input type="hidden" name="offer-delete-id" value=<?= $cart['travel_offer_id'] ?> />
            <button type="submit"><i class="fa fa-trash"></i></button>
          </form>
        </div>

      <?php } ?>

    </div>
    <aside class="prices <?php if (!isset($count))
      echo 'inactif'; ?>">
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
          echo "<a href=../payment/index.php?link=http://localhost/swiftn/home/index.php>checkout</a>";
        }
        ?>
        <script>
          function empty() {
            Swal.fire('Empty Cart', '', 'warning');
          }
        </script>
      </div>
    </aside>
    <i class="fa-solid fa-rotate" data-count=<?php if (isset($count)) {
      echo $count;
    } else {
      echo '1';
    } ?>></i>
  </section>

  <section class="home" id="home">
    <div class="swiper-container">
      <div class="swiper-slide" data-bg="../img/bg1.jpg">
        <div class="content">
          <h4>never stop</h4>
          <h3>exploring</h3>
          <div class="icons">
            <i class='fas fa-plane medium'></i>
          </div>
        </div>
      </div>

      <div class="swiper-slide" data-bg="../img/bg2.jpg">
        <div class="content">
          <h4>make tour</h4>
          <h3>amazing</h3>
          <div class="icons">
            <i class='fas fa-plane first'></i>
            <i class='fas fa-plane last'></i>
          </div>
        </div>
      </div>

      <div class="swiper-slide" data-bg="../img/bg3.jpg">
        <div class="content">
          <h4>explore the</h4>
          <h3>new world</h3>
          <div class="icons">
            <i class='fas fa-plane first'></i>
            <i class='fas fa-plane medium'></i>
            <i class='fas fa-plane last'></i>
          </div>
        </div>
      </div>
      <button class="swiper-button-prev" disabled title="Previous">
        <i class="fa fa-angle-left"></i>
      </button>
      <button class="swiper-button-next" title="Next">
        <i class="fa fa-angle-right"></i>
      </button>
    </div>
  </section>

  <section class="featured-destinations" id="featured-destinations">
    <h2>Featured Destinations</h2>
    <div class="destinations-container">

      <?php

      $stmt = $pdo->prepare("SELECT * FROM destination");
      $stmt->execute();

      while ($destination = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="destination-card">
          <img src=<?= $destination['destination_src_image'] ?> />
          <h3>
            <?= $destination['destination_name'] ?>
          </h3>
          <p>
            <?= $destination['discover'] ?>
          </p>
          <a href="../dest/index.php?id=<?= $destination['destination_id'] ?>">Learn More</a>
        </div>
      <?php } ?>

    </div>
    <div class="items-swipe-btn">
      <button class="prev" disabled title="Previous">
        <i class="fa fa-angle-left"></i>
      </button>
      <button class="next" title="Next">
        <i class="fa fa-angle-right"></i>
      </button>
    </div>
    <div class="view-all">
      <button><a href="../destinations/index.php">view all</a></button>
    </div>
  </section>

  <hr />

  <section class="about" id="about">
    <div class="video">
      <video autoplay loop muted>
        <source src="../img/video.mp4" type="video/mp4" />
      </video>
    </div>
    <div class="about-content">
      <h3>about us</h3>
      <p>
        Discover the world with our travel agency. From stunning beaches to
        breathtaking landscapes, we offer personalized travel services that
        cater to your unique needs. Let us help you create unforgettable
        memories and experience new cultures. With our extensive knowledge and
        expertise, we are committed to delivering exceptional service and
        exceeding your expectations.
      </p>
      <button><a href="../about/index.php" class="btn">read more</a></button>
      <div class="plane"><i class="fa-solid fa-plane-departure"></i></div>
    </div>
    <div class="services-container">
      <div class="service">
        <i class="fas fa-mountain"></i>
        <h3>Adventure Tours</h3>
      </div>
      <div class="service">
        <i class="fas fa-map-marked-alt"></i>
        <h3>Tour Packages</h3>
      </div>
      <div class="service">
        <i class="fas fa-landmark"></i>
        <h3>Cultural Experiences</h3>
      </div>
      <div class="service">
        <i class="fas fa-gem"></i>
        <h3>Luxury Travel</h3>
      </div>
      <div class="service">
        <i class="fas fa-umbrella"></i>
        <h3>Travel insurance</h3>
      </div>
      <div class="service">
        <i class="fas fa-plane"></i>
        <h3>Flight Booking</h3>
      </div>
    </div>
  </section>

  <section class="offers" id="offers">
    <h2 class="offers-title">Special Offers</h2>
    <div class="offers-container">

      <?php

      $stmt = $pdo->prepare("SELECT * FROM travel_offer o, destination d WHERE o.destination_id = d.destination_id ORDER BY price DESC");
      $stmt->execute();
      $i = 0;
      while (($travel_offer = $stmt->fetch(PDO::FETCH_ASSOC)) && ($i < 9)) { ?>
        <div class="offer-card">
          <div class="image">
            <img src=<?= $travel_offer['travel_offer_src_image'] ?> class="offer-image" />
            <span id="date1">
              <?= $travel_offer['departure_date'] ?>
            </span>
            <span id="date2">
              <?= $travel_offer['return_date'] ?>
            </span>
          </div>
          <div class="offer-details">
            <h3 class="offer-name">
              <?= $travel_offer['travel_offer_name'] ?>
            </h3>
            <p class="offer-description">
              <?= $travel_offer['description'] ?>
            </p>
            <div class="offer-destinations">
              <span><i class="fa fa-map-marker"></i>
                <?= $travel_offer['destination_name'] ?>,
                <?= $travel_offer['destination_surname'] ?>
              </span>
            </div>
            <div class="offer-price">
              <p class="offer-price-text">Starting from</p>
              <p class="offer-price-value">
                <?= $travel_offer['price'] ?> €
              </p>
              <p class="offer-price-text">per person</p>
            </div>
            <form method="GET" action="../book/index.php">
              <input type="hidden" name="id" value=<?= $travel_offer['travel_offer_id'] ?> />
              <button type="submit" class="offer-btn">Book Now</button>
            </form>
          </div>
        </div>

        <?php $i += 1;
      } ?>

    </div>
    <button class="load-more">Load More</button>
  </section>

  <footer class="footer">
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
            <li><a href="#">Home</a></li>
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
        <form action="./index.php#contact" method="post">
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
      <span>&copy; Swiftn | Designed by <a href="#">Med Khalil Zrelly</a></span>
    </div>
  </footer>

  <button id="scroll-top-btn"><i class="fas fa-chevron-up"></i></button>

  <script src="script.js"></script>
</body>

</html>