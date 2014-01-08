torrentEdit
===========

Read data from ".torrent" to array and write changed data

use:
====

init:
```php
$torrent = new torrent($raw_torrent_data);
```
or
```php
$torrent = new torrent();
$torrent->load($raw_torrent_data);
```
view
```php
$torrent->array; // all data in array;
$torrent->data; // original raw_torrent_data
```
edit and save
```php
$torrent->array; // do any changes
$torrent->save(); // generate and return raw data from $this->array
$torrent->save($filename); // generate raw data from $this->array and save to $filename (return true)
```

TODO:
-----
- in `->save($file)` need check for possible write, and successful writer
