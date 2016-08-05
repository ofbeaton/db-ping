<?php

namespace Ofbeaton\DbPing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 2016-08-04
 */
class DriversCommand extends Command
{


    /**
     * @return void
     * @since 2016-08-04
     */
    protected function configure()
    {
        $this->setName('drivers');
        $this->setDescription('List all available drivers');
    }//end configure()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return int status, 0 for OK, !0 for error
     * @since 2016-08-04
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setStyle('borderless');
        $table->setHeaders(['Driver', 'Extension']);


        $found = false;
        if (extension_loaded('pdo_mysql') === true) {
            $table->addRow(['mysql', 'pdo_mysql']);
            $found = true;
        }

        if ($found === true) {
            $table->render();
        } else {
            $output->writeln('No drivers found, please enable php pdo driver extensions.');
        }

        return 0;
    }//end execute()
}//end class
