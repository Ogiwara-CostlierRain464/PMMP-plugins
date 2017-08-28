<?php
namespace texter;

class Texter{

    public static function newInstance(): Texter{
        return new Texter();
    }

    function at($position): Texter{
        return this;
    }

    function message($message): Texter{
        return $this;
    }

    function show(): Texter{

    }

    function hide(){

    }
}

class TexterBuilder{
    static function at($position): TexterBuilder{

    }
    static function message($message): TexterBuilder{

    }
    static function build(): Texter{

    }
}