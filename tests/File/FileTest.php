<?php

class FileTest extends TestBase {

    /** @test */
    function it_is_initializable()
    {
        $this->assertInstanceOf(
            'Reactor\Transit\File\File',
            new Reactor\Transit\File\File()
        );
    }

}