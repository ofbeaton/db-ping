<?php

namespace Ofbeaton\DbPing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 2016-08-04
 */
abstract class PingCommand extends Command
{

    /**
     * @var float
     * @since 2016-08-04
     */
    protected $badTime = 0.0;

    /**
     * @var string
     * @since 2016-08-04
     */
    protected $checkSql = 'SELECT 1;';

    /**
     * @var \PDOStatement
     * @since 2016-08-04
     */
    protected $checkStmt = null;

    /**
     * @var integer
     * @since 2016-08-04
     */
    protected $connected = false;

    /**
     * @var \PDO
     * @since 2016-08-04
     */
    protected $dbh = null;

    /**
     * @var string
     * @since 2016-08-04
     */
    protected $driver = null;

    /**
     * @var float
     * @since 2016-08-05
     */
    protected $goodTime = 0.0;

    /**
     * @var InputInterface
     * @since 2016-08-04
     */
    protected $input = null;

    /**
     * @var array
     * @since 2016-08-04
     */
    protected $pdoOptions = [
                             \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                             \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                            ];

    /**
     * @var integer
     * @since 2016-08-04
     */
    protected $port = null;

    /**
     * @var OutputInterface
     * @since 2016-08-04
     */
    protected $output = null;

    /**
     * @var string
     * @since 2016-08-04
     */
    protected $user = 'root';

    /**
     * @var float
     * @since 2016-08-05
     */
    protected $sinceBad = null;

    /**
     * @var float
     * @since 2016-08-05
     */
    protected $sinceGood = null;

    /**
     * @var float
     * @since 2016-08-04
     */
    protected $startTime = 0.0;

    /**
     * @var integer
     * @since 2016-08-04
     */
    protected $statsIterations = 0;

    /**
     * @var integer
     * @since 2016-08-04
     */
    protected $statsFailures = 0;

    /**
     * @var float
     * @since 2016-08-04
     */
    protected $stopTime = 0.0;

    /**
     * @var float
     * @since 2016-08-05
     */
    protected $totalTime = null;


/**
 * @return void
 * @since 2016-08-04
 * @throws \RuntimeException Driver must be specified in configure().
 */
    protected function configure()
    {
        if ($this->driver === null) {
            throw new \RuntimeException('Driver must be specified in configure().');
        }

        $this->setName($this->driver);
        $this->setDescription('Verify a '.$this->driver.' server is responding');

        $this->addOption(
            'delay',
            null,
            InputOption::VALUE_REQUIRED,
            'Delay between pings in miliseconds',
            2000
        );

        $this->addOption(
            'host',
            'l',
            InputOption::VALUE_REQUIRED,
            'Host to connect to',
            '127.0.0.1'
        );

        $this->addOption(
            'iterations',
            'i',
            InputOption::VALUE_REQUIRED,
            'Number of times to ping'
        );

        $this->addOption(
            'no-replication',
            null,
            InputOption::VALUE_NONE,
            'Do not check replication status'
        );

        $this->addOption(
            'pass',
            'p',
            InputOption::VALUE_REQUIRED,
            'Password for user'
        );

        $this->addOption(
            'port',
            't',
            InputOption::VALUE_REQUIRED,
            'Port for server',
            $this->port
        );

        $this->addOption(
            'user',
            'u',
            InputOption::VALUE_REQUIRED,
            'User used in connection',
            $this->user
        );

        $this->addOption(
            'timeout',
            null,
            InputOption::VALUE_REQUIRED,
            'Seconds to wait to connect',
            10
        );
    }//end configure()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return int status, 0 for OK, !0 for error
     * @since 2016-08-04
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('iterations') !== false) {
            $iterations = $input->getOption('iterations');
        } else {
            $iterations = -1;
        }

        $delay = ($input->getOption('delay') * 1000);

        $this->pdoOptions = array_merge($this->pdoOptions, [\PDO::ATTR_TIMEOUT => $input->getOption('timeout')]);

        $output->writeln('DB-PING '.$input->getOption('host').':'.$input->getOption('port'));

        $signals = false;
        if (function_exists('pcntl_signal') === true) {
            $signals = true;
            $this->input = $input;
            $this->output = $output;
            \pcntl_signal(SIGINT, [$this, 'handleSigint']);
        }


        while ($iterations !== 0) {
            $now = microtime(true);
            if ($this->totalTime === null) {
                $this->totalTime = $now;
            }

            if ($signals === true) {
                pcntl_signal_dispatch();
            }

            $this->statsIterations++;

            $ret = $this->ping($input, $output);
            if ($ret === false) {
                $this->statsFailures++;
            }

            if ($ret === true) {
                if ($this->sinceBad !== null) {
                    $this->badTime = ($this->badTime + ($now - $this->sinceBad));
                    $this->sinceBad = null;
                }

                if ($this->sinceGood === null) {
                    $this->sinceGood = $now;
                }
            } else {
                if ($this->sinceGood !== null) {
                    $this->goodTime = ($this->goodTime + ($now - $this->sinceGood));
                    $this->sinceGood = null;
                }

                if ($this->sinceBad === null) {
                    $this->sinceBad = $now;
                }
            }

            $iterations--;
            if ($iterations !== 0) {
                usleep($delay);
            }
        }//end while

        $this->stats($input, $output);

        // close connection
        $this->dbh = null;

        return 0;
    }//end execute()

    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return bool Success.
     */
    protected function ping(InputInterface $input, OutputInterface $output)
    {
        if ($this->connected === false) {
            $ret = $this->connect($input, $output);
            if ($ret === false) {
                return false;
            }
        }

        $errorLevel = error_reporting();
        error_reporting(((E_ALL & ~E_NOTICE) & ~E_WARNING));
        $this->startTime = microtime(true);
        try {
            $this->checkStmt = $this->dbh->query($this->checkSql);
            $this->stopTime = microtime(true);
        } catch (\PDOException $e) {
            $this->stopTime = microtime(true);
            error_reporting($errorLevel);

            $errorInfo = $this->dbh->errorInfo();
            // server has gone away
            if (isset($errorInfo[1]) === true && $errorInfo[1] === 2006) {
                $this->connected = false;
                $this->writeReply('connection lost', $input, $output);
            } else {
                $this->writeReply('check failed with exception: '.rtrim($e->getMessage(), PHP_EOL), $input, $output);
            }
            return false;
        }

        error_reporting($errorLevel);

        if ($this->checkStmt === false) {
            $errorInfo = $this->dbh->errorInfo();
            // server has gone away
            if (isset($errorInfo[1]) === true && $errorInfo[1] === 2006) {
                $this->connected = false;
                $this->writeReply('connection lost', $input, $output);
            } else {
                $this->writeReply(
                    'check failed statement: ['.$this->dbh->errorCode().'] '
                    .implode(' ', $errorInfo),
                    $input,
                    $output
                );
            }
            return false;
        }

        $ret = $this->queryCheck($input, $output);
        if ($ret === false) {
            return false;
        }

        return true;
    }//end ping()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return bool Success.
     */
    protected function connect(InputInterface $input, OutputInterface $output)
    {
        if ($this->connected === true) {
            return true;
        }

        $this->startTime = microtime(true);
        try {
            $dsn = 'mysql:host='.$input->getOption('host').':'.$input->getOption('port').';charset=utf8';
            $this->dbh = new \PDO($dsn, $input->getOption('user'), $input->getOption('pass'), $this->pdoOptions);
            $this->stopTime = microtime(true);
            $this->connected = true;
        } catch (\PDOException $e) {
            $this->stopTime = microtime(true);

            // No connection could be made because the target machine actively refused it.
            if ($e->getCode() === 2002) {
                $this->writeReply('connection refused.', $input, $output);
            } else {
                $this->writeReply('connection failed: '.rtrim($e->getMessage(), PHP_EOL.'.').'.', $input, $output);
            }
            $this->dbh = null;
            $this->connected = false;
            return false;
        }

        // make sure our modes are set
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $this->writeReply('connected', $input, $output);
        return true;
    }//end connect()

    /**
     * @return void
     * @since 2016-08-04
     */
    protected function handleSigint()
    {
        $this->stats($this->input, $this->output);

        // close connection
        $this->dbh = null;

        // make sure we exit
        die(0);
    }//end handleSigint()


    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return void
     * @since 2016-08-04
     */
    protected function stats(InputInterface $input, OutputInterface $output)
    {
        $now = microtime(true);

        if ($this->sinceBad !== null) {
            $this->badTime = ($this->badTime + ($now - $this->sinceBad));
            $this->sinceBad = null;
        }

        if ($this->sinceGood !== null) {
            $this->goodTime = ($this->goodTime + ($now - $this->sinceGood));
            $this->sinceGood = null;
        }

        $success = ($this->statsIterations - $this->statsFailures);
        $failPercent = round(($this->statsFailures / $this->statsIterations * 100.0));
        $totalTime = ($now - $this->totalTime);
        $totalTimeS = round($totalTime, 4);
        $goodTimeS = round($this->goodTime, 4);
        $badTimeS = round($this->badTime, 4);
        if ($totalTime > 0.0) {
            $badTimeP = round(($this->badTime / $totalTime * 100));
        } else {
            $badTimeP = 0.0;
        }

        $output->writeln([
                          '--- '.$input->getOption('host').':'.$input->getOption('port')
                          .' database ping statistics ---',
                          $this->statsIterations.' tries, '
                                .$success.' successes, '
                                .$this->statsFailures.' failures, '
                                .$failPercent.'% fail tries',
                          $totalTimeS.'s time, '
                         .$goodTimeS.'s success, '
                         .$badTimeS.'s fail, '
                         .$badTimeP.'% fail time',
                         ]);
    }//end stats()

    /**
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return bool Success.
     * @since 2016-08-04
     */
    protected function queryCheck(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('check passed');
        return true;
    }//end queryCheck()

    /**
     * @return float elapsed time
     * @since 2016-08-04
     */
    protected function execTime()
    {
        $time = round(($this->stopTime - $this->startTime) * 100.0);
        return $time;
    }//end execTime()


    /**
     * @param string          $msg    Message to display.
     * @param InputInterface  $input  Input from the user.
     * @param OutputInterface $output Output to the user.
     * @return void
     * @since 2016-08-04
     */
    protected function writeReply($msg, InputInterface $input, OutputInterface $output)
    {
        if ($this->sinceGood !== null) {
            $sinceGood = round((microtime(true) - $this->sinceGood), 4);
        } else {
            $sinceGood = 0.0;
        }

        if ($this->sinceBad !== null) {
            $sinceBad = round((microtime(true) - $this->sinceBad), 4);
        } else {
            $sinceBad = 0.0;
        }
        $msg = 'from '.$input->getOption('host').':'.$input->getOption('port').': '
            .$msg
            .' delay='.$input->getOption('delay').'ms,'
            .' exec='.$this->execTime().'ms,'
            .' since success='.$sinceGood.'s,'
            .' since fail='.$sinceBad.'s';
        $output->writeln($msg);
    }//end writeReply()
}//end class
