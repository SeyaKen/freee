<?php  
  require "functions.php";

  // 👇エラーを表示してくれる関数
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  check_login();
  // 👇投稿を削除する処理
  if($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['action']) && $_POST['action'] == 'post_delete') {
    // Profileのデータを消す処理
    $id = $_GET['id'] ?? 0;
    // $_GET['id']がなかったら0を代入
    $user_id = $_SESSION['info']['id'];

    $query = "select * from posts where id = '$id' && user_id = '$user_id' limit 1";
    $result = mysqli_query($con, $query);
    if(mysqli_num_rows($result) > 0){

      $row = mysqli_fetch_assoc($result);
      // 👇投稿を消すときに写真も消す処理
      if(file_exists($row['image'])){
      unlink($row['image']);
      }
    }
    
    $query = "delete from posts where id = '$id' && user_id = '$user_id' limit 1";
    $result = mysqli_query($con, $query);

    header("Location: profile.php");
    die;
  }
  // 👇投稿を編集する処理
  elseif($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['action'] == "post_edit"))
  {
    $id = $_GET['id'] ?? 0;
    // $_GET['id']がなかったら0を代入
    $user_id = $_SESSION['info']['id'];
    $image_added = false;
    // 👇画像がある場合
    if(!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
      // file was uploaded
      $folder = "uploads/";
      if(!file_exists($folder)){
        
        mkdir($folder, 0777, true);
      }
      $image = $folder . $_FILES['image']['name'];
      move_uploaded_file($_FILES['image']['tmp_name'], $image);

      $query = "select * from posts where id = '$id' && user_id = '$user_id' limit 1";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) > 0){

          $row = mysqli_fetch_assoc($result);
          // 👇投稿を編集するときに前の写真を消す処理
          if(file_exists($row['image'])){
            unlink($row['image']);
          }
        }

      $image_added = true;

      }
      
      $post = addslashes($_POST['post']);
      // addslashedは自動で\を追加してくれる
      // \はKen's Breadなどの「'」を文字列として認識するためのもの

      if($image_added == true) {
        $query = "update posts set post = '$post', image = '$image' where id = '$id' && user_id = '$user_id' limit 1 ";
      } else {
        $query = "update posts set post = '$post' where id = '$id && user_id = '$user_id' limit 1 ";
      }

      $result = mysqli_query($con, $query);

      header("Location: profile.php");
      die;
    }
    // 👇プロフィールを削除（退会）する処理
    elseif($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_SESSION['info']['id'];
    $query = "delete from users where id = '$id' limit 1";
    $result = mysqli_query($con, $query);

    // 👇退会するときに写真も消す処理
    if(file_exists($_SESSION['info']['image'])){
      unlink($_SESSION['info']['image']);
    }

    // 👇退会するときに投稿も消す処理
    $query = "delete from posts where user_id ='$id'";
    $result = mysqli_query($con, $query);

    header("Location: logout.php");
    die;
  }
  // 👇プロフィールを登録する処理
  elseif($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['username']))
  {
    $image_added = false;
    if(!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
      // file was uploaded
      $folder = "uploads/";
      if(!file_exists($folder)){
        
        mkdir($folder, 0777, true);
      }
      $image = $folder . $_FILES['image']['name'];
      move_uploaded_file($_FILES['image']['tmp_name'], $image);

      // 👇古い写真を自動で消す処理
      // if(file_exists($_SESSION['info']['image'])){
      //   unlink($_SESSION['info']['image']);
      // }

      $image_added = true;

    }
    
    $username = addslashes($_POST['username']);
    // addslashedは自動で\を追加してくれる
    // \はKen's Breadなどの「'」を文字列として認識するためのもの
    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);
    $id = $_SESSION['info']['id'];

    if($image_added == true) {
      $query = "update users set username = '$username', email = '$email', password = '$password', image = '$image' where id = '$id' limit 1 ";
    } else {
      $query = "update users set username = '$username', email = '$email', password = '$password' where id = '$id' limit 1 ";
    }

    $result = mysqli_query($con, $query);

    $query = "select * from users where id = '$id' limit 1";
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) > 0) {
      // 更新した処理を$_SESSIONに保存する処理
      $_SESSION['info']  = mysqli_fetch_assoc($result);
      // $_SESSIONに入れることでどのページでも使える値になる
    }
 
    header("Location: profile.php");
    die;
  }
  // 👇投稿を追加する処理
  elseif($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['post']))
  {
    
    $image = "";
    if(!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0 && $_FILES['image']['type'] == 0) {
      // file was uploaded
      $folder = "uploads/";
      if(!file_exists($folder)){
        
        mkdir($folder, 0777, true);
      }
      $image = $folder . $_FILES['image']['name'];
      move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    
    $post = addslashes($_POST['post']);
    $user_id = $_SESSION['info']['id'];
    $date = date('Y-m-d H:i:s');

    $query = "insert into posts (user_id, post, image, date) value ('$user_id', '$post', '$image', '$date')";
    

    $result = mysqli_query($con, $query);
 
    header("Location: profile.php");
    die;
  }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - my website</title>
  <link href="https://use.fontawesome.com/releases/v6.2.0/css/all.css" rel="stylesheet">
  <link href="footer.css" rel="stylesheet" type="text/css">
</head>
<body>
  
  <div style="display:flex">
  <?php require "header.php"; ?>
    <div style="padding: 30px 20px 0;display: flex; align-items: center;margin: auto; max-width: 600px;">

      <!-- 👇プロフィールを編集する処理 -->
      <?php if(!empty($_GET['action']) && $_GET['action'] == 'edit'):?>
        <h2 style="text-align: center;">Edit Profile</h2>
          <form method="post" enctype="multipart/form-data" style="margin: auto; pading: 10px;">

            <img src="<?php echo $_SESSION['info']['image'] ?>" style="width: 100px; height: 100px;object-fit: cover;margin: auto; display: block">
            <input value="<?php echo $_SESSION['info']['image'] ?>" type="file" name="image"><br>
            <input value="<?php echo $_SESSION['info']['username'] ?>" type="text" name="username" placeholder="Username" required><br>
            <input value="<?php echo $_SESSION['info']['email'] ?>" type="text" name="email" placeholder="Email" required><br>
            <input value="<?php echo $_SESSION['info']['password'] ?>" type="text" name="password" placeholder="Password" required><br>

            <button>Save</button>

            <a href="profile.php">
              <button type="button">Cancel</button>
            </a>
          </form>

      <?php elseif(!empty($_GET['action']) && $_GET['action'] == 'delete'):?>
        <h2 style="text-align: center;">Are you sure you want to delete your profile?</h2>
          <div style="margin: auto; max-width: 600px;text-align: center;"> 
            <form method="post" style="margin: auto; pading: 10px;">
              <img src="<?php echo $_SESSION['info']['image'] ?>" style="width: 100px; height: 100px;object-fit: cover;margin: auto; display: block">
              <div><?php echo $_SESSION['info']['username'] ?></div>
              <div><?php echo $_SESSION['info']['email'] ?></div>

              <!-- 👇ここで$_POST['action']にdeleteが追加？される -->
              <input type="hidden" name="action" value="delete">
              <button>Delete</button>

              <a href="profile.php">
                <button type="button">Cancel</button>
              </a>
            </form>
          </div>
      <?php else:?>
      
        <div style="height: 300px;display: flex;align-items: center;margin: auto; width: 900px;text-align: center;">
          <div style="border-radius: 50%;">
            <img src="<?php echo $_SESSION['info']['image'] ?>" style="border-radius: 50%;height: 300px;object-fit: cover;">
          </div>
          <div style="margin-left: 50px;">
            <p style="font-weight: 400; font-size: 25px;"><?php echo $_SESSION['info']['username'] ?></p>
            <a href="profile.php?action=edit" style="padding: 10px 15px;">
              <div class="button">
                <p>勤怠</p>
              </div>
            </a>
          </div>

        </div>
<!-- 
        <div>
          <?= $today = date("Y-m-d H:i:s");?>
        </div> -->

        

        <?php endif;?>
      </div>
  </div>

  <?php require "footer.php"; ?>
  
</body>
</html>