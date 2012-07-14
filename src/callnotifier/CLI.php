<?php
namespace callnotifier;


class CLI
{
    public function run()
    {
        $s = new Socket('192.168.3.95');
        $s->run();
    }
}

?>