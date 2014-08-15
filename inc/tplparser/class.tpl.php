<?php

/**
 * Template parser class
 *
 * This Class parses a HTML file and extracts portions of the File as php/js/css
 *
 * based on RainTPL v. 2.7.2 (http://www.raintpl.com)
 * Distributed under GNU/LGPL 3 License
 *
 */
class kitTpl
{
    /**
     * @var bool
     */
    public $debug = false;

    public $br = "";

    public $formatOutput = false;



    private $brb;

    public $tpl_filename = '';

    /**
     * You can define in the black list what string are disabled into the template tags
     * @var array
     */
    static $black_list = array('\$this', 'tpl::', 'self::', '_SESSION', '_SERVER', '_ENV', 'eval', 'exec', 'unlink', 'rmdir');

    public $output = array(
        'php' => '',
        'js' => '',
    );

    public $myTags = array();
    public $actId = null;
    /**
     * main function
     *
     *
     *
     * @param $path
     */
    public function parse($path)
    {
        $this->brb = $this->br;
        $out = '<div>';
        $folder = dirname(realpath($path));
        $basename = basename($path, '.xhtml');


        if (!is_writable($folder)) {
            $out .= '<p class="error">Folder "' . $folder . '" is not writable!</p>';
            return $out.'</div>';
        }
        $this->tpl['tpl_filename'] = $path;

        $folder = $folder.'/'.$basename;

        if(!file_exists($folder)) {
            mkdir($folder);
        }

        chmod($folder, 0777);

        if (!$htmlString = file_get_contents($path)) {
            $out .= '<p class="error">File "' . $path . '" not found!</p>';
        } else {

            // a new dom object
            $dom = new domDocument();


            $dom->validateOnParse = false;

            // discard white space
            $dom->preserveWhiteSpace = false;

            // load the html into the object
            @$dom->loadHTML($htmlString);


            //print_r($dom);

            // getElementsByClassName
            $containers = $this->getElementsByClassName($dom, 'container');
            $count = 0;
            foreach ($containers as $container) {

                // collect data from the container
                $type = $container->attributes->getNamedItem('data-type')->nodeValue;
                $id = $container->attributes->getNamedItem('id')->nodeValue;
                $content = $this->innerHTML($container);

                // check for errors
                if (empty($type)) {
                    $out .= '<p class="error">Container ' . $count . ' has no type</p>';
                    continue;
                }
                if (!isset($this->output[$type])) {
                    $out .= '<p class="error">Container ' . $count . ' has no valid type</p>';
                    continue;
                }
                if (empty($id)) {
                    $out .= '<p class="error">Container ' . $count . ' has no id</p>';
                    continue;
                }

                // collet all tags found in the container (debugging)
                $this->actId = $id;
                $this->myTags[$this->actId] = array();

                // fix some characters
                $content = str_replace(
                    array("'",'%7B','%24','%7D'),
                    array("\\'",'{','$','}'),
                    $content
                );

                // preserve line-breaks outside of strings
                $this->br = ($type == "js") ? '@break@' : $this->brb;

                // compile the content
                $this->output[$type] .= $this->compileTemplate($content, $type, $id);

                if($this->debug) {
                    $out .= '<pre>'.print_r($this->myTags[$this->actId], true).'</pre>';
                }

                $count++;
            }//foreach container END

            // wrap a class around the php code
            if(!empty($this->output['php'])) {
                $this->output['php'] = "<?php\n//auto generated file (compiled at ".date('Y-m-d H:i:s')
                    .")\n\nclass $basename\n{\n    public \$arr = array();"
                    .$this->output['php']
                    ."\n}";
            }
            // add a comment to the header
            if(!empty($this->output['js'])) {
                $this->output['js'] = "//auto generated file (compiled at ".date('Y-m-d H:i:s').")\n".$this->output['js'];
            }


            // write to file
            foreach($this->output as $k => $str) {
                if(!empty($str)) {
                    $filepath = $folder.'/tpl.'.$k;
                    file_put_contents($filepath, $str);
                    @chmod($filepath, 0777);
                    $out .= '<p class="success">Template written to "'.$filepath.'"</p>';
                }
            }
        }
        $out .= '</div>';
        return $out;
    }


    /**
     * getElementsByClassName in php
     *
     * @param DOMDocument $DOMDocument
     * @param $ClassName
     * @return array
     */
    private function getElementsByClassName(\DOMDocument $DOMDocument, $ClassName)
    {

        $elements = $DOMDocument->getElementsByTagName('*');
        $matched = array();

        for ($i = 0; $i < $elements->length; $i++) {
            if ($elements->item($i)->attributes->getNamedItem('class')->nodeValue == $ClassName) {
                $matched[] = $elements->item($i);
            }
        }
        return $matched;
    }


    /**
     * fast innerHTML function that returns the result without iterating over child nodes
     *
     * @param $el
     * @return mixed
     */
    private function innerHTML($el)
    {
        $doc = new DOMDocument();
        $doc->formatOutput = $this->formatOutput;
        $doc->preserveWhiteSpace = false;
        $doc->validateOnParse = false;

        $doc->appendChild($doc->importNode($el, true));
        $html = trim($doc->saveHTML());
        $tag = $el->nodeName;
        return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
    }

    /**
     * Compile template
     *
     * @access protected
     * @param $template_code
     * @param $type
     * @param $id
     * @return string
     */
    protected function compileTemplate($template_code, $type, $id)
    {
        $br = $this->br;
        //tag list
        $tag_regexp = array('loop' => '(\{loop(?: name){0,1}="\${0,1}[^"]*"\})',
            'loop_close' => '(\{\/loop\})',
            'if' => '(\{if(?: condition){0,1}="[^"]*"\})',
            'elseif' => '(\{elseif(?: condition){0,1}="[^"]*"\})',
            'else' => '(\{else\})',
            'if_close' => '(\{\/if\})',
            'function' => '(\{function="[^"]*"\})',
            'noparse' => '(\{noparse\})',
            'noparse_close' => '(\{\/noparse\})',
            'ignore' => '(\{ignore\}|\{\*)',
            'ignore_close' => '(\{\/ignore\}|\*\})',
            'include' => '(\{include="[^"]*"(?: cache="[^"]*")?\})',
            'template_info' => '(\{\$template_info\})',
            'function' => '(\{function="(\w*?)(?:.*?)"\})',
        );

        $tag_regexp = "/" . join("|", $tag_regexp) . "/";

        //split the code with the tags regexp
        $template_code = preg_split($tag_regexp, $template_code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        //print_r($template_code);

        //compile, encapsulate and return the code
        $compiled_code = $this->compileCode($template_code, $type);

        //return the compiled code
        switch($type)
        {
            //
            case 'php':
                return "
    public function render_$id (\$_V)
    {
        if(is_array(\$_V) && (array_keys(\$_V) !== range(0, count(\$_V) - 1))) {
            foreach(\$_V as \$k=>\$v){\$\$k = \$v;}
        }

        \$_P='" . $compiled_code . "';

        return  \$_P;
    }";
            break;
            //  simple javascript function
            /*
             * is_array
             * sizeof
             *
             * foreach
             *
             */
            case 'js':

                // fix some code for javascript
                $compiled_code = str_replace(
                    array('.=', "\n", "';", 'elseif', '@break@'),
                    array('+=', '', "';\n", 'else if', $this->brb),
                    $compiled_code
                );

                return "
    function render_$id (\$_V)
    {
        if(typeof(\$_V==='object') && \$_V.length===0) {
            for(e in \$_V) this[e] = \$_V[e];
        }
        var \$_P='" . $compiled_code . "';

        return  \$_P;
    }";
            break;

        }





    }



    /**
     * Compile the code
     * @access protected
     * @param $parsed_code
     * @param $type
     * @return null|string
     * @throws Tpl_Exception
     */
    protected function compileCode($parsed_code, $type)
    {

        //variables initialization
        $compiled_code = $open_if = $comment_is_open = $ignore_is_open = null;
        $loop_level = 0;

        $br = $this->br;
        //read all parsed code
        while ($html = array_shift($parsed_code)) {

            //close ignore tag
            if (!$comment_is_open && (strpos($html, '{/ignore}') !== FALSE || strpos($html, '*}') !== FALSE))
                $ignore_is_open = false;

            //code between tag ignore id deleted
            elseif ($ignore_is_open) {
                //ignore the code
            } //close no parse tag
            elseif (strpos($html, '{/noparse}') !== FALSE)
                $comment_is_open = false;

            //code between tag noparse is not compiled
            elseif ($comment_is_open)
                $compiled_code .= $html;

            //ignore
            elseif (strpos($html, '{ignore}') !== FALSE || strpos($html, '{*') !== FALSE)
                $ignore_is_open = true;

            //noparse
            elseif (strpos($html, '{noparse}') !== FALSE)
                $comment_is_open = true;



            //loop
            elseif (preg_match('/\{loop(?: name){0,1}="\${0,1}([^"]*)"\}/', $html, $code)) {

                //increase the loop counter
                $loop_level++;
                $this->myTags[$this->actId][] = $code[0].'/'.$code[1];

                //replace the variable in the loop
                $var = $this->var_replace('$' . $code[1], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level - 1);

                //loop variables
                $counter = "\$counter$loop_level"; // count iteration
                $key = "\$key$loop_level"; // key
                $value = "\$value$loop_level"; // value

                //loop code
                if($type=='php') {

                    $compiled_code .= "'; $br $counter=-1; $br if( isset($var) && is_array($var) && sizeof($var) ) foreach( $var as $key => $value ){ $br $counter++;  $br \$_P.='";
                }
                if($type=='js') {
                    $compiled_code .= "'; $br $counter=-1; $br if( $var && typeof($var)=='object' ) for( $key in $var ){ $br $value = $var"."[".$key."], $counter++;  $br \$_P.='";
                }

            } //close loop tag
            elseif (strpos($html, '{/loop}') !== FALSE) {

                //iterator
                $counter = "\$counter$loop_level";

                //decrease the loop counter
                $loop_level--;

                //close loop code
                $compiled_code .= "'; } $br \$_P.='";

            } //if
            elseif (preg_match('/\{if(?: condition){0,1}="([^"]*)"\}/', $html, $code)) {

                //increase open if counter (for intendation)
                $open_if++;

                $this->myTags[$this->actId][] = $code[0].'/'.$code[1];

                //tag
                $tag = $code[0];

                //condition attribute
                $condition = $code[1];

                // check if there's any function disabled by black_list
                $this->function_check($tag);

                //variable substitution into condition (no delimiter into the condition)
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);

                //if code
                $compiled_code .= "'; $br if( $parsed_condition ){  $br \$_P.='";

            } //elseif
            elseif (preg_match('/\{elseif(?: condition){0,1}="([^"]*)"\}/', $html, $code)) {


                $this->myTags[$this->actId][] = $code[0].'/'.$code[1];

                //tag
                $tag = $code[0];

                //condition attribute
                $condition = $code[1];

                    //variable substitution into condition (no delimiter into the condition)
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);

                //elseif code
                $compiled_code .= "'; } $br elseif( $parsed_condition ){  $br \$_P.='";
            } //else
            elseif (strpos($html, '{else}') !== FALSE) {

                $this->myTags[$this->actId][] = '{else}';

                //else code
                $compiled_code .= "'; } $br else{  $br \$_P.='";


            } //close if tag
            elseif (strpos($html, '{/if}') !== FALSE) {

                $this->myTags[$this->actId][] = '{/if}';

                //decrease if counter
                $open_if--;

                // close if code
                $compiled_code .= "'; }  $br \$_P.='";

            } //function
            elseif (preg_match('/\{function="(\w*)(.*?)"\}/', $html, $code)) {

                $this->myTags[$this->actId][] = $code[0].'/'.$code[1];

                //tag
                $tag = $code[0];

                //function
                $function = $code[1];

                // check if there's any function disabled by black_list
                $this->function_check($tag);

                if (empty($code[2]))
                    $parsed_function = $function . "()";
                else
                    // parse the function
                    $parsed_function = $function . $this->var_replace($code[2], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);



                //if code
                $compiled_code .= "';  $br \$_P.= $parsed_function;  $br \$_P.='";
            }


            //all html code
            else {

                //variables substitution (es. {$title})
                $html = $this->var_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = "' ", $php_right_delimiter = ";  $br \$_P.='", $loop_level, $echo = true);
                //const substitution (es. {#CONST#})
                $html = $this->const_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = "' ", $php_right_delimiter = ";  $br \$_P.='", $loop_level, $echo = true);
                //functions substitution (es. {"string"|functions})
                $compiled_code .= $this->func_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = "' ", $php_right_delimiter = ";  $br \$_P.='", $loop_level, $echo = true);
            }
        }

        if ($open_if > 0) {
            $e = new Tpl_SyntaxException('Error! You need to close an {if} tag in ' . $this->tpl['tpl_filename'] . ' template');

        }
        return $compiled_code;
    }



    /**
     * reduce a path, eg. www/library/../filepath//file => www/filepath/file
     * @param $path
     * @return mixed
     */
    protected function reduce_path($path)
    {
        $path = str_replace("://", "@not_replace@", $path);
        $path = str_replace("//", "/", $path);
        $path = str_replace("@not_replace@", "://", $path);
        return preg_replace('/\w+\/\.\.\//', '', $path);
    }

    /**
     * replace const
     * @param $html
     * @param $tag_left_delimiter
     * @param $tag_right_delimiter
     * @param null $php_left_delimiter
     * @param null $php_right_delimiter
     * @param null $loop_level
     * @param null $echo
     * @return mixed
     */
    function const_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null)
    {
        // const
        $br = $this->br;
        return preg_replace('/\{\#(\w+)\#{0,1}\}/', $php_left_delimiter . ($echo ? ";  $br \$_P.= \$this->arr['" : null) . '\\1' . "']" . $php_right_delimiter, $html);
    }



    /**
     * replace functions/modifiers on constants and strings
     *
     * @param $html
     * @param $tag_left_delimiter
     * @param $tag_right_delimiter
     * @param null $php_left_delimiter
     * @param null $php_right_delimiter
     * @param null $loop_level
     * @param null $echo
     * @return mixed
     */
    function func_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null)
    {
        $br = $this->br;
        preg_match_all('/' . '\{\#{0,1}(\"{0,1}.*?\"{0,1})(\|\w.*?)\#{0,1}\}' . '/', $html, $matches);

        for ($i = 0, $n = count($matches[0]); $i < $n; $i++) {

            $this->myTags[$this->actId][] = $matches[0][$i];

            //complete tag ex: {$news.title|substr:0,100}
            $tag = $matches[0][$i];

            //variable name ex: news.title
            $var = $matches[1][$i];

            //function and parameters associate to the variable ex: substr:0,100
            $extra_var = $matches[2][$i];

            // check if there's any function disabled by black_list
            $this->function_check($tag);

            $extra_var = $this->var_replace($extra_var, null, null, null, null, $loop_level);


            // check if there's an operator = in the variable tags, if there's this is an initialization so it will not output any value
            $is_init_variable = preg_match("/^(\s*?)\=[^=](.*?)$/", $extra_var);

            //function associate to variable
            $function_var = ($extra_var and $extra_var[0] == '|') ? substr($extra_var, 1) : null;

            //variable path split array (ex. $news.title o $news[title]) or object (ex. $news->title)
            $temp = preg_split("/\.|\[|\-\>/", $var);

            //variable name
            $var_name = $temp[0];

            //variable path
            $variable_path = substr($var, strlen($var_name));

            //parentesis transform [ e ] in [" e in "]
            $variable_path = str_replace('[', '["', $variable_path);
            $variable_path = str_replace(']', '"]', $variable_path);

            //transform .$variable in ["$variable"]
            $variable_path = preg_replace('/\.\$(\w+)/', '["$\\1"]', $variable_path);

            //transform [variable] in ["variable"]
            $variable_path = preg_replace('/\.(\w+)/', '["\\1"]', $variable_path);

            //if there's a function
            if ($function_var) {

                // check if there's a function or a static method and separate, function by parameters
                $function_var = str_replace("::", "@double_dot@", $function_var);

                // get the position of the first :
                if ($dot_position = strpos($function_var, ":")) {

                    // get the function and the parameters
                    $function = substr($function_var, 0, $dot_position);
                    $params = substr($function_var, $dot_position + 1);

                } else {

                    //get the function
                    $function = str_replace("@double_dot@", "::", $function_var);
                    $params = null;

                }

                // replace back the @double_dot@ with ::
                $function = str_replace("@double_dot@", "::", $function);
                $params = str_replace("@double_dot@", "::", $params);


            } else
                $function = $params = null;

            $php_var = $var_name . $variable_path;

            // compile the variable for php
            if (isset($function)) {
                if ($php_var)
                    $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? ";  $br \$_P.= " : null) . ($params ? "( $function( $php_var, $params ) )" : "$function( $php_var )") . $php_right_delimiter;
                else
                    $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? ";  $br \$_P.= " : null) . ($params ? "( $function( $params ) )" : "$function()") . $php_right_delimiter;
            } else
                $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? ";  $br \$_P.= " : null) . $php_var . $extra_var . $php_right_delimiter;

            $html = str_replace($tag, $php_var, $html);

        }

        return $html;

    }

    /**
     * @param $html
     * @param $tag_left_delimiter
     * @param $tag_right_delimiter
     * @param null $php_left_delimiter
     * @param null $php_right_delimiter
     * @param null $loop_level
     * @param null $echo
     * @return mixed
     */
    function var_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null)
    {
        $br = $this->br;
        //all variables
        if (preg_match_all('/' . $tag_left_delimiter . '\$(\w+(?:\.\${0,1}[A-Za-z0-9_]+)*(?:(?:\[\${0,1}[A-Za-z0-9_]+\])|(?:\-\>\${0,1}[A-Za-z0-9_]+))*)(.*?)' . $tag_right_delimiter . '/', $html, $matches)) {

            for ($parsed = array(), $i = 0, $n = count($matches[0]); $i < $n; $i++)
                $parsed[$matches[0][$i]] = array('var' => $matches[1][$i], 'extra_var' => $matches[2][$i]);

            foreach ($parsed as $tag => $array) {

                $this->myTags[$this->actId][] = $tag;

                    //variable name ex: news.title
                $var = $array['var'];

                //function and parameters associate to the variable ex: substr:0,100
                $extra_var = $array['extra_var'];

                // check if there's any function disabled by black_list
                $this->function_check($tag);

                $extra_var = $this->var_replace($extra_var, null, null, null, null, $loop_level);

                // check if there's an operator = in the variable tags, if there's this is an initialization so it will not output any value
                $is_init_variable = preg_match("/^[a-z_A-Z\.\[\](\-\>)]*=[^=]*$/", $extra_var);

                //function associate to variable
                $function_var = ($extra_var and $extra_var[0] == '|') ? substr($extra_var, 1) : null;

                //variable path split array (ex. $news.title o $news[title]) or object (ex. $news->title)
                $temp = preg_split("/\.|\[|\-\>/", $var);

                //variable name
                $var_name = $temp[0];

                //variable path
                $variable_path = substr($var, strlen($var_name));

                //parentesis transform [ e ] in [" e in "]
                $variable_path = str_replace('[', '["', $variable_path);
                $variable_path = str_replace(']', '"]', $variable_path);

                //transform .$variable in ["$variable"] and .variable in ["variable"]
                $variable_path = preg_replace('/\.(\${0,1}\w+)/', '["\\1"]', $variable_path);

                // if is an assignment also assign the variable to $this->var['value']
                if ($is_init_variable)
                    $extra_var = "=\$this->arr['{$var_name}']{$variable_path}" . $extra_var;


                //if there's a function
                if ($function_var) {

                    // check if there's a function or a static method and separate, function by parameters
                    $function_var = str_replace("::", "@double_dot@", $function_var);


                    // get the position of the first :
                    if ($dot_position = strpos($function_var, ":")) {

                        // get the function and the parameters
                        $function = substr($function_var, 0, $dot_position);
                        $params = substr($function_var, $dot_position + 1);

                    } else {

                        //get the function
                        $function = str_replace("@double_dot@", "::", $function_var);
                        $params = null;

                    }

                    // replace back the @double_dot@ with ::
                    $function = str_replace("@double_dot@", "::", $function);
                    $params = str_replace("@double_dot@", "::", $params);
                } else
                    $function = $params = null;

                //if it is inside a loop
                if ($loop_level) {
                    //verify the variable name
                    if ($var_name == 'key')
                        $php_var = '$key' . $loop_level;
                    elseif ($var_name == 'value')
                        $php_var = '$value' . $loop_level . $variable_path;
                    elseif ($var_name == 'counter')
                        $php_var = '$counter' . $loop_level;
                    else
                        $php_var = '$' . $var_name . $variable_path;
                } else
                    $php_var = '$' . $var_name . $variable_path;

                // compile the variable for php
                if (isset($function))
                    $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? ";  $br \$_P.= " : null) . ($params ? "( $function( $php_var, $params ) )" : "$function( $php_var )") . $php_right_delimiter;
                else
                    $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? ";  $br \$_P.= " : ($is_init_variable ? ";$br" : null) ) .  $php_var . $extra_var . $php_right_delimiter;

                $html = str_replace($tag, $php_var, $html);


            }
        }

        return $html;
    }


    /**
     * Check if function is in black list (sandbox)
     *
     * @param string $code
     * @param string $tag
     */
    protected function function_check($code)
    {
        $br = $this->br;
        $preg = '#(\W|\s)' . implode('(\W|\s)|(\W|\s)', self::$black_list) . '(\W|\s)#';

        // check if the function is in the black list (or not in white list)
        if (count(self::$black_list) && preg_match($preg, $code, $match)) {

            // find the line of the error
            $line = 0;
            $rows = explode("\n", $this->tpl['source']);
            while (!strpos($rows[$line], $code))
                $line++;

            // stop the execution of the script
            $e = new Tpl_SyntaxException('Unallowed syntax in ' . $this->tpl['tpl_filename'] . ' template');

        }

    }

    /**
     * Prints debug info about exception or passes it further if debug is disabled.
     *
     * @param Tpl_Exception $e
     * @return string
     */
    protected function printDebug(Tpl_Exception $e)
    {

        if (!self::$debug) {
            throw $e;
        }
        $output = sprintf('<h2>Exception: %s</h2><h3>%s</h3><p>template: %s</p>',
            get_class($e),
            $e->getMessage(),
            $e->getTemplateFile()
        );
        if ($e instanceof Tpl_SyntaxException) {
            if (null != $e->getTemplateLine()) {
                $output .= '<p>line: ' . $e->getTemplateLine() . '</p>';
            }
            if (null != $e->getTag()) {
                $output .= '<p>in tag: ' . htmlspecialchars($e->getTag()) . '</p>';
            }
            if (null != $e->getTemplateLine() && null != $e->getTag()) {
                $rows = explode("\n", htmlspecialchars($this->tpl['source']));
                $rows[$e->getTemplateLine()] = '<font color=red>' . $rows[$e->getTemplateLine()] . '</font>';
                $output .= '<h3>template code</h3>' . implode('<br />', $rows) . '</pre>';
            }
        }
        $output .= sprintf('<h3>trace</h3><p>In %s on line %d</p><pre>%s</pre>',
            $e->getFile(), $e->getLine(),
            nl2br(htmlspecialchars($e->getTraceAsString()))
        );
        return $output;
    }

}


/**
 * Basic Rain tpl exception.
 */
class Tpl_Exception extends Exception
{
    /**
     * Path of template file with error.
     */
    protected $templateFile = '';

    /**
     * Returns path of template file with error.
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }


}

/**
 * Exception thrown when template file does not exists.
 */
class Tpl_NotFoundException extends Tpl_Exception
{
}

/**
 * Exception thrown when syntax error occurs.
 */
class Tpl_SyntaxException extends Tpl_Exception
{
    /**
     * Line in template file where error has occured.
     *
     * @var int | null
     */
    protected $templateLine = null;

    /**
     * Tag which caused an error.
     *
     * @var string | null
     */
    protected $tag = null;

    /**
     * Returns line in template file where error has occured
     * or null if line is not defined.
     *
     * @return int | null
     */
    public function getTemplateLine()
    {
        return $this->templateLine;
    }

    /**
     * Sets  line in template file where error has occured.
     *
     * @param int $templateLine
     * @return Tpl_SyntaxException
     */
    public function setTemplateLine($templateLine)
    {
        $this->templateLine = (int)$templateLine;
        return $this;
    }

    /**
     * Returns tag which caused an error.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Sets tag which caused an error.
     *
     * @param string $tag
     * @return Tpl_SyntaxException
     */
    public function setTag($tag)
    {
        $this->tag = (string)$tag;
        return $this;
    }
}

?>
