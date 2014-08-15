<?php
//auto generated file (compiled at 2014-07-02 17:32:24)

class index
{
    public $arr = array();
    public function render_header ($val)
    {
        if(is_array($val) && (array_keys($val) !== range(0, count($val) - 1))) {
            foreach($val as $k=>$v){$$k = $v;}
        }

         $parts='<title>cms-kit Extension-Management</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, height=device-height, initial-scale=1"><link rel="stylesheet" type="text/css" href="inc/styles/style.css"><link href="../../../vendor/cmskit/lib-jquery-ui/themes/%7B%24theme%7D/jquery-ui.css" rel="stylesheet"><script src="../../../vendor/cmskit/lib-jquery-ui/jquery.min.js"></script><script>$.uiBackCompat = false;</script><script src="../../../vendor/cmskit/lib-jquery-ui/jquery-ui.js"></script>';

        return  $parts;
    }
}