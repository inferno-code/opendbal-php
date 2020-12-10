About
=====

OpenDBAL is an alternative for Doctrine DBAL. It is more lightweight and guarantees backward
compatibility of API in a future releases.

Features:
- Logging queries and errors
- Can rise an exceptions or work is silent mode
- Stable PDO-like API with more comfortable methods

Future plans:
- Unit tests
- API documentation
- Add support CSV/TSV data streaming to export/import datasets.

Introduction
============

DBAL - database abstraction & access layer offers a lightweight and thin runtime layer
around a PDO-like API and others.

The fact that the OpenDBAL abstracts the concrete PDO API away through the use of interfaces
that closely resemble the existing PDO API makes it possible to implement custom drivers that
may use existing native or self-made APIs.

Data Retrieval And Manipulation
===============================

OpenDBAL follows the PDO API very closely. If you have worked with PDO before you will get
to know OpenDBAL very quickly.

First start with connection:

```php
$url = "pgsql://username:password@host:port/dbname";

$conn = new \OpenDBAL\PDO\Connection($url);
```

Write an SQL query and pass it to the query() method of your connection:

```php
$statement = $conn->query("SELECT * FROM clients");
```

The query method executes the SQL and returns a database statement object. A database statement
object can be iterated to retrieve all the rows that matched the query until there are no more rows:

```php
while (($row = $statement->fetchAssoc()) !== null)
	process($row);
```

Execute the query and fetch all results into an array:

```php
$clients = $conn->fetchAllAssoc("SELECT * FROM clients");
/*
	[
		[ 'email' => 'a.deker@gmail.com', 'name' => 'Alan Deker', 'age' => 35 ],
		[ 'email' => 'liza@hotmail.com', 'name' => 'Elizabeth Brown', 'age' => 27 ],
	]
*/
```

You can fetch results using meny different methods to get comfortable data structures. As an arrays:

```php
$clients = $conn->fetchAllArray("SELECT * FROM clients");
/*
	[
		[ 'a.deker@gmail.com', 'Alan Deker', 35 ],
		[ 'liza@hotmail.com', 'Elizabeth Brown', 27 ],
	]
*/
```

Or as an objects:

```php
$clients = $conn->fetchAllObject("SELECT * FROM clients");
/*
	[
		(object) [ 'email' => 'a.deker@gmail.com', 'name' => 'Alan Deker', 'age' => 35 ],
		(object) [ 'email' => 'liza@hotmail.com', 'name' => 'Elizabeth Brown', 'age' => 27 ],
	]
*/
```

Or get value of single column:

```php
$email = $conn->fetchColumn(
	"SELECT email FROM clients WHERE id = :clientId LIMIT 1",
	[ 'clientId' => 123 ]
);
/*
	'a.deker@gmail.com'
*/
```

Fetch the data as an associative array where the key represents the first or specified column and
the value is an associative array of the rest of the columns and their values:

```php
$clients = $conn->fetchAllDictAssoc("SELECT email, name FROM clients");
/*
	[
		'a.deker@gmail.com' => 'Alan Deker',
		'liza@hotmail.com' => 'Elizabeth Brown'
	]
*/

$clients = $conn->fetchAllDictAssoc("SELECT email, name, age FROM clients");
/*
	[
		'a.deker@gmail.com' => [ 'name' => 'Alan Deker', 'age' => 35 ],
		'liza@hotmail.com' => [ 'name' => 'Elizabeth Brown', 'age' => 27 ]
	]
*/
```

OpenDBAL called this as dictionaries. Fetch same dictionary but get rows as an arrays:

```php
$clients = $conn->fetchAllDictArray("SELECT email, name, age FROM clients");
/*
	[
		'a.deker@gmail.com' => [ 'Alan Deker', 35 ],
		'liza@hotmail.com' => [ 'Elizabeth Brown', 27 ]
	]
*/
```

Or as an objects:

```php
$clients = $conn->fetchAllDictObject("SELECT email, name, age FROM clients");
/*
	[
		'a.deker@gmail.com' => (object) [ 'name' => 'Alan Deker', 'age' => 35 ],
		'liza@hotmail.com' => (object) [ 'name' => 'Elizabeth Brown', 'age' => 27 ]
	]
*/
```
