#array into XML in two-way

##说明
这是从[lalit.lab](http://www.lalit.org/)上面拿到的array2xml和xml2array类。后来发现array2xml并没有满足我的要求。我就自己改了一下。现在array2xml通过实例化对象来使用。使用addNode方法可以随时添加节点。目前可能还不太灵活，有时间了继续优化它吧。
##原地址
- [array2xml](http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes/)
- [xml2array](http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array/)

## 使用前的准备

在 composer.json 文件中申明依赖：

```json
"zedisdog/array-into-xml-in-two-way": "~0.5.0"
```

##上手

###array2xml
注意：用法跟原来的不一样了。
实例化对象之后，将自动生成一个id属性为root的根节点。之后有空了，我会是它可以容易的添加id属性，以此来方便插入节点到指定的节点中。
```php
$xml = new Array2XML('root_node_name', $version /*='1.0'*/, $encoding /*='utf-8'*/);
$xml->addNode($node_name,$arr,$parentId/*='root'*/);
echo $xml->saveXML();
```

###xml2array
The usage is pretty simple. You have to include the class file in your code and call the following function.
```php
$array = XML2Array::createArray($xml);
print_r($array);
```
Important thing to note is that the $array returned can be converted back to XML using the Array2XML class.

## 致谢

- [lalit.lab](http://www.lalit.org/)