24SevenOffice
=============

PHP Wrapper Class for the 24SevenOffice API.

Usage example:
===

Create a connection
```
<?php
$connection = new Main_24SevenOffice(
	'00000000-0000-0000-0000-000000000000',
	'mail@example.com',
	'yourpassword'
);
?>
```

Get all persons with detailed information:
```
<?php
$user = $connection->GetPersonsDetailed();
?>
```
