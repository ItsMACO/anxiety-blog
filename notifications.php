<?php
include 'online_log.php';
include 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="styles.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="main.js"></script>
</head>
<body style="background: white !important;">
<div class="container-fluid">
<div class="center-align">
    <?php
    $user = $_SESSION['id'];
    $sql_notify = "SELECT notifytime FROM users WHERE id='$user'";
    $result_notify = mysqli_query($con, $sql_notify);
    $row = mysqli_fetch_assoc($result_notify);
    $notifytime = $row['notifytime'];

    $sql_comments = "SELECT * FROM comments WHERE (comment_to='$user') AND (time>$notifytime) AND NOT (comment_from='$user')";
    $result_comments = mysqli_query($con, $sql_comments);

    if(mysqli_num_rows($result_comments) > 0) {
        while($row = mysqli_fetch_assoc($result_comments)) {
            $comment_id = $row['comment_id'];
            $comment_from = $row['comment_from'];
            $comment_to = $row['comment_to'];
            $postid = $row['postid'];
            $time = $row['time'];
            $comment_content = $row['comment_content'];

            $sql_comment_post = "SELECT * FROM posts WHERE id='$postid'";
            $result_comment_post = mysqli_query($con, $sql_comment_post);
            if(mysqli_num_rows($result_comment_post) > 0) {
                while($row = mysqli_fetch_assoc($result_comment_post)) {
                    $comment_post_id = $row['id'];
                    $comment_post_name = $row['title'];

                    $notify_comment = "<div><h5>New comment: <br></h5><div class='box box1'><h5 style='margin: 25px;' class='break-long-words'>$comment_from</h5>
                    <h6 style='margin: 25px;' class='break-long-words'>$comment_content</h6>
                    <div class='center-align'><a href='view_post.php?pid=$comment_post_id#$comment_id' class='button-small button1'>GO THERE</a></div>
                    </div></div><br><div class='divider'></div>";

                    echo $notify_comment;
                }
            }
        }
    } else {
        echo "<br>You have no new notifications!<br><br>";
    }
    ?>
</div>
</div>




<?php
include 'notifytime.php';
?>
</body>
</html>