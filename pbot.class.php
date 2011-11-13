<?php
/*
 *
 * pbot: an IRC Bot PHP class
 *
 */

define("SENT",     " << ");
define("RECEIVED", " >> ");
define("ERROR",    " !! ");

class pbot {
  public function __construct($host, $port=6667, $ssl=false) {
    $this->host   = $host;
    $this->port   = $port;
    $this->ssl    = $ssl;
    $this->status = 0; //0 = not connected; 1 = connected; -1 = connection closed/failed
    $this->error  = null;
  }
  /*
   *
   * Sends packet to current connection
   *
   * @param str $packet
   *   Packet to send
   *
   * @return bool
   *   false on failure
   *
   */
  public function send($packet) {
    $packet = trim($packet);
    
    if (fwrite($this->socket, $packet.PHP_EOL)) {
      $this->status =  1;
      return true;
    } else {
      $this->status = -1;
      return false;
    }
  }
  /*
   *
   * Connects to IRC server and issues USER, NICK, and PASS commands
   *
   * @param str $nick
   *   Nickname of the bot
   *
   * @param str $username
   *   User name of the bot
   *
   * @param str $hostname
   *   Host name of the bot (usually ignored by the server, can be anything)
   *
   * @param str $servername
   *   Server name of the bot (usually ignored by the server, can be anything)
   *
   * @param str $realname
   *   Real name of the bot
   *
   * @param str $pass
   *   [optional] Password to the IRC server
   *
   * @param int $timeout
   *   [optional] Timeout of the connection, defaults to 10 seconds
   *
   * @return bool
   *   false on failure
   *
   * <https://tools.ietf.org/html/rfc1459#section-4.1>
   *
   */
  public function connect($nick, $username, $hostname, $servername, $realname, $pass=null, $timeout=10) {
    if ($this->ssl) {
      $prefix = "ssl://";
    } else {
      $prefix = null;
    }
    
    if ($this->socket = fsockopen($prefix.$this->host, $this->port, $timeout)) {
      $this->status =  1;
    } else {
      $this->status = -1;
      return false;
    }
    
    stream_set_blocking($this->socket, 0);
    
    if (!$this->send("NICK {$nick}"))                                            return false;
    if (!$this->send("USER {$username} {$hostname} {$servername} :{$realname}")) return false;
    
    if ($pass) $this->send("PASS {$pass}");
    
    return true;
  }
  /*
   *
   * Disconnects from the current server
   *
   * @param int $status
   *   status message to set on success, see __construct()
   *
   * @return bool
   *   false on failure
   *
   */
  public function disconnect($status=0) {
    if ($this->socket and $this->status) {
      fclose($this->socket);
      $this->status = $status;
      return true;
    } else {
      return false;
    }
  }
  /*
   *
   * Parses packet into array, responds to certain packets if $readonly is false
   *
   * @param str $raw
   *   Raw packet to be parsed
   *
   * @param bool $readonly
   *   Does not respond to certain packets (PING, ERROR) if true
   *
   * @return array
   *   parsed packet
   *
   */
  public function parse($raw, $readonly=false) { //still indev
    $space = explode(" ", $raw);
    $colon = explode(":", $raw);
    
    if (!$readonly) {
      switch($space[0]) {
        case "PING":
          $this->send("PONG ".$colon[1]);
          break;
        case "ERROR":
          $this->disconnect(-1);
          $this->error = $colon[1];
          break;
      }
    }
  }
  /*
   *
   * Reads packets sent by current connection
   *
   * @param int $wait
   *   [optional] time to wait for any packets in seconds, defaults to 1
   *
   * @return array
   *   packets received
   *
   */
  public function read() {
    if (!$this->socket) $this->status = -1;
    if (!$this->status) return false;

    if (!$data = @fread($this->socket, 1024*5) or empty($data)) return false;
    
    $packets = array();
    
    foreach(explode(PHP_EOL, $data) as $packet) {
      $packet = trim($packet);
      if ($packet) {
        array_push($packets, $packet);
      }
    }
    
    return $packets;
  }
  /*
   *
   * Issues JOIN command to join a channel w/ key, if provided
   *
   * @param str $channel
   *   Channel to join
   *
   * @param str $key
   *   [optional] Key for the channel
   *
   * @return bool
   *   false on failure
   *
   * <https://tools.ietf.org/html/rfc1459#section-4.2.1>
   *
   */
  public function doJOIN($channel, $key=null) { //does not support multiple channel JOIN
    if (!$this->status) return false;

    if (substr($channel, 0, 1) != "#") {
      $channel = "#".$channel;
      trigger_error("Channel name lacks '#', assuming ".$channel, E_USER_NOTICE);
    }

    if ($this->send("JOIN {$channel} {$key}")) { //if $key is null, the extra space is trimmed in $this->send()
      return true;
    } else {
      return false;
    }
  }
  /*
   *
   * Issues PART command to leave a channel
   *
   * @param str $channel
   *   Channel to part
   *
   * @return bool
   *   false on failure
   *
   * <https://tools.ietf.org/html/rfc1459#section-4.2.2>
   *
   */
  public function doPART($channel) { //does not support multiple channel PART
    if (!$this->status) return false;
    
    if (substr($channel, 0, 1) != "#") {
      $channel = "#".$channel;
      trigger_error("Channel name lacks '#', assuming ".$channel, E_USER_NOTICE);
    }
    
    if ($this->send("PART {$channel}")) {
      return true;
    } else {
      return false;
    }
  }
}

?>