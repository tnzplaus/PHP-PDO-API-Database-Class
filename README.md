# PHP-PDO-API-Database-Class
PDODb.php is a simple PHP PDO API wraper with prepared statements

## Getting Started

I wrote my own classes that saved my time and let me write queries efficiently and quickly. The following instructions will help you use my class for the basic purposes.
For more projects check my website [TarekNabil.com](http://www.tareknabil.com)

### 1. Installation
To utilize this class, first import PDODb.class.php into your project, and require it.
```
require_once('PDODb.class.php');                   
```
2. Initialization
Simple initialization with utf8 charset set by default:

```
$db = new PDODb('host', 'username', 'password', 'databaseName']);          
```
### 3. Insert Query
Simple example
```
$bind = array('fname' => 'Tarek', 'lname' => 'Abdel Aziz', 
		'email' => 'tarek@aucegypt.edu');
$query_array = array(
    'table' => '`messages`',
    'field' => array_keys($bind),
    'bind' => $bind
);
$lastInsertID = $db->insert($query_array);
```
Note that the method $db->insert() returns the Id of the last inserted query and all queries are represented as arrays.

Insert multiple datasets at once
```
$bind = array();
$i = 0;
foreach($_POST['country'] as $countryId){
    $bind += array("cityId".$i => $cityId);
    $bind += array("countryId".$i => $countryId);
    $i++;
}
$query_array = array(
    'table' => '`city_country`',
    'field' => array('cityId', 'countryId'),
    'bind' => $bind
);
$lastInsertID = $db->insert($query_array);
```
### 4. Update Query
Update multiple columns.
```
$bind = array('id' => 4, 'fname' => 'Tarek', 'lname' => 'Nabil', 
		'email' => 'tarek@aucegypt.edu');
$query_array = array(
    'table' => '`user`',
    'field' => array_keys($bind),
    'where' => '`id` = :id',
    'bind' => $bind
);
$db->update($query_array);
```
### 5. Select Query
Select all from table blog by id.
```
$query_array = array(
    'field' => '*',
    'from' => '`blog`',
    'where' => '`id` = :id',
    'bind' => array("id" => $blog_id)
);
$data = $db->select($query_array);
```
Here are the full properties of select query.
```
$query_array = array(
    'field' => '*',
    'from' => '`movie` INNER JOIN `movie_genre`',
    'on' => '`movie`.`id` = `movie_genre`.`movieId`',
    'where' => '`genreID` = :genreId',
    'group' => '`Id`'
    'order' => '`Id` DESC',
    'limit' => '10',
    'bind' => array("genreId" => $genreId)
);
$data = $db->select($query_array);
```
You don't need to write the whole query again.
```
$query_array = array(
    'field' => '*',
    'from' => '`movie`',
    'where' => '`id` = :movieId',
    'bind' => array("movieId" => 1)
);
$data = $db->select($query_array);

// just edit bind value

$query_array['bind'] = array("movieId" => 3);
$data = $db->select($query_array);
```
The results of select statement will be returned as array with fields' names as array keys and values as array values.
```
$query_array = array(
    'field' => 'id, title',
    'from' => '`song`',
    'limit' => 5,
    'order' => '`id` ASC'
);
$songs = $db->select($query_array);
print_r($songs);

/*---------- OUTPUT ----------*/

Array ( [0] => Array ( [id] => 1 [title] => Shape Of You )
	[1] => Array ( [id] => 2 [title] => Castle On The Hill ) 
        [2] => Array ( [id] => 3 [title] => Touch ) 
        [3] => Array ( [id] => 4 [title] => September Song ) 
        [4] => Array ( [id] => 5 [title] => Chained To The Rhythm ) 
      )
```
### 6. Delete Query
All queries are represented as arrays.
```
$query_array = array(
    'from' => '`user`',
    'where' => '`userId` = :id',
    'bind' => array('id' => 15)
);
$db->delete($query_array);
```
### 7. Exist Query
All queries are represented as arrays.
```
$query_array = array(
    'from' => '`cast`',
    'where' => 'name = :name',
    'bind' => array('name' => 'Matt Damon')
);
if($db->exists($query_array)){
	// record exists
}else{
	// record dosn't exist
}
```
### 8. Print Query
You can print query to test it against errors by setting $db->setPrintQuery(true) to true.
```
$query_array = array(
    'field' => '*',
    'from' => '`album`',
    'where' => '`genre` = :genre',
    'order' => '`id` DESC',
    'bind' => array('genre' => 'Jazz')
);
$db->setPrintQuery(true);
$data = $db->select($query_array);

/*------------ OUTPUT ------------*/

Query:
SELECT * From `album` WHERE `genre` = :genre ORDER BY `id` DESC 

Bind Array:
Array ( [genre] => Jazz ) 
```
9. Close Connection
```
$db->destruct();
```
