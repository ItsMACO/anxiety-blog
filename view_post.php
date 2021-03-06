<?php
session_start();
require_once 'db.php';
require 'sidebar_new.php';
include 'seen.php';

if (isset($_SESSION['id'])) {
    $user = $_SESSION['id'];
} 

$pid = $_GET['pid'];

$sql = "SELECT * FROM posts WHERE id=$pid LIMIT 1";
$result = mysqli_query($con, $sql) or die(mysqli_error($con));

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pagetitle = $row['title'];
        $image = $row['image'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pagetitle; ?></title>
</head>
<body>
<div class="container-fluid">
<div class="wrap">
<div class="wrap-content">
<div class='center-align' style="background: url('<?php echo $image; ?>'); height: 400px;"><h3 class='break-long-words screen'><?php echo $pagetitle; ?></h3></div>
<br><br>
<?php
require_once 'nbbc.php';
$bbcode = new BBCode;

$pid = $_GET['pid'];

$sql = "SELECT * FROM posts WHERE id=$pid LIMIT 1";
$result = mysqli_query($con, $sql) or die(mysqli_error($con));

$post = "";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = $row['title'];
        $date = $row['date'];
        $content = $row['content'];
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
        if(isset($_SESSION['username']) && $_SESSION['username'] == $author) {
        $edit_delete = "<div>
            <a href='#stats' class='button button1 modal-trigger'>STATS</a>
            <a href='edit_post.php?pid=$pid' class='button button2'>EDIT</a>&nbsp;
            <a href='del_post.php?pid=$pid' class='button button3'>DELETE</a></div>";
        } else {
            $edit_delete = "<div>";
        }

        $post .= "<h6 class='flair'>$flair</h6>
            <p>$date by <a href='profile.php?id=$userid'>$author</a></p><br>
            <h6 class='break-long-words'>$output</h6><br><br>
            </div><br>";

        echo $post."&nbsp;".$edit_delete;

        
        echo "<div><form action='view_post.php?pid=$pid' method='post'>";
        if(isset($_SESSION['id'])) {
        $user = $_SESSION['id'];
        $result_like = mysqli_query($con, "SELECT * FROM likes WHERE (user_from='$user') AND (postid='$pid')");
        if (mysqli_num_rows($result_like) > 0) {
            echo "<button type='submit' name='like' class='button button1-reverse'>LIKED</button>&nbsp";
        } else {
            echo "<button type='submit' name='like' class='button button1'>LIKE</button>&nbsp";
        }
    } else {
        echo "<button type='submit' name='like' class='button button1'>LIKE</button>&nbsp";
    }
        echo "<a href='#report-modal' name='report' class='button button3 modal-trigger'>REPORT</a></form></div><br><br>";
        
        // LETS USER LIKE A POST

        if (isset($_POST['like'])) {
            if (isset($_SESSION['id'])) {
                $user = $_SESSION['id'];
                $time = time();
                $sql_like = "INSERT INTO likes (user_from, user_to, postid, time) VALUES ('$user', '$userid', '$id', '$time')";
                $sql_like_posts = "UPDATE posts SET likes = likes + 1 WHERE id=$pid";
                $result_like = mysqli_query($con, "SELECT * FROM likes WHERE (user_from='$user') AND (postid='$pid')");
                if (mysqli_num_rows($result_like) > 0) {
                    echo "<div class='left-align'><h5>You've already liked this post.</h5></div>"; 
                } else {
                    mysqli_query($con, $sql_like);
                    mysqli_query($con, $sql_like_posts);
                    echo "<div class='left-align'><h5>Liked!</h5></div>";
                }
            } else {
                echo "<h5>You have to log in to like posts.</h5><br>";
            }
        }

        // LETS USER COMMENT ON POST

        if (isset($_POST['comment-submit'])) {
            if(isset($_SESSION['id'])) {
                $user = $_SESSION['id'];
                $user_name = $_SESSION['username'];
                $time = time();
                $comment_content = $_POST['comment-content'];
                $sql_comment = "INSERT INTO comments (comment_from, comment_to, postid, time, comment_content) VALUES ('$user_name', '$userid',  '$id', '$time', '$comment_content')";
                mysqli_query($con, $sql_comment);
                echo "<div class='left-align'><h5>Comment submitted!</h5></div>";
            } else {
                echo "You have to log in to comment on posts.";
            }
        }
        echo "<form action='view_post?pid=$pid' method='post' enctype='multipart/form-data'>";
        echo "<textarea name='comment-content' class='text-input' placeholder='Comment'></textarea><br><br>";
        echo "<button type='submit' name='comment-submit' class='button button1'>SEND</button><br><br>";
        echo "</form>";
    }
} else {
    echo "<p>There is no post to display!</p>";
}


// DISPLAYS ALL COMMENTS
$sql_comments = "SELECT * FROM comments WHERE postid='$pid' ORDER BY time DESC";
$result_comments = mysqli_query($con, $sql_comments) or die(mysqli_error($con));

$comments = "";

if (mysqli_num_rows($result_comments) > 0) {

    $result_comments_number = mysqli_num_rows($result_comments);
    echo "<h5>".$result_comments_number." comments</h5><div class='divider'></div><br>";

    while ($row = mysqli_fetch_assoc($result_comments)) {
        $comment_id = $row['comment_id'];
        $comment_user_username = $row['comment_from'];
        $comment_postid = $row['postid'];
        $comment_time = $row['time'];
        $comment_content = $row['comment_content'];

        $sql_comment_profile = "SELECT * FROM users WHERE username='$comment_user_username'";
        $result_comment_profile = mysqli_query($con, $sql_comment_profile) or die(mysqli_error($con));
        if (mysqli_num_rows($result_comment_profile) > 0) {
            while ($row = mysqli_fetch_assoc($result_comment_profile)) {
                $userid = $row['id'];
                $username = $row['username'];
                $email = $row['email'];
            }
        }
                //converts unix time to normal datetime
                $unix_converted = date('d-m-Y H:i:s', $comment_time);
                //breaks the comment after 90 characters
                $comments = "<div class='box box1' id='$comment_id'>
                <h6 style='margin: 25px;' class='break-long-words'>
                <a href='profile.php?id=$userid'>$comment_user_username</a>
                <br>$unix_converted UTC<br>$comment_content</h6></div><br><br>";
                echo $comments;
    }
} else {
    echo "<div class='left-align'>There are no comments to display!</div><br>";
}
?>
</div>
</div>
</div>

<?php
if(isset($_POST['report-submit'])) {
    if(isset($_POST['reason'])) {
        $reason = $_POST['reason'];
        $time = time();

        $sql = "SELECT * FROM posts WHERE id=$pid LIMIT 1";
        $result = mysqli_query($con, $sql) or die(mysqli_error($con));
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $author = $row['author'];
                $sql_profile = "SELECT * FROM users WHERE username='$author'";
                $result_profile = mysqli_query($con, $sql_profile) or die(mysqli_error($con));
                if (mysqli_num_rows($result_profile) > 0) {
                    while ($row = mysqli_fetch_assoc($result_profile)) {
                        $userid = $row['id'];        
                        if(isset($_SESSION['id'])) {
                            $sql_report = "INSERT INTO post_reports (postid, user_from, user_to, reason, time) VALUES ('$pid', '$user', '$userid', '$reason', '$time')";
                        } else {
                            $sql_report = "INSERT INTO post_reports (postid, user_from, user_to, reason, time) VALUES ('$pid', '0', '$userid', '$reason', '$time')";
                        }
                        mysqli_query($con, $sql_report) or die(mysqli_error($con));
                    }
                }
            }
        }
    }
}
?>

<div id="report-modal" class="modal">
    <div class="modal-content">
      <h4>Report Post</h4>
        <form action="view_post.php?pid=<?php echo $pid; ?>" method="post" enctype="multipart/form-data">
        <p>I would like to report this post because...</p>
        <input type='text' name='reason' class='text-input'>
        <button type='submit' name='report-submit' class='button button3'>REPORT</button>
    </form> 
    </div>
  </div>
  <div id="stats" class="modal">
    <div class="modal-content">
      <h4>Statistics</h4>
        <iframe src="statistics.php?pid=<?php echo $pid; ?>" height="400px" width="400px"></iframe>
    </form> 
    </div>
  </div>
  <script>
var elem = document.querySelector('#report-modal');
var instance = M.Modal.init(elem, {
  accordion: false
});</script>
<script>
var elem = document.querySelector('#stats');
var instance = M.Modal.init(elem, {
  accordion: false
});</script>
</body>

</html>