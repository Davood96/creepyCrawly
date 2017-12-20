<?php

    /**
     * Represents a Tuple <K, V>
     */
    class Pair
    {
        public $key;
        public $val;

        public function __construct($k, $v)
        {
            $this->key = $k;
            $this->val = $v;
        }
    }

?>

