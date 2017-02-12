<?php

namespace Ofbeaton\DbPing;

use Symfony\Component\Console\Tester\CommandTester;
use phpmock\MockBuilder;

// http://symfony.com/doc/current/console.html#testing-commands
class ApplicationTest extends\PHPUnit_Framework_TestCase 
{

    // reported https://github.com/ofbeaton/db-ping/issues/5
    public function testConnectionInexistant()
    {
        $application = new Application();
        $command = $application->find('mysql');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                 '--host' => 'inexistant',
           '--iterations' => 3
        ));

        // the output of the command in the console
        $expected = file_get_contents(__DIR__.'/data/inexistant.regexp');
        $expected = str_replace("\n", "(?:\r\n|\r|\n)", $expected);
        $actual = $commandTester->getDisplay();
        //file_put_contents(__DIR__.'/data/inexistant.actual',$actual);
        $this->assertRegExp($expected, $actual);
    }

}
