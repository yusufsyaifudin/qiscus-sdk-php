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
   * @return json object
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
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    }  catch (\GuzzleHttp\Exception\BadResponseException $exception) {
      // docs.guzzlephp.org/en/latest/quickstart.html#exceptions
      // for 500-level errors or 400-level errors
      $response_body = $exception->getResponse()->getBody(true);
      $response_json = json_decode((string) $response_body);
      return $response_json;
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
   * @return json object
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
   *
   * @return json object
   */
  public function getOrCreateRoomWithTarget(array $emails)
  {
    try {
      if (count($emails) != 2) {
        throw new \Exception('Email participants must 2 email');
      }

      $query_params = '';

      foreach ($emails as $email) {
        $query_params .= 'emails[]=' . $email . '&';
      }

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
              'QISCUS_SDK_SECRET' => $this->qiscus_sdk_secret
          ]
        ]);

      $response_json = json_decode((string) $response->getBody());
      return $response_json;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }
}