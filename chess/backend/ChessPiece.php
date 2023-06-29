<?php

class Piece {
    function __construct($id, $x, $y, $type, $color) {
        $this->x = $x;
        $this->y = $y;
        $this->type = $type;
        $this->color = $color;
        $this->id = $id;
    }
    public $x;
    public $y;
    public $type;
    public $color;
    public $id;
}
