# rebel-flight-path

Composer library will find the path through the Imperial Defenses.

## Requirements

Composer

Library Installation / Usage
--------------------

Download and install Composer by following the [official instructions](https://getcomposer.org/download/).

In your web document root (or a sub directory), create a composer.json file, and add the following to use this library.
It will automatically include all required packages

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/codemonkeycreations/rebel-flight-path"
        }],
    "require": {
        "iamacodemonkey/rebel-flight-path": "dev-master"
    }
}
```

Create an index.php file with the following code:

```
<?php
 
require 'vendor/autoload.php';

use iamacodemonkey\MapMaker;

$mapMaker = new MapMaker();

$url = 'http://deathstar.victoriaplum.com/alliance.php';
$name = 'Chewbacca';
$validResponseCodes = array('finished' => 200, 'lost' => 410, 'crashed' => 417);

$pathThroughMines = $mapMaker->createMap($url, $name, $validResponseCodes);

print_r($pathThroughMines);

```

Visit page in a web browser.  The path (complete url) to make it to the end of the minefield will be displayed.

## NOTE:
Each step takes approximately 0.03s.  For small minefields, the delay is not noticable.
On longer minefields, you will notice a delay in receiving the final output.  All tests were over 600 steps,
so there was an 18 - 20 second delay to see the final output.

