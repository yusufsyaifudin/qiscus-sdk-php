<?php

namespace Qiscus;

/**
 * This is wrapper library for Qiscus SDK REST API written in PHP.
 * 
 * @date 21st February 2017
 */
class QiscusSdk
{
  
  private $qiscus_sdk_app_id;
  private $qiscus_sdk_secret;
  private $client;

  function __construct($qiscus_sdk_app_id, $qiscus_sdk_secret)
  {
    $this->qiscus_sdk_app_id = $qiscus_sdk_app_id;
    $this->qiscus_sdk_secret = $qiscus_sdk_secret;

    $base_url = "https://" . $qiscus_sdk_app_id . ".qiscus.com/";
    $this->client = new \GuzzleHttp\Client(["base_uri" => $base_url]);
  }

  /**
   * Create a new chat room,
   * you can assign more than 2 participant since this chat room is a group chat
   *
   * @param string $room_name
   * @param array $participants
   * @param string $creator - qiscus sdk email of creator
   *
   * @return json object
   */
  public function create_room(string $room_name, array $participants, string $creator)
  {
    try {
      
      // building parameters
      $multipart = [];
      $multipart[] = [
        'name' => 'name', 
        'contents' => $room_name
      ];

      foreach ($participants as $participant) {
        $multipart[] = [
          'name' => 'participants[]', 
          'contents' => $participant
        ];
      }

      $multipart[] = [
        'name' => 'creator', 
        'contents' => $creator
      ];

      $response = $this->client->request('POST', '/api/v2/rest/create_room',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      // echo $response_json->results->room_id;
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Get room comments if given participants is exist (for single chat only)
   * otherwise it will create a new chat room using given emails
   *
   * @param array $emails
   * @param string $avatar_url
   *
   * @return json object
   */
  public function get_or_create_room_with_target(array $emails, string $avatar_url = "")
  {
    try {
      $query_params = "";

      foreach ($emails as $email) {
        $query_params .= "emails[]=" . $email . "&";
      }

      $query_params .= "avatar_url=" . $avatar_url;

      $response = $this->client->request('GET', '/api/v2/rest/get_or_create_room_with_target?' . $query_params,
        [
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();

      return json_decode($responseBodyAsString);
    }
  }

  /**
   * Get room info like last comment id, last comment message, last comment timestamp, room avatar url,
   * room avatar url, room id, room name, room type, and unread count
   *
   * @param string $user_email - different user email will return different unread count
   * @param array of integer $room_ids
   *
   * @return json object
   */
  public function get_rooms_info(string $user_email, array $room_ids)
  {
    try {

      $room_ids = array_unique($room_ids);
      $query_params = "";
      foreach ($room_ids as $room_id) {
        $query_params .= "room_id[]=" . $room_id . "&";
      }

      $query_params .= "user_email=" . $user_email;

      $response = $this->client->request('GET', '/api/v2/rest/get_rooms_info?' . $query_params,
        [
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Add participants to given room
   *
   * @param integer $room_id
   * @param array of string $emails
   * 
   * @return json object
   */
  public function add_room_participants(int $room_id, array $emails)
  {
    try {
      
      // building parameters
      $multipart = [];
      $multipart[] = [
        'name' => 'room_id', 
        'contents' => $room_id
      ];

      foreach ($emails as $email) {
        $multipart[] = [
          'name' => 'emails[]', 
          'contents' => $email
        ];
      }

      $response = $this->client->request('POST', '/api/v2/rest/add_room_participants',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Remove participants from given room
   *
   * @param integer $room_id
   * @param array of string $emails
   * 
   * @return json object
   */
  public function remove_room_participants(int $room_id, array $emails)
  {
    try {
      
      // building parameters
      $multipart = [];
      $multipart[] = [
        'name' => 'room_id', 
        'contents' => $room_id
      ];

      foreach ($emails as $email) {
        $multipart[] = [
          'name' => 'emails[]', 
          'contents' => $email
        ];
      }

      $response = $this->client->request('POST', '/api/v2/rest/remove_room_participants',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Post a message to target email.
   *
   * @param string $sender_email
   * @param string $target_email
   * @param string $message
   * 
   * @return json object
   */
  public function post_comment(string $sender_email, string $target_email, string $message)
  {
    try {
      
      // building parameters
      $multipart = [];
      $multipart[] = [
        'name' => 'sender_email', 
        'contents' => $sender_email
      ];

      $multipart[] = [
        'name' => 'target_email', 
        'contents' => $target_email
      ];

      $multipart[] = [
        'name' => 'message', 
        'contents' => $message
      ];

      $response = $this->client->request('POST', '/api/v2/rest/post_comment',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Load comments for given room id
   *
   * @param integer $room_id
   * @param integer $page = default value = 1
   * @param integer $limit, default value = 20
   *
   * @return json object
   */
  public function load_comments(int $room_id, int $page = 1, int $limit = 20)
  {
    try {

      $query_params = [];
      $query_params['room_id'] = $room_id;
      $query_params['page'] = $page;
      $query_params['limit'] = $limit;

      $response = $this->client->request('GET', '/api/v2/rest/load_comments',
        [
          'query' => $query_params,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  // For testing purpose only, don't even run it in production mode
  public static function test()
  {
    throw new \Exception("For testing purpose only", 1);
    
    $qiscus_sdk = new QiscusSdk("dragongo", "dragongo-123");

    $room_name = "Testing...";
    $emails = ["userid_78_6285848458681@qisme.com", "6282110472017@qisme.com"];
    $creator = "6282110472017@qisme.com";
    $another_users = ["userid_90_6282110472018@qisme.com"];

    // create a new room
    $create_room = $qiscus_sdk->create_room($room_name, $emails, $creator);
    echo "================ CREATE ROOM =======================";
    var_dump($create_room);

    // load comments
    $load_comments = $qiscus_sdk->load_comments($create_room->results->room_id);
    echo "================ LOAD COMMENTS =====================";
    var_dump($load_comments);

    // get room info
    $get_rooms_info = $qiscus_sdk->get_rooms_info($emails[0], [$create_room->results->room_id]);
    echo "================ GET ROOM INFO =====================";
    var_dump($get_rooms_info);

    // add participant
    $add_room_participants = $qiscus_sdk->add_room_participants($create_room->results->room_id, $another_users);
    echo "================ ADD PARTICIPANTS ==================";
    var_dump($add_room_participants);

    // remove participant
    $remove_room_participants = $qiscus_sdk->remove_room_participants($create_room->results->room_id, $another_users);
    echo "================ REMOVE PARTICIPANTS ==================";
    var_dump($remove_room_participants);

    // post comment
    // this is for single chat only
    $post_comment = $qiscus_sdk->post_comment($emails[0], $emails[1], "Halo");
    echo "================ POST COMMENT ======================";
    var_dump($post_comment);

    // get or create room with target
    // this is for single chat only
    $get_or_create_room_with_target = $qiscus_sdk->get_or_create_room_with_target($emails);
    echo "================ GET OR CREATE ROOM WITH TARGET ============";
    var_dump($get_or_create_room_with_target);
  }

}