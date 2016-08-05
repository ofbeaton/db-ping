# db-ping
db-ping verifies a database server is responding by executing a query in a timed loop.

[![Latest Stable Version](https://img.shields.io/packagist/v/ofbeaton/db-ping.svg?style=flat-square)](https://packagist.org/packages/ofbeaton/db-ping)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/ofbeaton/db-ping/master.svg?style=flat-square)](https://travis-ci.org/ofbeaton/db-ping)

Optionally includes slave replication checks.

PHP 5.6+ console command that uses PDO to provide the database drivers:
- [x] MySQL

## Installing via phar

Before proceeding, you need a working PHP 5.6+ installation.

The recommended way to install db-ping is by downloading the phar. 

See [Releases](https://github.com/ofbeaton/db-ping/releases) for downloads.

Next, run the phar from the command line:


```bash
php db-ping.phar help

php db-ping.phar mysql --pass=mysecretpassword
```

## Support Me

Hi, I'm Finlay Beaton ([@ofbeaton](https://github.com/ofbeaton)). This software is made possible by donations of fellow users like you, encouraging me to toil the midnight hours away and sweat into the code and documentation. 

Everyone's time should be valuable, please consider donating.

[Donate through Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=RDWQCGL5UD6DS&lc=CA&item_name=ofbeaton&item_number=dbping&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted)

## License

This software is distributed under the MIT License. Please see [License file](LICENSE) for more information.