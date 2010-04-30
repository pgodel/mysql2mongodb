<?php

/*
 * Usage: php mysql2mongodb.php localhost root pass dbname table
 *
*/

echo "\nmysql2mongodb v0.1\n\n";

if (!isset($_SERVER['argv']) || count($_SERVER['argv'])< 2)
{
  echo "\nUsage: php mysql2mongodb.php localhost root pass dbname [table]\n\n";
  die();
}
$dbserver = $_SERVER['argv'][1];

if (!isset($_SERVER['argv'][2]))
{
  die("error: dbuser not set\n");
}
$dbuser = $_SERVER['argv'][2];

if (!isset($_SERVER['argv'][3]))
{
  die("error: dbpass not set\n");
}
$dbpass = $_SERVER['argv'][3];

if (!isset($_SERVER['argv'][4]))
{
  die("error: dbname not set\n");
}
$dbname = $_SERVER['argv'][4];


$table = '';
if (isset($_SERVER['argv'][5]))
{
  $table = $_SERVER['argv'][5];
}

$numericFields = false;

$conn = mysql_connect($dbserver, $dbuser, $dbpass);
if (!$conn) die("error: could not connect to server");

if (!mysql_select_db($dbname)) die("error: could not select db $dname");

$start = time();

$tables = array();

if (empty($table))
{
  $res = mysql_query("SHOW TABLES");
  while($row = mysql_fetch_array($res))
  {
    $tables[] = $row[0];
  }
}
else
{
  $tables[] = $table;
}

if (!class_exists('Mongo'))
{
  die("Mongo support required. Install mongo pecl extension with 'pecl install mongo; echo \"extension=mongo.so\" >> php.ini'");
}
try
{
  $mongo = new Mongo();
}
catch (MongoConnectionException $ex)
{
  error_log();
  die("Failed to connect to MongoDB - ".$ex->getMessage());
}


$total = 0;
foreach( $tables as $table )
{

  $sql = 'SELECT * FROM '.$table;

  echo "\nSelecting rows from $table...\n";
  $q = mysql_query($sql);

  $db = $mongo->$dbname;

  $c = $db->$table;

  $i = 0;
  while ($row = ($num ? mysql_fetch_array($q, MYSQL_NUM) : mysql_fetch_assoc($q) ) )
  {
    $c->insert( $row );
    $i++;
    if ( $i % 10000 == 0)
    {
      echo "Inserted $i records\n";
    }
  }

  echo $i." rows imported from $table\n";

  $total += $i;
}

$end = time();

$secs = $end - $start;

echo $total." rows imported in $secs seconds\n\n";
