<?php

namespace Ofbeaton\DbPing\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @since 2017-01-11
 */
class OdbcCommand extends MysqlCommand
{

    /**
     * @return void
     * @since 2016-08-04
     */
    protected function configure()
    {
        $this->addOption(
            'dsn',
            'd',
            InputOption::VALUE_REQUIRED,
            'Name of dsn in /etc/odbc.ini to connect to'
        );

        PingCommand::configure();
    }//end configure()

    public function driver() {
      return 'ODBC';
    }

    public function dsn(InputInterface $input) {
        return 'odbc:'.$input->getOption('dsn');
    }

    public function nickname(InputInterface $input) {
      return $this->dsn($input);
    }

}//end class
