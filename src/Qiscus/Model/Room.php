<?php

namespace Qiscus\Model;

class Room implements \JsonSerializable {

  public $id; // String
  public $channel_id; // String
  public $type; // String
  public $name; // String
  public $creator_email; // String
  public $participants; // array(User)
  public $avatar_url; // String
  public $unread_count; // int
  public $last_comment_id; // String
  public $last_comment_message; // String
  public $comments; // String

  public function jsonSerialize() {
    return get_object_vars($this);
  }

}
