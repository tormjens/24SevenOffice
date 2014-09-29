24SevenOffice
=============

PHP Wrapper Class for the 24SevenOffice API.

Usage example:
===

Create a connection
```
$connection = new Main_24SevenOffice(
	'00000000-0000-0000-0000-000000000000',
	'mail@example.com',
	'yourpassword'
);
```

Get all persons with detailed information:
```
$user = $connection->GetPersonsDetailed();
```
