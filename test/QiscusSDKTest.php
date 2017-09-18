<?php 
namespace test;
require 'vendor/autoload.php';
require 'src/Qiscus/QiscusSdk.php';
use PHPUnit\Framework\TestCase;

class QiscusSDKTest extends TestCase {

    private $room_participants = ['email2@mailinator.com', 'email3@mailinator.com'];
    private $email = 'email1@mailinator.com';
    private $room_name = 'Testing...';
    
    public function testLoginOrRegisterSuccess(){
        $qiscus_sdk = new \Qiscus\QiscusSdk('cozer-aji-qy3o5pykia1', 'bd23a60ca011c6793898c1100e3cec90');
        $room_name = 'Testing...';
        $password = 'password';
        $emails = ['email1@mailinator.com', 'email2@mailinator.com', 'email3@mailinator.com', 'email4@mailinator.com'];
        $creator = $emails[0];
        
        // login or register
        foreach($this->room_participants as $email) {
            $user = $qiscus_sdk->loginOrRegister($email, $password, $email); 
            $this->assertEquals($user->status, 200);
            $this->assertEquals($user->results->user->email, $email);
        }
    }
    
    public function testLoginInvalidParams(){
        $qiscus_sdk = new \Qiscus\QiscusSdk('cozer-aji-qy3o5pykia1', 'bd23a60ca011c6793898c1100e3cec90');
        $room_name = 'Testing...';
        $password = 'password';
        $email= 'email1@mailinator.com';
        $user = $qiscus_sdk->loginOrRegister($email, '', ''); 
        $this->assertEquals($user->status, 400);
    }

    public function testCreateRoomAndLoadComment(){
        $qiscus_sdk = new \Qiscus\QiscusSdk('cozer-aji-qy3o5pykia1', 'bd23a60ca011c6793898c1100e3cec90');
        $room_name = 'Testing...';
        $password = 'password';
        $email = 'email1@mailinator.com';
        $room_participants = ['email2@mailinator.com', 'email3@mailinator.com',];
        $room = $qiscus_sdk->createRoom($room_name, $room_participants, $email); 
        $this->assertEquals($room->results->creator, $email);
        $this->assertEquals(sizeof($room->results->participants), 2);
        // "================ LOAD COMMENTS =====================";
        $load_comments = $qiscus_sdk->loadComments($room->results->room_id);
        $this->assertEquals($load_comments->status, 200);
        // "================ GET ROOM INFO =====================";
        // $get_rooms_info = $qiscus_sdk->getRoomsInfo($email, [$room->results->room_id]);
        var_dump($room->results->room_id);
        // $this->assertEquals($get_rooms_info->result, 200);
    }


}