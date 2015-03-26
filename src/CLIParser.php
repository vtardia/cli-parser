<?php
/**
 * Command Line Parser Library
 *
 * Parses Command Line Arguments and returns options and args
 *
 * Emulates Linux's getopt_long() function. Some sample options are:
 * <code>
 * $shortOptions = 'vho:a';
 *
 * $longOptions = array(
 *         array('id', true),
 *         array('name', true),
 *         array('verbose', true, 'v'),
 *         array('output', true, 'o'),
 * );
 * </code>
 *
 * CLI Parser uses some code written by Patrick Fisher (see links)
 *
 * @package   CLIParser
 * @author    Vito Tardia <vito@tardia.me>
 * @copyright 2011-2015 Vito Tardia <http://vito.tardia.me>
 * @license   http://opensource.org/licenses/MIT MIT
 * @version   1.0.0
 * @see       http://www.php.net/manual/en/features.commandline.php
 * @see       https://github.com/pwfisher/CommandLine.php
 */

namespace CLIParser;

/**
 * Main Parser
 *
 * @package  CLIParser
 * @author   Vito Tardia <vito@tardia.me>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  1.0.0
 * @link     http://github.com/vtardia/cli-parser
 */
class CLIParser
{
    
    /**
     * Current program name
     * @var string | NULL
     */
    private $program = null;
    

    /**
     * Program arguments (eg. filenames)
     * @var array
     */
    private $arguments = array();


    /**
     * Program options (eg. -c -v --name)
     * @var array
     */
    private $options = array();
    

    /**
     * List of supported short options (-v, -f, ecc)
     * @var string
     */
    private $shortOptions = '';


    /**
     * List of supported long options (eg. --name, --verbose, ecc)
     * @var array
     */
    private $longOptions = array();


    /**
     * Current option being examined
     * @var int
     */
    private $optind = 1;
    

    /**
     * Argument value for the current option
     * @var string | NULL
     */
    private $optarg = null;
    

    /**
     * Copy of global $argv array
     * @var array
     */
    private $argv = array();


    /**
     * Copy of global $argc
     * @var int
     */
    private $argc = 0;

    /**
     * Constructor
     *
     * Sets program's name and copy command line arguments for internal use
     *
     * @param string $shortOptions String containing the short option list (eg cvf:)
     * @param array  $longOptions  Array containing the short option list
     *
     * @return void
     */
    public function __construct($shortOptions = '', array $longOptions = array())
    {
        global $argv;
        global $argc;
        
        $this->argv = $argv;
        $this->argc = $argc;
        
        $this->program = $this->argv[0];
        
        $this->setEnv($shortOptions, $longOptions);
    }

    /**
     * Sets the working environment for the program
     *
     * Creates local copies of global $argv and $argc and allows to rewrite the $argv array
     *
     * @param string $shortOptions A string of allowed single-char options.
     *                             Parametrized options are followed by the ':' character
     *
     * @param array  $longOptions  An array of allowed long options which contains:
     *                             name (string), parameter need (boolean TRUE/FALSE) and
     *                             optional short-option equivalent (char)
     * @param array  $argv         An array of arguments formatted as the real $argv
     *
     * @return void
     */
    public function setEnv($shortOptions = '', array $longOptions = array(), array $argv = array())
    {
        
        if (!empty($argv)) {
            $this->argv = $argv;
            $this->argc = count($argv);
        }
        
        $this->shortOptions = $shortOptions;
        $this->longOptions  = $longOptions;
        
        $this->program = $this->argv[0];
        
    }


    /**
     * Return all the options in an associative array where the key is the option's name
     *
     * For options that act like switches (eg. -v) the array value is TRUE
     * ($options['v'] => true)
     *
     * For options that require a parameter (eg. -o filename) the array value is the
     * parameter value ($options['o'] => "filename")
     *
     * @param int $start Initial index to start with, in order to allow the syntax
     *                   'program command [options] [arguments]'
     *
     * @return array
     */
    public function options($start = 1)
    {
        
        // Init index
        $this->optind = (0 == ($start)) ? 1 : (int) $start;
        
        // Loop the arguments until there is no option (-1)
        // At the end of the loop $this->optind points to the first non-option
        // parameter
        do {
            $nextOption = $this->getopt();
            
            // If the option is an option (!== -1) and is valid (not null)
            // set it's value and put it in the options array to return
            if (null !== $nextOption && -1 !== $nextOption) {
                $this->options[$nextOption] = (null !== $this->optarg) ? $this->optarg : true;
            }
            
        } while ($nextOption !== -1);
        
        return $this->options;
    }


    /**
     * Returns program's arguments
     *
     * An argument is everything that is not an option, for example a file path
     *
     * @return array
     */
    public function arguments()
    {
        
        if ($this->optind < $this->argc) {
            for ($i = $this->optind; $i < $this->argc; $i++) {
                $this->arguments[] = $this->argv[$i];
            }
            
        }
        
        return $this->arguments;
    }


    /**
     * Returns the program name
     *
     * @return string
     */
    public function program()
    {
        return $this->program;
    }


    /**
     * Searches for a valid next option
     *
     * If the option is not valid returns NULL, if there is no option returns -1
     *
     * @return mixed | NULL
     */
    private function getopt()
    {

        // Reset option argument
        $this->optarg = null;
        
        // Check for index validity
        if ($this->optind >= $this->argc) {
            return -1;
        }

        // Get a copy of the current option to examine
        $arg = $this->argv[$this->optind];

        if ($this->isLongOption($arg)) {
            return $this->parseLongOption($arg);
        }

        if ($this->isShortOption($arg)) {
            return $this->parseShortOption($arg);
        }

        // If it's not an option, it's an argument,
        // so we stop parsing at the first non-option string
        // and $this->optind points to the first argument

        // Default: no options found
        return -1;
    }


    /** Private utilities below **/


    /**
     * Checks if the value is a long options
     *
     * Long options start with '--'
     *
     * @param string $value The value to check
     * @return boolean
     */
    private function isLongOption($value)
    {
        return (substr($value, 0, 2) === '--');
    }


    /**
     * Checks if the value is a short options
     *
     * Long options start with '-'
     *
     * @param string $value The value to check
     * @return boolean
     */
    private function isShortOption($value)
    {
        return (substr($value, 0, 1) === '-');
    }


    /**
     * Extracts the key from a long option
     *
     * Two syntax are allowed: '--key=value' and '--key value'
     *
     * @param string $arg The argument to parse
     * @return string
     */
    private function getLongOptionKey($arg)
    {

        $key = (false !== ($eqPos = strpos($arg, '='))) ?
            substr($arg, 2, $eqPos - 2) : // --key=value
            substr($arg, 2); // --key value

        return $key;
    }


    /**
     * Parse and extract the key/value of a long option
     *
     * @param string $arg The argument to parse
     * @return string | NULL
     */
    private function parseLongOption($arg)
    {

        $longOptions  = $this->longOptions;

        // If our program does not accept long options
        // ignore current keyword and increment index
        if (empty($longOptions)) {
            $this->optind++;
            return null;
        }

        $key = $this->getLongOptionKey($arg);

        // Init return value to NULL (invalid option)
        $option = null;

        // Search if the option is in the list of valid long options
        foreach ($longOptions as $opt) {
            // Transform in array, this allow string-only declarations in options array
            if (!is_array($opt)) {
                $opt = array($opt);
            }

            if ((string)$opt[0] === $key) {
                // Match found, set return option name
                $option = $key;
                
                // If 1-char equiv is present, it overrides long name
                if (isset($opt[2]) && strlen((string)$opt[2]) == 1) {
                    $option = $opt[2];
                }

                // If option should have a parameter
                if (isset($opt[1]) && true === $opt[1]) {
                    if (false !== ($eqPos = strpos($arg, '='))) {
                        // Parsing equal format (--key=value): parameter value is the string after '='
                        $this->optarg = substr($arg, $eqPos + 1);
                        
                        // Index is updated by 1 step only
                        $this->optind++;
                        return $option;

                    }

                    // Parameter should be in the command line in the next position
                    // Test if arg is a real arg or another option (contains - or --)
                    if ($this->optind < $this->argc -1) {
                        $optarg = $this->argv[$this->optind + 1];
                        if (!($this->isLongOption($optarg)) && !($this->isShortOption($optarg))) {
                            // Option value is ok, set it and update index by 2 steps
                            $this->optarg = $optarg;
                            $this->optind += 2;
                            return $option;
                        }

                    }

                    // Option value missing, set it to FALSE and update index by 1 step only
                    $this->optarg = false;
                    $this->optind++;
                    return $option;
                }

                $this->optarg = true;
                $this->optind++;
                return $option;
            }
        }

         // At the end of the loop $option can be NULL or a string value
        return $option;
    }


    /**
     * Parse and extract the keys and values of a short options group
     *
     * Only the last option in the group can have a value
     *
     * @param string $key The options group string
     * @return string | NULL
     */
    private function parseShortOptionGroup($key)
    {
        $shortOptions = $this->shortOptions;

        // We parse every single character, remove it from the current argument
        // and wd DO NOT update index counter, unless we are parsing the last option
        $chars = str_split($key);

        foreach ($chars as $char) {
            // Test if is a valid option
            $cpos = strpos($shortOptions, $char);

            if (false === $cpos) {
                $this->optind++;
                return null;
            }

            // Is Valid option: set return value
            $option = $char;

            // Check if option accept a parameter
            if (isset($shortOptions[$cpos+1]) && $shortOptions[$cpos+1] === ':') {
                // Ok, current option accept a parameter, start parsing
                
                // Check n.1: if an option accepts a parameter, to be valid must be the last
                // in an option-group, for example:
                // YES: tar -cvzf filename.tar.gz
                // NO: tar -cvfz filename.tar.gz
                
                if (strpos($key, $char) < (strlen($key)-1)) {
                    // Current option is not the last, so the parameter is invalid
                    // return option with FALSE as argument
                    $this->optarg = false;

                    // Remove current option from $this->argv[$this->optind]
                    // so it's not counted on next loop
                    $this->argv[$this->optind] = str_replace($char, '', $this->argv[$this->optind]);
                    return $option;
                    
                }

                // Our option is the last, so check for a valid parameter
                
                if ($this->optind < $this->argc -1) {
                    // Get next argument from our copy of $argv
                    $optarg = (string) $this->argv[$this->optind + 1];

                    // If the argument is not an option (should not begin with - or --)
                    if (!($this->isLongOption($optarg)) && !($this->isShortOption($optarg))) {
                        // Option value is ok, set it and update index by 2 steps
                        $this->optarg = $optarg;
                        $this->optind += 2;
                        return $option;
                    }

                }

                // Option value missing, set it to FALSE and update index by 1 step only
                $this->optarg = false;
                $this->optind++;
                return $option;

            }

            // Current option do not accept parameters
            $this->optarg = true;
            
            // Remove current option from $this->argv[$this->optind]
            // so it's not counted on next loop
            $this->argv[$this->optind] = str_replace($char, '', $this->argv[$this->optind]);
            
            // Return the option value
            // Option index is updated (by 1) only if this is the last option
            if (strpos($key, $char) == (strlen($key)-1)) {
                $this->optind++;
            }
            return $option;
        }

        // If we reach here the chance is we didn't find any allowed option
        // so we return null
        if (strpos($key, $char) == (strlen($key)-1)) {
            $this->optind++;
        }
        return null;
    }

    /**
     * Parse and extract the key/value of a short option
     *
     * @param string $arg The argument to parse
     * @return string | NULL
     */
    private function parseShortOption($arg)
    {
        $shortOptions = $this->shortOptions;

        // If our program does not accept short options
        // ignore current keyword
        if (empty($shortOptions)) {
            $this->optind++;
            return null;
        }

        // We do not accept single letter options with '=' (format -o=value)
        if (is_numeric(strpos($arg, '='))) {
            $this->optind++;
            return null;
        }

        // We must accept grouped option (eg. -cvd)
        $key = substr($arg, 1);

        if (strlen($key) > 1) {
            // We are parsing an option group
            return $this->parseShortOptionGroup($key);
        }

        // Deal with a single-char option, no option group
        if (strlen($key) == 1) {
            // Check if option is allowed
            $cpos = strpos($shortOptions, $key);

            // Invalid option, go to next
            if (false === $cpos) {
                $this->optind++;
                return null;
            }

            // Option is supported, go ahead
            $option = $key;

            // Check if option allows parameters
            if (isset($shortOptions[$cpos+1]) && $shortOptions[$cpos+1] === ':') {
                // Ok, parse parameter
                
                if ($this->optind < $this->argc -1) {
                    // Get next arg from our copy of $argv
                    $optarg = $this->argv[$this->optind + 1];

                    // If arg is not an option (should not begin with - or --)
                    if (!(substr($optarg, 0, 2) === '--') && !(substr($optarg, 0, 1) === '-')) {
                        // Option value is ok, set it and update index by 2 steps
                        $this->optarg = $optarg;
                        $this->optind += 2;
                        return $option;
                    }

                }

                // Option value missing, set it to FALSE and update index by 1 step only
                $this->optarg = false;
                $this->optind++;
                return $option;

            }

            // No parameter
            // In any case we now have a supported option
            $this->optarg = true;
            $this->optind++;
            return $option;

        }

        // Invalid option, go to next
        $this->optind++;
        return null;
    }
}
