<?php
namespace texter;

class Texter{

    public $message;
    public $position;
    public $title;

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

class Texter2{
    static function texter(){
        return function ($pos){
            return function ($message){
                return function ($title): Texter{
                    return new Texter();
                };
            };
        };
    }

    static function apply($apply){
        $apply(new Texter());
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