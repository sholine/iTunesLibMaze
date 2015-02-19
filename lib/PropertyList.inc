<?php
 
/**
 * PropertyList class
 * Implements writing Apple Property List (.plist) XML and text files from an array.
 *
 * @author Jesus A. Alvarez <zydeco@namedfork.net>
 */
 
function plist_encode_text ($obj) {
    $plist = new PropertyList($obj);
    return $plist->text();
}
 
function plist_encode_xml ($obj) {
    $plist = new PropertyList($obj);
    return $plist->xml();
}
 
class PropertyList
{
    private $obj, $xml, $text;
 
    public function __construct ($obj) {
        $this->obj = $obj;
    }
 
    private static function is_assoc ($array) {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
 
    public function xml () {
        if (isset($this->xml)) return $this->xml;
        $x = new XMLWriter();
        $x->openMemory();
        $x->setIndent(TRUE);
        $x->startDocument('1.0', 'UTF-8');
        $x->writeDTD('plist', '-//Apple//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
        $x->startElement('plist');
        $x->writeAttribute('version', '1.0');
        $this->xmlWriteValue($x, $this->obj);
        $x->endElement(); // plist
        $x->endDocument();
        $this->xml = $x->outputMemory();
        return $this->xml;
    }
 
    public function text() {
        if (isset($this->text)) return $this->text;
        $text = '';
        $this->textWriteValue($text, $this->obj);
        $this->text = $text;
        return $this->text;
    }
 
    private function xmlWriteDict(XMLWriter $x, &$dict) {
        $x->startElement('dict');
        foreach($dict as $k => &$v) {
            $x->writeElement('key', $k);
            $this->xmlWriteValue($x, $v);
        }
        $x->endElement(); // dict
    }
 
    private function xmlWriteArray(XMLWriter $x, &$arr) {
        $x->startElement('array');
        foreach($arr as &$v)
            $this->xmlWriteValue($x, $v);
        $x->endElement(); // array
    }
 
    private function xmlWriteValue(XMLWriter $x, &$v) {
        if (is_int($v) || is_long($v))
            $x->writeElement('integer', $v);
        elseif (is_float($v) || is_real($v) || is_double($v))
            $x->writeElement('real', $v);
        elseif (is_string($v))
            $x->writeElement('string', $v);
        elseif (is_bool($v))
            $x->writeElement($v?'true':'false');
        elseif (PropertyList::is_assoc($v))
            $this->xmlWriteDict($x, $v);
        elseif (is_array($v))
            $this->xmlWriteArray($x, $v);
        elseif (is_a($v, 'PlistData'))
            $x->writeElement('data', $v->base64EncodedData());
        elseif (is_a($v, 'PlistDate'))
            $x->writeElement('date', $v->encodedDate());
        else {
            //trigger_error("Unsupported data type '".gettype($v)."' in plist ($v)", E_USER_WARNING);
            $x->writeElement('stringg', $v);
        }
    }
 
    private function textWriteValue(&$text, &$v, $indentLevel = 0) {
        if (is_int($v) || is_long($v))
            $text .= sprintf("%d", $v);
        elseif (is_float($v) || is_real($v) || is_double($v))
            $text .= sprintf("%g", $v);
        elseif (is_string($v))
            $this->textWriteString($text, $v);
        elseif (is_bool($v))
            $text .= $v?'YES':'NO';
        elseif (PropertyList::is_assoc($v))
            $this->textWriteDict($text, $v, $indentLevel);
        elseif (is_array($v))
            $this->textWriteArray($text, $v, $indentLevel);
        elseif (is_a($v, 'PlistData'))
            $text .= '<' . $v->hexEncodedData() . '>';
        elseif (is_a($v, 'PlistDate'))
            $text .= '"' . $v->ISO8601Date() . '"';
        else {
            trigger_error("Unsupported data type in plist ($v)", E_USER_WARNING);
            $this->textWriteString($text, $v);
        }
    }
 
    private function textWriteString(&$text, &$str) {
        $oldlocale = setlocale(LC_CTYPE, "0");
        if (ctype_alnum($str)) $text .= $str;
        else $text .= '"' . $this->textEncodeString($str) . '"';
        setlocale(LC_CTYPE, $oldlocale);
    }
 
    private function textEncodeString(&$str) {
        $newstr = '';
        $i = 0;
        $len = strlen($str);
        while($i < $len) {
            $ch = ord(substr($str, $i, 1));
            if ($ch == 0x22 || $ch == 0x5C) {
                // escape double quote, backslash
                $newstr .= '\\' . chr($ch);
                $i++;
            } else if ($ch >= 0x07 && $ch <= 0x0D ){
                // control characters with escape sequences
                $newstr .= '\\' . substr('abtnvfr', $ch - 7, 1);
                $i++;
            } else if ($ch < 32) {
                // other non-printable characters escaped as unicode
                $newstr .= sprintf('\U%04x', $ch);
                $i++;
            } else if ($ch < 128) {
                // ascii printable
                $newstr .= chr($ch);
                $i++;
            } else if ($ch == 192 || $ch == 193) {
                // invalid encoding of ASCII characters
                $i++;
            } else if (($ch & 0xC0) == 0x80){
                // part of a lost multibyte sequence, skip
                $i++;
            } else if (($ch & 0xE0) == 0xC0) {
                // U+0080 - U+07FF (2 bytes)
                $u = (($ch & 0x1F) << 6) | (ord(substr($str, $i+1, 1)) & 0x3F);
                $newstr .= sprintf('\U%04x', $u);
                $i += 2;
            } else if (($ch & 0xF0) == 0xE0) {
                // U+0800 - U+FFFF (3 bytes)
                $u = (($ch & 0x0F) << 12) | ((ord(substr($str, $i+1, 1)) & 0x3F) << 6) | (ord(substr($str, $i+2, 1)) & 0x3F);
                $newstr .= sprintf('\U%04x', $u);
                $i += 3;
            } else if (($ch & 0xF8) == 0xF0) {
                // U+10000 - U+3FFFF (4 bytes)
                $u = (($ch & 0x07) << 18) | ((ord(substr($str, $i+1, 1)) & 0x3F) << 12) | ((ord(substr($str, $i+2, 1)) & 0x3F) << 6) | (ord(substr($str, $i+3, 1)) & 0x3F);
                $newstr .= sprintf('\U%04x', $u);
                $i += 4;
            } else {
                // 5 and 6 byte sequences are not valid UTF-8
                $i++;
            }
        }
        return $newstr;
    }
 
    private function textWriteDict(&$text, &$dict, $indentLevel) {
        if (count($dict) == 0) {
            $text .= '{}';
            return;
        }
        $text .= "{\n";
        $indent = '';
        $indentLevel++;
        while(strlen($indent) < $indentLevel) $indent .= "\t";
        foreach($dict as $k => &$v) {
            $text .= $indent;
            $this->textWriteValue($text, $k);
            $text .= ' = ';
            $this->textWriteValue($text, $v, $indentLevel);
            $text .= ";\n";
        }
        $text .= substr($indent, 0, -1) . '}';
    } 
 
    private function textWriteArray(&$text, &$arr, $indentLevel) {
        if (count($arr) == 0) {
            $text .= '()';
            return;
        }
        $text .= "(\n";
        $indent = '';
        $indentLevel++;
        while(strlen($indent) < $indentLevel) $indent .= "\t";
        foreach($arr as &$v) {
            $text .= $indent;
            $this->textWriteValue($text, $v, $indentLevel);
            $text .= ",\n";
        }
        $text .= substr($indent, 0, -1) . ')';
    }
}
 
class PlistData
{
    private $data;
 
    public function __construct($str) {
        $this->data = $str;
    }
 
    public function base64EncodedData () {
        return base64_encode($this->data);
    }
 
    public function hexEncodedData () {
        $len = strlen($this->data);
        $hexstr = '';
        for($i = 0; $i < $len; $i += 4)
            $hexstr .= bin2hex(substr($this->data, $i, 4)) . ' ';
        return substr($hexstr, 0, -1);
    }
}
 
class PlistDate
{
    private $dateval;
 
    public function __construct($init = NULL) {
        if (is_int($init))
            $this->dateval = $init;
        elseif (is_string($init))
            $this->dateval = strtotime($init);
        elseif ($init == NULL)
            $this->dateval = time();
    }
 
    public function ISO8601Date() {
        return gmdate('Y-m-d\TH:i:s\Z', $this->dateval);
    }
}
?>