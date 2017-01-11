<?php

namespace Ofbeaton\DbPing;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * @since 2016-08-04
 */
class Application
{

    /**
     * @var SymfonyApplication
     * @since 2016-08-04
     */
    private $app;


    /**
     * @since 2016-08-04
     */
    public function __construct()
    {
        $this->app = new SymfonyApplication();

        $driversCmd = new Command\DriversCommand();
        $this->app->add($driversCmd);

        if (extension_loaded('pdo_mysql') === true) {
            $mysqlCmd = new Command\MysqlCommand();
            $this->app->add($mysqlCmd);
        }

        if (extension_loaded('pdo_odbc') === true) {
            $odbcCmd = new Command\OdbcCommand();
            $this->app->add($odbcCmd);
        }
    }//end __construct()


    /**
     * @since 2016-08-04
     * @return void
     */
    public static function main()
    {
        $app = new Application();
        $app->run();
    }//end main()


    /**
     * @since 2016-08-04
     * @return void
     */
    public function run()
    {
        $this->app->run();
    }//end run()
}//end class
