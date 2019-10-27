<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
require_once('dbFunctions.php');

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);

function ProcessRequest($request)
{
    try{
        
        if(!isset($request['type'])){
            return array('message'=>"ERROR: Message type is not supported");
        }
        switch($request['type']){
                
            // Add book request    
            case "AddBook":
                $response_msg = addBook($request['bookTitle'],$request['bookIsbn'], $request['listingPrice'], $request['authorName'], $request['username'], $request['bookDescription'], $request['rateTypeId']);
                break;
            //New User registration
            case "Register":
                $response_msg = register($request['username'], $request['password']);
                break;
            case "ForgotPassword":
                $response_msg = forgotPassword($request['username']);
                break;
            case "ResetPassword":
                $response_msg = resetPassword($request['username'], $request['token'], $request['password']);
                break;
            //Login & Authentication request    
            case "Login":
                $response_msg = doLogin($request['username'],$request['password']);
                break;
            //Login & Authentication request    
            case "AddBookReview":
                $response_msg = addBookReview($request['username'],$request['bookId'], $request['reviewText'], $request['reviewRating']);
                break;
            case "AddBookFlag":
                $response_msg = addBookFlag($request['username'],$request['bookId'], $request['flagText']);
                break;
            case "GetBooks":
                $response_msg = getBooks($request['pageNumber'],$request['recordsPerPage']);
                break;
            case "GetUserDetails":
                $response_msg = getUserDetails($request['username']);
                break;
            case "GetBookReviews":
                $response_msg = getBookReviews($request['bookId']);
                break;
            case "GetBookFlags":
                $response_msg = getBookFlags($request['bookId']);
                break;
            case "GetBookDetail":
                $response_msg = getBookDetail($request['bookId']);
                break;
            case "UpdateUserDetails":
                $response_msg = updateUserDetails($request['username'], $request['firstName'], $request['lastName'], $request['email'], $request['password']);
                break;
            case "TestRequest":
                $response_msg = true;
                break;
        }
    }
    catch(Throwable $t){
        echo 'Message: ' . $t->getMessage();
        $response_msg = false;
    }
    catch(Exception $e){
        echo 'Message: ' . $e->getMessage();
        $response_msg = false;
    }
    return json_encode($response_msg);
}

echo " [x] Awaiting RPC requests\n";
$callback = function ($req) {
    $n = $req->body;
    echo ' [.] ProcessRequest(', $n, ")\n";

    $msg = new AMQPMessage(
        ProcessRequest(json_decode($req->body, true)),
        array('correlation_id' => $req->get('correlation_id'))
    );

    $req->delivery_info['channel']->basic_publish(
        $msg,
        '',
        $req->get('reply_to')
    );
    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']
    );
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    try{
    $channel->wait();
    }
    catch(Exception $e){
        echo 'Message: ' . $e->getMessage();
        $channel->close();
        $connection->close();
        header("location: bookListener.php");
    }
}

$channel->close();
$connection->close();