<?php
require_once 'db.php';
include 'online_log.php';
include 'sidebar_new.php';
if (!isset($_SESSION)) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Blog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="materialize/js/materialize.js"></script>
    <script src="main.js"></script>
    <link rel="stylesheet" href="styles.css?<?php echo time(); ?>">
</head>
<body>
<div class="container-fluid">
<div class="wrap">
<div class="wrap-content">
<br><br>
<?php
require_once 'nbbc.php';
$bbcode = new BBCode;

if(!isset($_GET['order']) || $_GET['order'] == "new") {
    $sql = "SELECT * FROM posts ORDER BY id DESC";
    echo "<a href='?order=top' class='button-small button2'>ORDER BY TOP POSTS</a>";
} else {
    $sql = "SELECT * FROM posts ORDER BY likes DESC";
    echo "<a href='?order=new' class='button-small button2'>ORDER BY NEW POSTS</a>";
}
$result = mysqli_query($con, $sql) or die(mysqli_error($con));

$posts = "";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = $row['title'];
        $content = $row['content'];
        $date = $row['date'];
        $author = $row['author'];
        $image = $row['image'];
        $flair = $row['flair'];

        $sql_profile = "SELECT * FROM users WHERE username='$author'";
        $result_profile = mysqli_query($con, $sql_profile) or die(mysqli_error($con));
        if (mysqli_num_rows($result_profile) > 0) {
            while ($row = mysqli_fetch_assoc($result_profile)) {
                $userid = $row['id'];
                $username = $row['username'];
                $email = $row['email'];
            }
        }

        $output = $bbcode->Parse($content);

        $posts .= "<div class='row'>
            <div class='col s8'>
            <h2 class='break-long-words'><a href='view_post.php?pid=$id'>$title</a></h2><h6 class='flair'>$flair</h6>
            <p>$date by <a href='profile.php?id=$userid'>$author</a></p>
            <h6>" . substr($output, 0, 140) . "...</h6><br><br>
            <a href='view_post.php?pid=$id' class='button button1'>READ MORE</a>
            <a href='?read_later=$id' class='button button2'>READ LATER</a>
            <br>
            </div>
            <div class='col s4'><br><br><img src='$image' height='200' width='200' class='right-align'></div><br>
            </div><br>";
    }
    echo $posts;
    if(isset($_GET['read_later'])) {
        if(isset($_SESSION['id'])) {
            $read_postid = $_GET['read_later'];
            $read_later_exists = mysqli_query($con, "SELECT * FROM read_later WHERE (read_user='$user') AND (read_postid='$read_postid')");
            if(mysqli_num_rows($read_later_exists) > 0) {
                echo "You've already saved this post.";
            } else {
                $sql_read_later = "INSERT INTO read_later (read_user, read_postid) VALUES ('$user', '$read_postid')";
                mysqli_query($con, $sql_read_later);
            }
        } else {
            header('Location: login.php');
        }
    }

} else {
    echo "There are no posts to display!";
}
?>
<!--DIV CONTAINER FLUID -->
</div>
<!--DIV WRAP -->
</div>
</div>
</body>
</html>