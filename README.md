# db-ping
db-ping verifies a database server is responding by executing a query in a timed loop. Optionally includes slave replication checks.

[![Latest Stable Version](https://img.shields.io/packagist/v/ofbeaton/db-ping.svg?style=flat-square)](https://packagist.org/packages/ofbeaton/db-ping)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/ofbeaton/db-ping/master.svg?style=flat-square)](https://travis-ci.org/ofbeaton/db-ping)

PHP 5.4+ console command that uses PDO to provide the database drivers:
- [ ] CUBRID
- [ ] Firebird
- [ ] IBM
- [ ] Informix
- [x] MySQL
- [ ] MS SQL Server (PDO_DBLIB)
- [ ] MS SQL Server (PDO_SQLSRV)
- [ ] ODBC and DB2
- [ ] Oracle
- [ ] PostgreSQL
- SQLite - _Unplanned_
- [ ] 4D

## Installing via Composer

The recommended way to install db-ping is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer.phar require ofbeaton/db-ping
```

After installing, you can now use it from the command line:

```bash
vendor/bin/db-ping help

vendor/bin/db-ping mysql --pass=mysecretpassword
```

## Support Me

Hi, I'm Finlay Beaton ([@ofbeaton](https://github.com/ofbeaton)). This software is made possible by donations of fellow users like you, encouraging me to toil the midnight hours away and sweat into the code and documentation. 

Everyone's time should be valuable, please consider donating.

[Donate through Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=RDWQCGL5UD6DS&lc=CA&item_name=ofbeaton&item_number=dbping&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted)

## License

This software is distributed under the MIT License. Please see [License file](LICENSE) for more information.