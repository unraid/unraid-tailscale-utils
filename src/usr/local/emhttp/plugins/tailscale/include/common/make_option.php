<?php

function make_option(string $select, string $value, string $text, string $extra="") : string {
    return "<option value='$value'".($value==$select ? " selected" : "").(strlen($extra) ? " $extra" : "").">$text</option>";
  }