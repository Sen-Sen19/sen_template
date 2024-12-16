<?php require 'process/login.php';

if (isset($_SESSION['username'])) {
  if ($_SESSION['role'] == 'admin') {
    header('location: /my_template/page/admin/accounts.php');

     exit;
 }elseif($_SESSION['role'] == 'user'){
     header('location: page/user/accounts.php');
     exit;
 }
}
?>

<style>
body {
  background: url('dist/img/background5.jpg') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

.transparent-box {
  background: linear-gradient(to right, rgba(0, 0, 0, 0.2) 60%, white 60%);
  backdrop-filter: blur(10px);
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 800px;
  height: 500px;
  display: flex;
  flex-direction: column;
  position: relative;
  justify-content: center;
  align-items: center;
}

.transparent-box::after {
  content: "";
  background: url('dist/img/crow.png') no-repeat center center;
  background-size: contain; 
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 40%;
}
.form-title {
  text-align: center;
  font-size: 3em;
  margin-bottom: 20px;

  color: white;
  margin-right: 40%;
  font-family: 'voor', sans-serif;
                                                
}

.form-group {
  margin-bottom: 15px;
  width: 100%;
  max-width: 250px; 
  margin-right:350px;
}
label {
  font-size: 1em;
  color: #fff;
  display: block;
  margin-bottom: 5px;
  font-family: 'perry gothic', sans-serif;
  
}

.form-control {
  width: 100%;
  background: transparent !important;
  border: 1px solid darkgreen !important;
  color: white !important;
  padding: 10px;
  font-size: 1em;
  border-radius: 60px !important;
}

.btn-block {
  position: relative;
  width: 100%;
  max-width: 250px;
  background: #fff;
  color: #000;
  padding: 8px 16px;
  font-size: 14px;
  border: none;
  border-radius: 25px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  margin-top: 20px;
}

.btn-block:hover {
  background: #6c757d;
  color: #fff;
  cursor: pointer;
}

</style>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sen Template</title>

  <link href="https://fonts.cdnfonts.com/css/voor" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/perry-gothic" rel="stylesheet">

  <link rel="icon" href="dist/img/nature.png" type="image/x-icon" />
  <link rel="stylesheet" href="dist/css/font.min.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
<body>

  <div class="transparent-box">
    <div class="form-title">Sen</div>

    <form action="process/login.php" method="POST"> 
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" autocomplete="off" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="off" required>
      </div>

      <?php
      if (isset($_GET['login_failed']) && $_GET['login_failed'] == 'true') {
          echo '<div class="alert alert-danger" role="alert">Incorrect username or password. Please try again.</div>';
      }
      ?>

      <div class="row mb-2">
        <div class="col-12">
          <button type="submit" class="btn-block" name="Login" value="login">Login</button>
        </div>
      </div>
    </form>

  </div>

  <script src="plugins/jquery/dist/jquery.min.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>

</body>
</html>
