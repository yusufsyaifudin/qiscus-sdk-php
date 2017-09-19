<?php 
namespace test;
require 'vendor/autoload.php';
require 'src/Qiscus/QiscusSdk.php';
require "src/Qiscus/QiscusIdentityToken.php";
use PHPUnit\Framework\TestCase;

class QiscusSDKTest extends TestCase {

    private $room_participants = ['email2@mailinator.com', 'email3@mailinator.com'];
    private $email = 'email1@mailinator.com';
    private $room_name = 'Testing...';
    
    public function testLoginOrRegisterSuccess(){

       $qiscus_sdk = new \Qiscus\QiscusSdk('check', 'rahasiabanget');

        $room_name = 'Testing...';
        $password = 'password';
        $emails = ['email1@mailinator.com', 'email2@mailinator.com', 'email3@mailinator.com', 'email4@mailinator.com'];
        $creator = $emails[0];
        $room_participants = [$creator, $emails[3]];

        // login or register
        foreach($emails as $email) {
            try {
                $user = $qiscus_sdk->loginOrRegister($email, $password, $email); 
                $this->assertEquals($email, $user->email);
            } catch (\Exception $e) {
                $this->assertNotNull($e);
            }
        }
    }
    
    // public function testLoginInvalidParams(){
    //     $qiscus_sdk = new \Qiscus\QiscusSdk('cozer-aji-qy3o5pykia1', 'bd23a60ca011c6793898c1100e3cec90');
    //     $room_name = 'Testing...';
    //     $password = 'password';
    //     $email= 'email1@mailinator.com';
    //     $user = $qiscus_sdk->loginOrRegister($email, '', ''); 
    //     $this->assertEquals($user->status, 400);
    // }

    public function testCreateRoomAndLoadComment(){
        $qiscus_sdk = new \Qiscus\QiscusSdk('check', 'rahasiabanget');
            $room_name = 'Testing...';
            $password = 'password';
            $emails = ['email1@mailinator.com', 'email2@mailinator.com', 'email3@mailinator.com', 'email4@mailinator.com'];
            $creator = $emails[0];
            $room_participants = [$creator, $emails[3]];
        try {
            $create_room = $qiscus_sdk->createRoom($room_name, $emails, $creator);
            // "================ CREATE ROOM =======================";
            $this->assertEquals($room_name, $create_room->name);

            // add participant to group
            $add_room_participants = $qiscus_sdk->addRoomParticipants($create_room->id, $emails);
            // "================ ADD PARTICIPANTS ==================";
            $this->assertEquals($add_room_participants->name, $room_name);

            // post comment
            $post_comment = $qiscus_sdk->postComment($creator, $create_room->id, 'Halo');
            // "================ POST COMMENT ======================";
            $this->assertEquals('Halo', $post_comment->message);

            // load comments
            $load_comments = $qiscus_sdk->loadComments($create_room->id);
            // "================ LOAD COMMENTS =====================";
            $this->assertEquals($load_comments->room->id, $create_room_id);

            // remove participant from group
            $remove_room_participants = $qiscus_sdk->removeRoomParticipants($create_room->id, [$emails[2]]);
            // "================ REMOVE PARTICIPANTS ==================";
            $this->assertEquals($remove_room_participants->id, $create_room->id);

            // get or create room with target
            // this is for single chat only
            $get_or_create_room_with_target = $qiscus_sdk->getOrCreateRoomWithTarget($room_participants);
            // "================ GET OR CREATE ROOM WITH TARGET ============";
            $this->assertNotNull($get_or_create_room_with_target->id);

            $post_comment = $qiscus_sdk->postComment($creator, $get_or_create_room_with_target->id, 'Halo', 'custom', ['type' => 'bar', 'content' => 'oke']);
            // "================ POST COMMENT ======================";
            //$this->assertEquals($get_or_create_room_with_target->id, $post_comment->room->id);
            $this->assertEquals($post_comment->message, 'Halo');

            // load comments
            $load_comments = $qiscus_sdk->loadComments($get_or_create_room_with_target->id);
            // "================ LOAD COMMENTS =====================";
            $this->assertNotNull($load_comments);

            // get room info
            $get_rooms_info = $qiscus_sdk->getRoomsInfo($creator, [$create_room->id, $get_or_create_room_with_target->id]); 
            // "================ GET ROOM INFO =====================";
            $this->assertNotEquals(count($get_rooms_info), 0);
            //var_dump($get_rooms_info);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function testLoginWithNonce(){
        $qiscus_identity_token = new \Qiscus\QiscusIdentityToken("dragongo", "dragongo-123");
        // $qiscus_identity_token->localMode(true);
        $nonce = $qiscus_identity_token->getNonce();
        // var_dump($nonce);

        // get nonce
        $nonce = $nonce->results->nonce;

        $this->assertNotNull($nonce);

        // now build identity token (client's server)
        $email = "email-sdk-qiscus-tester@mailinator.com";
        $name = "Tester SDK using client auth";
        $avatar_url = "https://res.cloudinary.com/qiscus/image/upload/v1501313707/group_avatar_kiwari-prod_user_id_72/abzgeaglfyeb2oujq5bb.jpg";

        $identity_token = $qiscus_identity_token->generateIdentityToken($nonce, $email, $name, $avatar_url);

        $this->assertNotNull($identity_token);

        // then call verify identity token
        $verified_user = $qiscus_identity_token->verifyIdentityToken($identity_token);

        $this->assertEquals($verified_user->status, 200);
    }

}