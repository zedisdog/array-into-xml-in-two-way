#array into XML in two-way

##说明
这是从[lalit.lab](http://www.lalit.org/)上面拿到的array2xml和xml2array类。

##上手

###array2xml
The usage is pretty simple. You have to include the class file in your code and call the following function.

```php
$xml = Array2XML::createXML('root_node_name', $php_array);
echo $xml->saveXML();
```
Important thing to note is that the $xml object returned is of type DOMDocument and hence you can perform further operations on it.

Optionally you can also set the version of XML and encoding by calling the Array2XML::init() function before calling the Array2XML::createXML() function.

```php
Array2XML::init($version /* ='1.0' */, $encoding /* ='UTF-8' */);
```
It throws exception if the tag name or attribute name has illegal chars as per W3C spec.

###xml2array
The usage is pretty simple. You have to include the class file in your code and call the following function.
```php
$array = XML2Array::createArray($xml);
print_r($array);
```
Important thing to note is that the $array returned can be converted back to XML using the Array2XML class.

