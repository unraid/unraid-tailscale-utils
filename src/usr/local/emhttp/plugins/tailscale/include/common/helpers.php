<?php

function make_option(string $select, string $value, string $text, string $extra="") : string {
    return "<option value='$value'".($value==$select ? " selected" : "").(strlen($extra) ? " $extra" : "").">$text</option>";
  }

  function auto_v(string $file) : string {
    global $docroot;
    $path = $docroot.$file;
    clearstatcache(true, $path);
    $time = file_exists($path) ? filemtime($path) : 'autov_fileDoesntExist';
    $newFile = "$file?v=".$time;

    return $newFile;
  }