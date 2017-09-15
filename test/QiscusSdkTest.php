<?php

require 'vendor/autoload.php';
require 'src/Qiscus/QiscusSdk.php';

$qiscus_sdk = new \Qiscus\QiscusSdk('dragongo', 'dragongo-123');

$room_name = 'Testing...';
$password = 'password';
$emails = ['email1@mailinator.com', 'email2@mailinator.com', 'email3@mailinator.com', 'email4@mailinator.com'];
$creator = $emails[0];
$room_participants = [$emails[2], $emails[3]];

// login or register
foreach($emails as $email) {
  $user = $qiscus_sdk->loginOrRegister($email, $password, $email); 
  var_dump($user);
}

// // create a new room
// $create_room = $qiscus_sdk->createRoom($room_name, $emails, $creator);
// echo "================ CREATE ROOM =======================";
// var_dump($create_room);

// // load comments
// $load_comments = $qiscus_sdk->loadComments($create_room->results->room_id);
// echo "================ LOAD COMMENTS =====================";
// var_dump($load_comments);

// // get room info
// $get_rooms_info = $qiscus_sdk->getRoomsInfo($emails[0], [$create_room->results->room_id]);
// echo "================ GET ROOM INFO =====================";
// var_dump($get_rooms_info);

// // add participant
// $add_room_participants = $qiscus_sdk->addRoomParticipants($create_room->results->room_id, $room_participants);
// echo "================ ADD PARTICIPANTS ==================";
// var_dump($add_room_participants);

// // remove participant
// $remove_room_participants = $qiscus_sdk->removeRoomParticipants($create_room->results->room_id, $room_participants);
// echo "================ REMOVE PARTICIPANTS ==================";
// var_dump($remove_room_participants);

// // post comment
// // this is for single chat only
// $post_comment = $qiscus_sdk->postComment($creator, $create_room->results->room_id, 'Halo');
// echo "================ POST COMMENT ======================";
// var_dump($post_comment);

// // get or create room with target
// // this is for single chat only
// $get_or_create_room_with_target = $qiscus_sdk->getOrCreateRoomWithTarget($room_participants);
// echo "================ GET OR CREATE ROOM WITH TARGET ============";
// var_dump($get_or_create_room_with_target);
