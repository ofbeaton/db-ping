<?php

namespace Ofbeaton\DbPing\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @since 2016-08-04
 */
class MysqlCommand extends PingCommand
{


    /**
     * @return void
     * @since 2016-08-04
     */
    protected function configure()
    {
        $this->addOption(
            'host',
            'l',
            InputOption::VALUE_REQUIRED,
            'Host to connect to',
            '127.0.0.1' // default host
        );

        $this->addOption(
            'port',
            't',
            InputOption::VALUE_REQUIRED,
            'Port for server',
            3306 // default port
        );

        PingCommand::configure();
    }//end configure()

    /**
     * @return string
     */
    public function driver()
    {
        return 'mysql';
    }//end driver()

    /**
     * @param InputInterface $input Input from the user.
     * @return string
     */
    public function dsn(InputInterface $input)
    {
        $out = 'mysql:host='.$input->getOption('host').':'.$input->getOption('port').';charset=utf8';
        return $out;
    }//end dsn()

    /**
     * @param InputInterface $input Input from the user.
     * @return string
     */
    public function nickname(InputInterface $input)
    {
        $out = $input->getOption('host').':'.$input->getOption('port');
        return $out;
    }//end nickname()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return int status, 0 for OK, !0 for error
     * @since 2016-08-04
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('no-replication') !== false) {
            $this->checkSql = 'SHOW SLAVE STATUS;';
        }

        $ret = parent::execute($input, $output);
        return $ret;
    }//end execute()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return bool Success.
     * @since 2016-08-04
     */
    protected function queryCheck(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('no-replication') === false) {
            $this->writeReply('check passed.', $input, $output);
            return true;
        }

        $row = $this->checkStmt->fetch();

        if (empty($row) === true) {
            $this->writeReply('check passed.', $input, $output);
            return true;
        }

        $slaveIoRunning = $row['Slave_IO_Running'];
        $slaveSqlRunning = $row['Slave_SQL_Running'];
        $secondsBehindMaster = $row['Seconds_Behind_Master'];

        if ($slaveIoRunning !== 'Yes'
            || $slaveSqlRunning !== 'Yes'
            || $secondsBehindMaster > 0
        ) {
            $msg = 'check failed,';
            if ($slaveIoRunning !== 'Yes') {
                $msg = $msg.' Slave_IO_Running='.$slaveIoRunning;
            }

            if ($slaveSqlRunning !== 'Yes') {
                $msg = $msg.' Slave_SQL_Running='.$slaveSqlRunning;
            }

            if ($secondsBehindMaster > 0) {
                $msg = $msg.' Seconds_Behind_Master='.$secondsBehindMaster;
            }
            $this->writeReply($msg, $input, $output);
            return false;
        }

        $this->writeReply('check passed.', $input, $output);
        return true;
    }//end queryCheck()
}//end class
