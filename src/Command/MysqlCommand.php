<?php

namespace Ofbeaton\DbPing\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->driver = 'mysql';
        $this->port = 3306;
        parent::configure();
    }//end configure()

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
