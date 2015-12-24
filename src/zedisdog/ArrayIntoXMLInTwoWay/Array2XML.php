<?php
namespace zedisdog\ArrayIntoXMLInTwoWay;

use Exception;

/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *          - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *          - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *          - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *          - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *          - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *          - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *          - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *          - Removed htmlspecialchars() before adding to text node or attributes.
 * Author : zed
 * Version: 1.0
 *          - rewrite the class,keep a few function.change the usage.
 *          I'll make it what easy to adding ID attribute in the near future.
 *
 * Usage:
 *       $xml = new Array2XML('root_node_name',$version,$encoding);
 *       $xml->addNode($node_name,$arr,$parentId='root');
 *       echo $xml->getXML();
 */
class Array2XML
{
    private $xml;
    private $encoding;

    /**
     * Array2XML constructor.
     * @param string $rootNodeName 根节点名称
     * @param string $version xml版本
     * @param string $encoding xml编码
     * @throws Exception
     */
    public function __construct($rootNodeName, $version='1.0', $encoding='utf-8')
    {
        $this->encoding = $encoding;
        $this->xml = new \DOMDocument($version,$encoding);
        if(!$this->isValidTagName($rootNodeName)){
            throw new Exception("[Array2XML] Illegal character in node name. node: \'.$rootNodeName.\'");
        }else{
            $root = $this->xml->createElement($rootNodeName);
            $rootAttribute = $this->xml->createAttribute('id');
            $rootAttribute->value = 'root';
            $root->appendChild($rootAttribute);
            $root->setIdAttribute('id',true);
            $this->xml->appendChild($root);
        }
    }

    /**
     * add node into specially node or root
     * @param string $node_name
     * @param array $arr
     * @param null|string $parentId
     * @throws Exception
     */
    public function addNode($node_name, $arr, $parentId='root')
    {
        if($parentId){
            $oldParent = $this->xml->getElementById($parentId);
            $newParent = $oldParent;
            $newParent->appendChild($this->convert($node_name,$arr));
            $this->xml->replaceChild($newParent,$oldParent);
        }else{
            $this->xml->appendChild($this->convert($node_name,$arr));
        }
    }

    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return \DOMNode
     * @throws Exception
     */
    private function convert($node_name, $arr=array()) {

        //print_arr($node_name);
//        $xml = $this->xml;
        $node = $this->xml->createElement($node_name);

        if(is_array($arr)){
            // get the attributes first.;
            if(isset($arr['@attributes'])) {
                foreach($arr['@attributes'] as $key => $value) {
                    if(!self::isValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$node_name);
                    }
                    $node->setAttribute($key, $this->bool2str($value));
                }
                unset($arr['@attributes']); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if(isset($arr['@value'])) {
                $node->appendChild($this->xml->createTextNode($this->bool2str($arr['@value'])));
                unset($arr['@value']);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else if(isset($arr['@cdata'])) {
                $node->appendChild($this->xml->createCDATASection($this->bool2str($arr['@cdata'])));
                unset($arr['@cdata']);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create subnodes using recursion
        if(is_array($arr)){
            // recurse to get the node for that key
            foreach($arr as $key=>$value){
                if(!$this->isValidTagName($key)) {
                    throw new Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$node_name);
                }
                if(is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach($value as $k=>$v){
                        $node->appendChild($this->convert($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild($this->convert($key, $value));
                }
                unset($arr[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if(!is_array($arr)) {
            $node->appendChild($this->xml->createTextNode($this->bool2str($arr)));
        }

        return $node;
    }

    /**
     * 返回xml字符串
     * @return string
     */
    public function getXML()
    {
        return $this->xml->saveXML();
    }

    /*
     * Get string representation of boolean value
     */
    private function bool2str($v){
        //convert boolean to text value.
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;
        return $v;
    }

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    private function isValidTagName($tag){
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}