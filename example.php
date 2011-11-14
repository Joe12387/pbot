<?php
/*
 *
 * This file is part of pbot: an IRC Bot PHP class
 *   by Joe Rutkowski (Joe12387)
 *     <http://twitter.com/Joe12387>
 *     <http://www.joe12387.com/>
 *
 * <http://github.com/Joe12387/pbot>
 *
 * pbot is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * pbot is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with pbot. If not, see <http://www.gnu.org/licenses/>.
 *
 */

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