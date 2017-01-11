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

    /**
     * @return string
     */
    public function driver()
    {
        return 'ODBC';
    }//end driver()

    /**
     * @param InputInterface $input Input from the user.
     * @return string
     */
    public function dsn(InputInterface $input)
    {
        $out = 'odbc:'.$input->getOption('dsn');
        return $out;
    }//end dsn()

    /**
     * @param InputInterface $input Input from the user.
     * @return string
     */
    public function nickname(InputInterface $input)
    {
        $out = $this->dsn($input);
        return $out;
    }//end nickname()
}//end class
