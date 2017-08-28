<?php
namespace texter;
use texter\Texter;
use texter\Texter2;

class Test{
    function test()
    {
        $texter = Texter::newInstance()->at("pos")->message("message")->show();

        $texter2 = TexterBuilder::at("position")::message($message)::build()->show();


        $texte3 = Texter2::texter()("pos")("message")("title")->show();

        $texter4 = Texter2::apply(function (Texter $t){
            $t->message = "message";
            $t->position = "pos";
            $t->title = "title";
        });
    }
}