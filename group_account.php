<?php

// The "i" after the pattern delimiter indicates a case-insensitive search
$x ="100: Mr Greg Omebije 2011-04-02";
$x1 ="g 1 2011-04-02";
$x2 ="Group1 19 2011-03-17";

if (preg_match("/^\w+\s+\d+\s+(\d{4})-(\d{2})-(\d{2})/i", $x, $matches)) {
    echo "A match was found.";
	print_r($matches);
	echo "<br>";
} else {
    echo "A match was not found.<br>";
}

// get host name from URL
preg_match("/^(http:\/\/)?([^\/]+)/i",
    "http://www.php.net/index.html", $matches);
$host = $matches[2];
print_r($matches);
echo "$host<br>";
// get last two segments of host name
preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
echo "domain name is: {$matches[0]}\n";

?> 