<?php

require("pbot.class.php");

$bot = new pbot("irc.freenode.net", 6667);
$bot->connect("pbot".rand(100,999), "github.com/Joe12387/pbot", "pointless.com", "doesntmatter.net", "PHP Bot");

sleep(3); //quick, sloppy fix, enough time for server to process data before joining

$bot->doJOIN("#pbot", "testest");

while ($bot->status) {
  if ($response = $bot->read()) {
  
    foreach($response as $packet) {
      echo RECEIVED;
      echo $packet;
      echo PHP_EOL;
    
      $bot->parse($packet);
    }
  }
}

if ($bot->error) {
  echo ERROR;
  echo $bot->error;
  echo PHP_EOL;
}

echo "Disconnected.";

die(PHP_EOL);

?>