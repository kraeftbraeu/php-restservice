<?php

class Data
{
    public function printData($data)
    {
        foreach ($data as $key => $value)
            echo "<p>$key => $value</p>\n";
    }
}

?>