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

    $base_url = 'https://' . $qiscus_sdk_app_id . '.qiscus.com/';
    $this->client = new \GuzzleHttp\Client(['base_uri' => $base_url]);
  }

  /**
   * Set local mode to true if want to test SDK application from local server.
   *
   * @param boolean $local
   * @param string $base_url
   */
  public function localMode($local = false, $base_url = 'http://localhost:9000')
  {
    if ($local === true) {
      $this->client = new \GuzzleHttp\Client(['base_uri' => $base_url]);
    }
  }

  /**
   * Create new user, or if email is exist it will login.
   * Previous password will be replaced to new password if email with that user is exist.
   *
   * @param string $email required
   * @param string $password required
   * @param string $display_name
   * @param string $avatar_url
   * @param string $device_platform
   * @param string $device_token
   *
   * @return \Qiscus\Model\User;
   */
  public function loginOrRegister(string $email, string $password, string $display_name = '', string $avatar_url = '',
    string $device_platform = '', string $device_token = '')
  {
    try {
      $multipart = [
        [
          'name' => 'email', 
          'contents' => $email
        ],
        [
          'name' => 'password',
          'contents' => $password
        ],
        [
          'name' => 'username',
          'contents' => $display_name
        ],
        [
          'name' => 'avatar_url',
          'contents' => $avatar_url
        ],
        [
          'name' => 'device_platform',
          'contents' => $device_platform
        ],
        [
          'name' => 'device_token',
          'contents' => $device_token
        ]
      ];

      $response = $this->client->request('POST', '/api/v2/rest/login_or_register',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      
      $user_info = $response_json->results->user;

      $user = new \Qiscus\Model\User();
      $user->id = (string) $user_info->id;
      $user->token = $user_info->token;
      $user->email = $user_info->email;
      $user->password = $password;
      $user->display_name = $user_info->username;
      $user->avatar_url = $user_info->avatar_url;
      $user->last_comment_read_id = new \Exception('This endpoint does not return last comment read id.', 1);
      $user->last_comment_received_id = (string) $user_info->last_comment_id;
      return $user;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Create a new chat room,
   * you can assign more than 2 participant since this chat room is a group chat
   *
   * @param string $room_name
   * @param array $participants
   * @param string $creator - qiscus sdk email of creator
   *
   * @return \Qiscus\Model\Room
   */
  public function createRoom(string $room_name, array $participants, string $creator)
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
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $room_info = $response_json->results;

      $room = new \Qiscus\Model\Room();
      $room->id = (string) $room_info->room_id;
      $room->channel_id = new \Exception('This endpoint does not return channel id.', 1);
      $room->type = (string) $room_info->room_type;
      $room->name = (string) $room_info->room_name;
      $room->creator_email = (string) $room_info->creator;
      $room->avatar_url = (string) $room_info->room_avatar_url;
      $room->unread_count = new \Exception('This endpoint does not return unread count badge.', 1);
      $room->last_comment_id = '0'; // always zero in room creation
      $room->last_comment_message = ''; // always empty message in room creation
      $room->last_comment_timestamp = new \Exception('This endpoint does not return last comment timestamp.', 1);
      $room->comments = []; // always empty comment in room creation

      $participants = [];
      foreach ($response_json->results->participants as $user_info) {
        $user = new \Qiscus\Model\User();
        $user->id = (string) $user_info->id;
        $user->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $user->email = $user_info->email;
        $user->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $user->display_name = $user_info->username;
        $user->avatar_url = $user_info->avatar_url;
        $user->last_comment_read_id = (string) $user_info->last_comment_read_id;
        $user->last_comment_received_id = (string) $user_info->last_comment_received_id;

        $participants[] = $user;
      }

      $room->participants = $participants; // array(User)
      return $room;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Get room comments if given participants is exist (for single chat only)
   * otherwise it will create a new chat room using given emails
   *
   * @param array $emails
   *
   * @return \Qiscus\Model\Room
   */
  public function getOrCreateRoomWithTarget(array $emails)
  {
    try {
      if (count($emails) != 2) {
        throw new \Exception('Email participants must 2 email', 400);
      }

      $query_params = '';

      foreach ($emails as $email) {
        $query_params .= 'emails[]=' . $email . '&';
      }

      $response = $this->client->request('GET', '/api/v2/rest/get_or_create_room_with_target?' . $query_params,
        [
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $room_info = $response_json->results->room;

      $room = new \Qiscus\Model\Room();
      $room->id = (string) $room_info->id;
      $room->channel_id = (string) $room_info->unique_id;
      $room->type = (string) $room_info->chat_type;
      $room->name = (string) $room_info->room_name;
      $room->creator_email = new \Exception('This endpoint does not return creator email.', 1); // Empty response for this
      $room->avatar_url = (string) $room_info->avatar_url;
      $room->unread_count = new \Exception('This endpoint does not return unread count badge.', 1);
      $room->last_comment_id = (string) $room_info->last_comment_id;
      $room->last_comment_message = (string) $room_info->last_comment_message;
      $room->last_comment_timestamp = new \Exception('This endpoint does not return last comment timestamp.', 1);
      $room->participants = [];
      $room->comments = [];

      // set comment
      $comments = [];
      foreach ($response_json->results->comments as $c) {
        $comment = new \Qiscus\Model\Comment();
        $comment->id = (string) $c->id;
        $comment->type = (string) $c->type;
        $comment->message = (string) $c->message;
        $comment->payload = $c->payload; // already an object

        // set user creator object
        $creator = new \Qiscus\Model\User();
        $creator->id = (string) $c->user_id;
        $creator->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $creator->email = $c->email;
        $creator->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $creator->display_name = $c->username;
        $creator->avatar_url = $c->user_avatar_url;
        $creator->last_comment_read_id = new \Exception('Cannot get by this endpoint.', 1);
        $creator->last_comment_received_id = new \Exception('Cannot get by this endpoint.', 1);

        $comment->creator = $creator;

        // current room is a comment's room, so return an error instead clone it to save memory
        $comment->room = new \Exception('Use previous room object to get room info.', 1);

        $comment->unique_temp_id = $c->unique_temp_id;
        $comment->timestamp = new \DateTime((string) $c->timestamp);
        $comment->unix_timestamp = $c->unix_timestamp;
        $comment->comment_before_id = (string) $c->comment_before_id;
        $comment->disable_link_preview = $c->disable_link_preview;
        $comment->unique_temp_id = (string) $c->unique_temp_id;

        $comments[] = $comment;
      }

      $room->comments = $comments;

      // set participant after set comment, so comment room will not be return participant that causing infinite object creation
      $participants = [];
      foreach ($room_info->participants as $user_info) {
        $user = new \Qiscus\Model\User();
        $user->id = (string) $user_info->id;
        $user->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $user->email = $user_info->email;
        $user->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $user->display_name = $user_info->username;
        $user->avatar_url = $user_info->avatar_url;
        $user->last_comment_read_id = (string) $user_info->last_comment_read_id;
        $user->last_comment_received_id = (string) $user_info->last_comment_received_id;

        $participants[] = $user;
      }

      $room->participants = $participants; // array(User)

      return $room;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
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
  public function getRoomsInfo(string $user_email, array $room_ids)
  {
    try {

      $room_ids = array_unique($room_ids);
      $multipart = [];
      $multipart[] = [
        'name' => 'user_email', 
        'contents' => $user_email
      ];

      foreach ($room_ids as $room_id) {
        $multipart[] = [
          'name' => 'room_id[]', 
          'contents' => $room_id
        ];
      }

      $response = $this->client->request('POST', '/api/v2/rest/get_rooms_info',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $rooms = [];

      foreach ($response_json->results->rooms_info as $room_info) {
        $room = new \Qiscus\Model\Room();
        $room->id = (string) $room_info->room_id;
        $room->channel_id = new \Exception('This endpoint does not return channel id.', 1);
        $room->type = (string) $room_info->room_type;
        $room->name = (string) $room_info->room_name;
        $room->creator_email = new \Exception('This endpoint does not return creator email.', 1); // Empty response for this
        $room->avatar_url = (string) $room_info->room_avatar_url;
        $room->unread_count = $room_info->unread_count;
        $room->last_comment_id = (string) $room_info->last_comment_id;
        $room->last_comment_message = (string) $room_info->last_comment_message;
        $room->last_comment_timestamp = new \Exception('This endpoint does not return last comment timestamp.', 1);
        $room->participants = new \Exception('This endpoint does not return participants.', 1); // Empty response for this
        $room->comments = new \Exception('This endpoint does not return comments.', 1); // Empty response for this

        $rooms[] = $room;
      }

      return $rooms;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
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
  public function addRoomParticipants(int $room_id, array $emails)
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
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $room_info = $response_json->results;

      $room = new \Qiscus\Model\Room();
      $room->id = (string) $room_info->room_id;
      $room->channel_id = new \Exception('This endpoint does not return channel id.', 1);
      $room->type = (string) $room_info->room_type;
      $room->name = (string) $room_info->room_name;
      $room->creator_email = (string) $room_info->creator;
      $room->avatar_url = (string) $room_info->room_avatar_url;
      $room->unread_count = new \Exception('This endpoint does not return unread count badge.', 1);
      $room->last_comment_id = new \Exception('This endpoint does not return last comment id.', 1);
      $room->last_comment_message = new \Exception('This endpoint does not return last comment message.', 1);
      $room->last_comment_timestamp = new \Exception('This endpoint does not return last comment timestamp.', 1);
      $room->participants = []; // default value is empty array
      $room->comments = new \Exception('This endpoint does not return comments.', 1); // Empty response for this
      
      $participants = [];
      foreach ($response_json->results->participants as $participant_email) {
        $user = new \Qiscus\Model\User();
        $user->id = new \Exception('This endpoint does not return user id.', 1);
        $user->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $user->email = $participant_email;
        $user->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $user->display_name = new \Exception('This endpoint does not return display name.', 1);
        $user->avatar_url = new \Exception('This endpoint does not return user avatar url.', 1);
        $user->last_comment_read_id = new \Exception('This endpoint does not return last comment read id.', 1);
        $user->last_comment_received_id = new \Exception('This endpoint does not return last comment received id.', 1);

        $participants[] = $user;
      }

      $room->participants = $participants;

      return $room;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());

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
  public function removeRoomParticipants(int $room_id, array $emails)
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
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $room_info = $response_json->results;

      $room = new \Qiscus\Model\Room();
      $room->id = (string) $room_info->room_id;
      $room->channel_id = new \Exception('This endpoint does not return channel id.', 1);
      $room->type = (string) $room_info->room_type;
      $room->name = (string) $room_info->room_name;
      $room->creator_email = (string) $room_info->creator;
      $room->avatar_url = (string) $room_info->room_avatar_url;
      $room->unread_count = new \Exception('This endpoint does not return unread count badge.', 1);
      $room->last_comment_id = new \Exception('This endpoint does not return last comment id.', 1);
      $room->last_comment_message = new \Exception('This endpoint does not return last comment message.', 1);
      $room->last_comment_timestamp = new \Exception('This endpoint does not return last comment timestamp.', 1);
      $room->participants = []; // default value is empty array
      $room->comments = new \Exception('This endpoint does not return comments.', 1); // Empty response for this
      
      $participants = [];
      foreach ($response_json->results->participants as $participant) {
        $user = new \Qiscus\Model\User();
        $user->id = (string) $participant->id;
        $user->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $user->email = (string) $participant->email;
        $user->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $user->display_name = (string) $participant->username;
        $user->avatar_url = (string) $participant->avatar_url;
        $user->last_comment_read_id = (string) $participant->last_comment_read_id;
        $user->last_comment_received_id = (string) $participant->last_comment_received_id;

        $participants[] = $user;
      }

      $room->participants = $participants;
      return $room;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Post a message to a room.
   *
   * @param string $sender_email required
   * @param string $room_id required
   * @param string $message required
   * @param string $type optional, default text
   * @param string $payload optional
   * 
   * @return json object
   */
  public function postComment(string $sender_email, string $room_id, string $message, string $type = 'text', array $payload = [],
    string $unique_temp_id = '', boolean $disable_link_preview = null)
  {
    try {
      $multipart = [
        [
          'name' => 'sender_email', 
          'contents' => $sender_email
        ],
        [
          'name' => 'room_id', 
          'contents' => $room_id
        ],
        [
          'name' => 'message', 
          'contents' => $message
        ],
        [
          'name' => 'type', 
          'contents' => $type
        ],
        [
          'name' => 'payload', 
          'contents' => json_encode($payload)
        ],
        [
          'name' => 'unique_temp_id', 
          'contents' => $unique_temp_id
        ],
        [
          'name' => 'disable_link_preview', 
          'contents' => ($disable_link_preview == null || $disable_link_preview = false) ? 'false' : 'true'
        ]
      ];

      $response = $this->client->request('POST', '/api/v2/rest/post_comment',
        [
          'multipart' => $multipart,
          'headers' => [
              'Accept' => 'application/json',
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $c = $response_json->results->comment;

      $comment = new \Qiscus\Model\Comment();
      $comment->id = (string) $c->id;
      $comment->type = (string) $c->type;
      $comment->message = (string) $c->message;
      $comment->payload = $c->payload; // already an object
      // current room is a comment's room, so return an error instead clone it to save memory
      $comment->room = new \Exception('Use previous room object to get room info.', 1);
      $comment->unique_temp_id = $c->unique_temp_id;
      $comment->timestamp = new \DateTime((string) $c->timestamp);
      $comment->unix_timestamp = $c->unix_timestamp;
      $comment->comment_before_id = (string) $c->comment_before_id;
      $comment->disable_link_preview = $c->disable_link_preview;
      $comment->unique_temp_id = (string) $c->unique_temp_id;

      // set user creator object
      $result = $response_json->results;
      $creator = new \Qiscus\Model\User();
      $creator->id = (string) $result->user_id;
      $creator->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
      $creator->email = $result->email;
      $creator->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
      $creator->display_name = $result->username;
      $creator->avatar_url = $result->user_avatar_url;
      $creator->last_comment_read_id = new \Exception('Cannot get by this endpoint.', 1);
      $creator->last_comment_received_id = new \Exception('Cannot get by this endpoint.', 1);

      $comment->creator = $creator;

      return $comment;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
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
  public function loadComments(int $room_id, int $page = 1, int $limit = 20)
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
              'QISCUS_SDK_APP_ID' => $this->qiscus_sdk_app_id,
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());

      $comments = [];
      foreach ($response_json->results->comments as $c) {
        $comment = new \Qiscus\Model\Comment();
        $comment->id = (string) $c->id;
        $comment->type = (string) $c->type;
        $comment->message = (string) $c->message;
        $comment->payload = $c->payload; // already an object
        // current room is a comment's room, so return an error instead clone it to save memory
        $comment->room = new \Exception('Use previous room object to get room info.', 1);
        $comment->unique_temp_id = $c->unique_temp_id;
        $comment->timestamp = new \DateTime((string) $c->timestamp);
        $comment->unix_timestamp = $c->unix_timestamp;
        $comment->comment_before_id = (string) $c->comment_before_id;
        $comment->disable_link_preview = $c->disable_link_preview;
        $comment->unique_temp_id = (string) $c->unique_temp_id;

        // set user creator object
        $creator = new \Qiscus\Model\User();
        $creator->id = (string) $c->user_id;
        $creator->token = new \Exception('Token is confidential property of user, and it cannot be loaded using this endpoint', 1);
        $creator->email = $c->email;
        $creator->password = new \Exception('Password is confidential property of user and cannot be loaded using this endpoint.', 1);
        $creator->display_name = $c->username;
        $creator->avatar_url = $c->user_avatar_url;
        $creator->last_comment_read_id = new \Exception('Cannot get by this endpoint.', 1);
        $creator->last_comment_received_id = new \Exception('Cannot get by this endpoint.', 1);
        
        $comment->creator = $creator;

        $comments[] = $comment;
      }

      return $comments;
    } catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);

      $errors = '';
      if (property_exists($response_json->error, 'detailed_messages')) {
        $errors = join(', ', $response_json->error->detailed_messages);
      }

      throw new \Exception($response_json->error->message . ': ' . $errors, $exception->getResponse()->getStatusCode());
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }
}
