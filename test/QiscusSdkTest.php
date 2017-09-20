<?php

// require 'vendor/autoload.php';
// require 'src/Qiscus/QiscusSdk.php';

// $qiscus_sdk = new \Qiscus\QiscusSdk('check', 'rahasiabanget');
// // $qiscus_sdk->localMode(true);

// $room_name = 'Testing...';
// $password = 'password';
// $emails = ['email1@mailinator.com', 'email2@mailinator.com', 'email3@mailinator.com', 'email4@mailinator.com'];
// $creator = $emails[0];
// $room_participants = [$creator, $emails[3]];

// // login or register
// foreach($emails as $email) {
//   try {
//     $user = $qiscus_sdk->loginOrRegister($email, $password, $email); 
//     var_dump($user);
//   } catch (\Exception $e) {
//     var_dump($e->getMessage());
//   }
// }

// try {
//   // create a new room
//   $create_room = $qiscus_sdk->createRoom($room_name, $emails, $creator);
//   echo "================ CREATE ROOM =======================";
//   var_dump($create_room);

//   // add participant to group
//   $add_room_participants = $qiscus_sdk->addRoomParticipants($create_room->id, $emails);
//   echo "================ ADD PARTICIPANTS ==================";
//   var_dump($add_room_participants);

//   // post comment
//   $post_comment = $qiscus_sdk->postComment($creator, $create_room->id, 'Halo');
//   echo "================ POST COMMENT ======================";
//   var_dump($post_comment);

//   // load comments
//   $load_comments = $qiscus_sdk->loadComments($create_room->id);
//   echo "================ LOAD COMMENTS =====================";
//   var_dump($load_comments);

//   // remove participant from group
//   $remove_room_participants = $qiscus_sdk->removeRoomParticipants($create_room->id, [$emails[2]]);
//   echo "================ REMOVE PARTICIPANTS ==================";
//   var_dump($remove_room_participants);

//   // get or create room with target
//   // this is for single chat only
//   $get_or_create_room_with_target = $qiscus_sdk->getOrCreateRoomWithTarget($room_participants);
//   echo "================ GET OR CREATE ROOM WITH TARGET ============";
//   var_dump($get_or_create_room_with_target);

//   $post_comment = $qiscus_sdk->postComment($creator, $get_or_create_room_with_target->id, 'Halo', 'custom', ['type' => 'bar', 'content' => 'oke']);
//   echo "================ POST COMMENT ======================";
//   var_dump($post_comment);

//   // load comments
//   $load_comments = $qiscus_sdk->loadComments($get_or_create_room_with_target->id);
//   echo "================ LOAD COMMENTS =====================";
//   var_dump($load_comments);

//   // get room info
//   $get_rooms_info = $qiscus_sdk->getRoomsInfo($creator, [$create_room->id, $get_or_create_room_with_target->id]); 
//   echo "================ GET ROOM INFO =====================";
//   var_dump($get_rooms_info);
//   $get_user_rooms = $qiscus_sdk->getUserRooms($creator); 
//   echo "================ GET ROOM INFO =====================";
//   var_dump($get_user_rooms);
// } catch (\Exception $e) {
//   var_dump($e->getMessage());
// }
