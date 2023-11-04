<?php

session_start();

if (isset($_SESSION['authentified'])) {
  header('Location: ../home/index.php');
}

if (isset($_GET['link']) && strpos($_GET['link'], 'localhost/swiftn/')) {
  $link = $_GET['link'];
} else {
  $link = 'http://localhost/swiftn/home/index.php';
}

$errors = [];

if ($_POST) {

  extract($_POST);

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

  if (isset($name)) {
    $role = 0;
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      $errors['unique_email'] = "Email already exists";
    }
    if ($password != $verify_password) {
      $errors['verify_password'] = "Passwords do not match";
    }
    if (empty($errors)) {
      $stmt = $pdo->prepare("INSERT INTO user (user_name, email, password, role) VALUES (:name, :email, :password, :role)");
      $stmt->execute(['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role]);
      $_SESSION['authentified'] = true;
      $_SESSION['name'] = $name;
      $_SESSION['email'] = $email;
      header('Location: ' . $link);
      exit;
    }
  } else {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email AND password = :password");
    $stmt->execute(['email' => $email, 'password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      $_SESSION['authentified'] = true;
      $_SESSION['name'] = $user['user_name'];
      $_SESSION['email'] = $email;
      header('Location: ' . $link);
      exit;
    } else {
      $errors['login'] = "Invalid email or password";
    }
  }
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
</head>

<body>
  <?php
  if (isset($_GET['register'])) {
    $class = "active";
  } else {
    $class = "";
  }
  ?>
  <div class="container <?= $class ?>">
    <div class="forms">
      <?php if (!isset($_GET['register'])) { ?>
        <div class="form login">
          <span class="title">Login</span>
          <form method="POST">
            <div class="input-field">
              <input type="email" name="email" id="" placeholder="Enter your email" value="<?php if (isset($_POST['email'])) {
                echo $_POST['email'];
              } ?>" required autocomplete="off" />
              <i class="fas fa-envelope"></i>
            </div>
            <div class="input-field">
              <input type="password" name="password" id="" class="password" placeholder="Enter your password" required
                <?php if (isset($_POST['password'])) {
                  echo "autofocus";
                } ?> autocomplete="off" />
              <i class="fas fa-lock"></i>
              <i class="fas fa-eye-slash showHidePw"></i>
            </div>
            <div class="checkbox-text">
              <div class="checkbox-content">
                <input type="checkbox" id="logCheck" />
                <label for="logCheck" class="text">Remember me</label>
              </div>
              <a href="#" class="text">Forgot password ?</a>
            </div>
            <div class="submit-form">
              <div class="input-field button">
                <input type="submit" value="Login Now" />
              </div>
              <?php if (isset($errors['login'])) { ?>
                <span class="error">
                  <?php echo $errors['login']; ?>
                </span>
              <?php } ?>
            </div>
          </form>
          <div class="login-signup">
            <span class="text">
              <span>Not a member ?</span><a href="./index.php?register=true&link=<?= $link ?>"
                class="text signup-link">Signup Now</a>
            </span>
          </div>
        </div>
      <?php } else { ?>
        <div class="form signup">
          <span class="title">Sign Up</span>
          <form method="POST">
            <div class="input-field">
              <input type="text" name="name" id="" placeholder="Enter your name" value="<?php if (isset($_POST['name'])) {
                echo $_POST['name'];
              } ?>" required autocomplete="off" />
              <i class="fas fa-user"></i>
            </div>
            <div class="input-field">
              <input type="email" name="email" id="" placeholder="Enter your email" value="<?php if (isset($_POST['email']) && !isset($errors['unique_email'])) {
                echo $_POST['email'];
              } ?>" required <?php if (isset($errors['unique_email'])) {
                 echo "autofocus";
               } ?> autocomplete="off" />
              <i class="fas fa-envelope"></i>
            </div>
            <div class="input-field">
              <input type="password" name="password" id="" class="password" placeholder="Create your password" required
                <?php if (isset($errors['verify_password'])) {
                  echo "autofocus";
                } ?> autocomplete="off" />
              <i class="fas fa-lock icon"></i>
            </div>
            <div class="input-field">
              <input type="password" name="verify_password" id="" class="password" placeholder="Confirm your password"
                required autocomplete="off" />
              <i class="fas fa-lock icon"></i>
              <i class="fas fa-eye-slash showHidePw"></i>
            </div>
            <div class="checkbox-text">
              <div class="checkbox-content">
                <input type="checkbox" id="sigCheck" required />
                <label for="sigCheck" class="text">Accept terms & conditions</label>
              </div>
            </div>
            <div class="submit-form">
              <div class="input-field button">
                <input type="submit" value="Register Now" />
              </div>
              <span class="error">
                <?php if (isset($errors['unique_email'])) {
                  echo $errors['unique_email'];
                } else if (isset($errors['verify_password'])) {
                  echo $errors['verify_password'];
                } ?>
              </span>
            </div>
            <div class="login-signup">
              <span class="text">
                <span>Already a member ?</span>
                <a href="./index.php?link=<?= $link ?>" class="text login-link">Login Now</a>
              </span>
            </div>
          </form>
        </div>
      <?php } ?>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>