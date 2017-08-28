<?php
namespace texter;
use texter\Texter;

class Test{
    function test()
    {
        $texter = Texter::newInstance()->at("pos")->message("message")->show();

        $texter2 = TexterBuilder::at("position")::message($message)::build()->show();
    }
}