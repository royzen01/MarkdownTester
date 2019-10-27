<?php 

    //Requried files
    require_once('../rabbitmqphp_example/path.inc');
    require_once('../rabbitmqphp_example/get_host_info.inc');
    require_once('../rabbitmqphp_example/rabbitMQLib.inc');
    require_once('dbConnection.php');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    // Load Composer's autoloader
    require 'vendor/autoload.php';
    //Error logging
    error_reporting(E_ALL);

    ini_set('display_errors', 'off');
    ini_set('log_errors', 'On');
    ini_set('error_log', dirname(__FILE__).'/../logging/log.txt');
    
//
    //logAndSendErrors();

    //Error loggon
    
    //Function for loggin user in the system and authentication
    function doLogin($username, $password){
        
        $connection = dbConnection();
        
        $query = "SELECT * FROM user WHERE username = '$username'";
        $result = $connection->query($query);
        if($result){
            if($result->num_rows == 0){
                return false;
            }else{
                while ($row = $result->fetch_assoc()){
                    $salt = $row['salt']; 
                    $h_password = hashPassword($password, $salt);
                    if ($row['h_password'] == $h_password){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }
    }

    // This function checks is username is already taken
    function checkUsername($username){
        
        $connection = dbConnection();
        
        //Query to check if the username is taken
        $check_username = "SELECT * FROM user WHERE username = '$username'";
        $check_result = $connection->query($check_username);
        
        if($check_result){
            if($check_result->num_rows == 0){
                return true;
            }elseif($check_result->num_rows == 1){
                return false;
                }
        }
    }

    

    // This function sends user email with username and password
    function sendEmail($username){
        $mail = new PHPMailer(true);
        try{

            $subject = "Change password";
            // $headers = "MIME-Version: 1.0" . "\r\n";
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // $headers .= "From: <nb97@njit.edu>"; //will change later
            
            $email = get_credentials($username);

            if(!isset($email))
            {
                return false;
            }
            
            $uniquekey = generateUniqueKey(4);
            
            $storekey = storeUniqueKey($username, $uniquekey);
            
            $message = "<b>Username:</b> " . "$username" . "<br>" . "<b>Unique Key:</b> " . "$uniquekey" . "<br><br>" . "Please reset the password in 24 hours. Click on the link below and reset your password by providing your Username and Unique Key.<br><br>" . "<a href='http://localhost:8080/books/frontend/html/resetpassword.html'>link</a>";

            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();   
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'it490donotreply@gmail.com';                     // SMTP username
            $mail->Password   = 'hD6(4),qs.gj';                               // SMTP password
            $mail->SMTPSecure = 'ssl';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 465;     

            $mail->setFrom('it490donotreply@gmail.com', 'Mailer');
            $mail->addAddress($email, 'Recipient');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;            

            $mail->send();
            
            // mail($email, $subject, $message, $headers);
            
            return true;
        } catch (Throwable $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        return false;
    }

    function forgotPassword($username){
        return sendEmail($username);
    }

    function resetPassword($username, $token, $password){
        $username = getUserKey($username, $token);

        if(!isset($username)){
            return false;
        }

        //Generates a salt for the new user
        $salt = randomString(29);
        
        //Hashes password
        $h_password = hashPassword($password, $salt);
        
        updatePassword($salt, $h_password, $username);      
    }

    function getUserKey($username, $token){
        $connection = dbConnection();
        
        //Query for fetching credentials
        $credentials_query = "SELECT username_id FROM userkey WHERE username_id = '$username' and uniquekey = '$token'";
        $credentials_query_result = $connection->query($credentials_query);
        
        $row = $credentials_query_result->fetch_assoc();
        $username = $row['username_id'];
        return $username;
    }

    function updatePassword($salt, $h_password, $username){
        $connection = dbConnection();

        $delete_query = "Delete from userkey where username_id='$username'";
        $connection->query($delete_query);

        //Query for a new user
        $updatePasswordQuery = "Update user set h_password = '$h_password',salt = '$salt' where username='$username'";

        $connection->query($updatePasswordQuery);
    }

    // This functions returns credentials of user with email provided
    function get_credentials($username){
        
        $connection = dbConnection();
        
        //Query for fetching credentials
        $credentials_query = "SELECT email FROM user WHERE username = '$username'";
        $credentials_query_result = $connection->query($credentials_query);
        
        $row = $credentials_query_result->fetch_assoc();
        $email = $row['email'];
        return $email;
    }

    // This function generates unique for reset password
    function generateUniqueKey($length){
        
        $randstr = '';
            srand((double) microtime(TRUE) * 1000000);
           
            $chars = array(
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
                'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
                '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 
                'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            for ($rand = 0; $rand <= $length; $rand++) {
                $random = rand(0, count($chars) - 1);
                $randstr .= $chars[$random];
            }
            return $randstr;
    }

    // This function stores Unique generated for user
    function storeUniqueKey($username, $uniquekey){
        
        $connection = dbConnection();
        
        $delete_query = "Delete from userkey where username_id='$username'";
        $connection->query($delete_query);

        //Query to store unique key for username
        $storekey_query = "INSERT INTO userkey(username_id,uniquekey) VALUES ('$username', '$uniquekey')";
        $storekey_query_result = $connection->query($storekey_query);
        
        return $storekey_query_result;
    }
    
    //Generating random Alpha-Numeric string for unique salt for every new registration
    function randomString($length) {
            $randstr = '';
            srand((double) microtime(TRUE) * 1000000);
           
            $chars = array(
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
                'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
                '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 
                'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            for ($rand = 0; $rand <= $length; $rand++) {
                $random = rand(0, count($chars) - 1);
                $randstr .= $chars[$random];
            }
            return $randstr;
    }
    
    //Hashes password for storing
    function hashPassword($password, $salt){
            $new_pass = $password . $salt;
             return hash("sha256", $new_pass);
        }
    
    //  This function registers a new user 
    function register($username, $password){
        
        //Makes connection to database
        $connection = dbConnection();
        
        //Generates a salt for the new user
        $salt = randomString(29);
        
        //Hashes password
        $h_password = hashPassword($password, $salt);
        
        //Query for a new user
        $newuser_query = "INSERT INTO user(username,h_password,salt) VALUES ('$username', '$h_password', '$salt')";
        
        $result = $connection->query($newuser_query);
        
        return true;
    }

    function addBook($bookTitle, $bookIsbn, $listingPrice, $authorName, $username, $bookDescription, $rateTypeId){
        
        $connection = dbConnection();
        
        $addsuggestion_query = "INSERT INTO book(title,isbn,listing_price,author_name,username_id,description,rate_type_id) VALUES ('$bookTitle', '$bookIsbn', '$listingPrice', '$authorName', '$username', '$bookDescription', '$rateTypeId')";
        $result = $connection->query($addsuggestion_query);
        if(!$result){
            echo("Error description: " . mysqli_error($connection));
        }
        
        return true;
    }

    function addBookReview($username, $bookId, $reviewText, $reviewRating){
        
        $connection = dbConnection();
        
        $addsuggestion_query = "INSERT INTO review(username_id,book_id,review_text,review_rating) VALUES ('$username', '$bookId', '$reviewText', '$reviewRating')";
        $result = $connection->query($addsuggestion_query);
        if(!$result){
            echo("Error description: " . mysqli_error($connection));
        }
        
        return true;
    }

    function addBookFlag($username, $bookId, $flagText){
        
        $connection = dbConnection();
        
        $addsuggestion_query = "INSERT INTO flag(username_id,book_id,flag_text) VALUES ('$username', '$bookId', '$flagText')";
        $result = $connection->query($addsuggestion_query);
        if(!$result){
            echo("Error description: " . mysqli_error($connection));
        }
        
        return true;
    }

    function getBooks($pageNumber, $recordsPerPage){
        $connection = dbConnection();

        //$offset = ($pageNumber - 1) * $recordsPerPage;

        //$book_query = "SELECT *, count(*) OVER() AS total_books_count FROM book LIMIT " . $offset . "," . $recordsPerPage;
        $book_query = "SELECT * FROM book ORDER BY last_modified_datetime DESC";
        $book_result = $connection->query($book_query);
        $books = array();
        while ($row = $book_result->fetch_assoc()){
            $id = $row['book_id'];
            $title = $row['title'];
            $isbn = $row['isbn'];
            $listingPrice = $row['listing_price'];
            $authorName = $row['author_name'];
            $username = $row['username_id'];
            $description = $row['description'];
            $createdDateTime = $row['created_datetime'];
            $lastModifiedDateTime = $row['last_modified_datetime'];

            array_push($books, array("id"=>$id , "title"=>$title , "isbn"=>$isbn , "listingPrice"=>$listingPrice ,"authorName"=>$authorName , "username"=>$username , "description"=>$description, "createdDateTime"=>$createdDateTime, "lastModifiedDateTime"=>$lastModifiedDateTime));
        }
        $all_info = array('totalBooksCount'=>count($books), 'books'=>$books);
        return $all_info;
    }

    function getBookDetail($bookId){
        $connection = dbConnection();

        //$offset = ($pageNumber - 1) * $recordsPerPage;

        //$book_query = "SELECT *, count(*) OVER() AS total_books_count FROM book LIMIT " . $offset . "," . $recordsPerPage;
        $book_query = "SELECT * FROM book inner join rate_type on book.rate_type_id=rate_type.rate_type_id where book_id='$bookId'";
        $book_result = $connection->query($book_query);
        $books = [];
        while ($row = $book_result->fetch_assoc()){
            $id = $row['book_id'];
            $title = $row['title'];
            $isbn = $row['isbn'];
            $listingPrice = $row['listing_price'];
            $authorName = $row['author_name'];
            $username = $row['username_id'];
            $description = $row['description'];
            $createdDateTime = $row['created_datetime'];
            $lastModifiedDateTime = $row['last_modified_datetime'];
            $rate_type_description = $row['rate_description'];

            $books[] = array("id"=>$id , "title"=>$title , "isbn"=>$isbn , "listingPrice"=>$listingPrice ,"authorName"=>$authorName , "username"=>$username , "description"=>$description, "createdDateTime"=>$createdDateTime, "lastModifiedDateTime"=>$lastModifiedDateTime, "rateDescription"=>$rate_type_description);
        }
        return $books;
    }

    function getUserDetails($username){
        $connection = dbConnection();

        $user_query = "SELECT * FROM user where username='$username'";
        $user_result = $connection->query($user_query);
        $user = [];
        while ($row = $user_result->fetch_assoc()){
            $username = $row['username'];
            $email = $row['email'];
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $createdDateTime = $row['created_datetime'];
            $lastModifiedDateTime = $row['last_modified_datetime'];

            $user[] = array("username"=>$username , "email"=>$email , "firstname"=>$firstname , "lastname"=>$lastname ,"createdDateTime"=>$createdDateTime , "lastModifiedDateTime"=>$lastModifiedDateTime);
        }
        return $user;
    }

    function getBookReviews($bookId){
        $connection = dbConnection();

        $review_query = "SELECT * FROM review where book_id='$bookId' ORDER BY last_modified_datetime DESC";
        $review_result = $connection->query($review_query);
        $reviews = array();
        while ($row = $review_result->fetch_assoc()){
            $reviewId = $row['review_id'];
            $username = $row['username_id'];
            $bookId = $row['book_id'];
            $reviewText = $row['review_text'];
            $reviewRating = $row['review_rating'];
            $createdDateTime = $row['created_datetime'];
            $lastModifiedDateTime = $row['last_modified_datetime'];

            array_push($reviews, array("reviewId"=>$reviewId , "username"=>$username , "bookId"=>$bookId , "reviewText"=>$reviewText, "reviewRating"=>$reviewRating,"createdDateTime"=>$createdDateTime , "lastModifiedDateTime"=>$lastModifiedDateTime));
        }
        return $reviews;
    }

    function getBookFlags($bookId){
        $connection = dbConnection();

        $review_query = "SELECT * FROM flag where book_id='$bookId' ORDER BY last_modified_datetime DESC";
        $review_result = $connection->query($review_query);
        $reviews = array();
        while ($row = $review_result->fetch_assoc()){
            $flagId = $row['flag_id'];
            $username = $row['username_id'];
            $bookId = $row['book_id'];
            $flagText = $row['flag_text'];
            $createdDateTime = $row['created_datetime'];
            $lastModifiedDateTime = $row['last_modified_datetime'];

            array_push($reviews, array("flagId"=>$flagId , "username"=>$username , "bookId"=>$bookId , "flagText"=>$flagText,"createdDateTime"=>$createdDateTime , "lastModifiedDateTime"=>$lastModifiedDateTime));
        }
        return $reviews;
    }

    function updateUserDetails($username, $firstName, $lastName, $email, $password){
        
        //Makes connection to database
        $connection = dbConnection();
        
        //Generates a salt for the new user
        $salt = randomString(29);
        
        //Hashes password
        $h_password = hashPassword($password, $salt);
        
        //Query for a new user
        $newuser_query = "UPDATE user set email='$email', h_password = '$h_password', salt = '$salt', firstname='$firstName', lastname='$lastName' WHERE username='$username'";
        
        $result = $connection->query($newuser_query);
        
        return true;
    }

?>