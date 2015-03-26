CLIParser
=========

CLIParser is a PHP utility that parses the command line of a PHP script detecting short and long options, switches and arguments.

The output is similar to Linux's `getopt_long()` native function in both behavior and configuration, both long and short options can be validated, for example:

```php
$shortOptions = 'vho:a';

$longOptions = array(
    array('id', TRUE),
    array('name', TRUE),
    array('verbose', FALSE, 'v'),
    array('output', TRUE, 'o'),
);
```
The above code defines

 - the long option `--verbose` with short equivalent `-v` and no required parameter
 - the long option `--output`, with short equivalent `-o` and a required parameter
 - the long options `--id` and `--name` with required parameters and no short equivalent
 - the short options `-h` and `-a` with no long equivalent and no required parameter

With CLIParser you can build scripts like:

```shell
$ myscript -abc -v -o somefile.log --name=SomeName --other-option OtherValue Argument1 Argument2...ArgumentN
```

In addition, you can parse the arguments starting from an arbitrary position, so you can have:

```shell
$ myscript SomeCommand [OPTIONS] Argument1...ArgumentN
```


## Usage

```php
// require CLIParser.php library file or use an autoloader (eg. Composer)

use CLIParser\CliParser;

// Define some options
$shortOptions = 'vo:';
$longOptions = array(
    array('verbose', false, 'v'),
    array('output', true, 'o'),
);

// Create a parser...
$cli = new CLIParser($shortOptions, $longOptions);

// ... and use it!

// Executable name or path
$program = $cli->program();

// Array of valid options with values
$options = $cli->options();

// Array of arguments
$arguments = $cli->arguments();
```


## Installation

```shell
$ composer require vtardia/cli-parser
```

## Requirements

PHP 5.3 or better, PHPUnit in order to run tests.


## Contributing

See CONTRIBUTING file.


## Running the Tests

```shell
$ composer test
```


## Credits

CLI Parser uses some code written by [Patrick Fisher](https://github.com/pwfisher/CommandLine.php).


## Contributor Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](http://contributor-covenant.org/). By participating in this project you agree to abide by its terms. See CODE_OF_CONDUCT file.

## License

CLIParser is released under the MIT License. See the bundled LICENSE file for details.
