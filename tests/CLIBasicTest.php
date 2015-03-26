<?php
/**
 * Basic tests CLIParser
 *
 * We are testing constructors and a common list of usages
 *
 */

namespace CLIParser;

class CLIBasicTest extends \PHPUnit_Framework_TestCase
{
    
    protected $cli = null;
    
    public function setUp()
    {
        $this->cli = new CLIParser();
    }
    
    public function tearDown()
    {
        unset($this->cli);
    }
    
    
    /**
     * Tests an empty constructor
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('CLIParser\CLIParser', $this->cli);
    }

    /**
     * test that program name on empty object must be phpunit
     */
    public function testProgramNameRun()
    {
        $program = $this->cli->program();
        $this->assertInternalType('string', $program);
        $this->assertEquals('phpunit', basename($program));
    }
    
    /**
     * Test a forced environment
     */
    public function testSetEnv()
    {
        
        $shortOptions = 'vo:';
        $longOptions = array(
            array('verbose', false, 'v'),
            array('output', true, 'o'),
        );
        
        /*
		_SERVER["argv"] => Array
		(
		    [0] => ../options.php
		    [1] => -v
		    [2] => -o
		    [3] => test
		)

		_SERVER["argc"] => 4
		*/
        
        // Test Program name with empty args
        $argv = array('myself');
        $this->cli->setEnv('', array(), $argv);
        $this->assertEquals('myself', basename($this->cli->program()));

        // Test basic args
        $argv = array('myself', '-v', '-o', 'test.txt', 'test');
        $this->cli->setEnv($shortOptions, $longOptions, $argv);
        
        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));
        
        // Test Options
        $options = $this->cli->options();
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('v', $options);
        $this->assertTrue($options['v']);
        $this->assertArrayHasKey('o', $options);
        $this->assertEquals($options['o'], 'test.txt');
        
        // // Test arguments
        $arguments = $this->cli->arguments();
        $this->assertInternalType('array', $arguments);
        $this->assertEquals($arguments[0], 'test');

    }
    
    /**
     * Tests: myself --foo --bar=baz
     */
    public function testOne()
    {
        
        $longOptions = array(
        array('foo', false),
        array('bar', true),
        );

        $argv = array('myself', '--foo', '--bar', 'baz');
        $this->cli->setEnv('', $longOptions, $argv);
    
        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));
        
        $options = $this->cli->options();
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('foo', $options);
        $this->assertTrue($options['foo']);

        $this->assertArrayHasKey('bar', $options);
        $this->assertEquals($options['bar'], 'baz');

    }
    
    
    /**
     * Tests: myself -abc
     */
    public function testTwo()
    {
        
        $shortOptions = 'abc';
        $argv = array('myself', '-abc');
        $this->cli->setEnv($shortOptions, array(), $argv);

        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));

        $options = $this->cli->options();
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('a', $options);
        $this->assertArrayHasKey('b', $options);
        $this->assertArrayHasKey('c', $options);
        $this->assertTrue($options['a']);
        $this->assertTrue($options['b']);
        $this->assertTrue($options['c']);

    }
    
    /**
     * Tests: myself arg1 arg2 arg3
     */
    public function testThree()
    {
        
        $argv = array('myself', 'arg1', 'arg2', 'arg3');
        $this->cli->setEnv('', array(), $argv);

        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));

        $options = $this->cli->options();
        $this->assertInternalType('array', $options);
        $this->assertEquals(0, count($options));

        // Test arguments
        $arguments = $this->cli->arguments();
        $this->assertInternalType('array', $arguments);
        $this->assertEquals(3, count($arguments));
        $this->assertEquals($arguments[0], 'arg1');
        $this->assertEquals($arguments[1], 'arg2');
        $this->assertEquals($arguments[2], 'arg3');

    }

    /**
     * Tests: myself -abc -v -o filename --invalidValue
     */
    public function testFour()
    {
        
        $shortOptions = 'vo:';
        $longOptions = array(
            array('authinfo', true),
            array('name', true),
            array('verbose', false, 'v'),
            array('output', true, 'o'),
        );

        // Test basic args
        $argv = array('myself', '-abc', '-v', '-o', 'test.txt', '--authinfo');
        $this->cli->setEnv($shortOptions, $longOptions, $argv);
    
        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));

        $options = $this->cli->options();
        $this->assertInternalType('array', $options);
        $this->assertEquals(3, count($options));

        $this->assertArrayHasKey('v', $options);
        $this->assertTrue($options['v']);
        $this->assertArrayHasKey('o', $options);
        $this->assertEquals($options['o'], 'test.txt');

        // Test arguments
        $arguments = $this->cli->arguments();
        $this->assertInternalType('array', $arguments);
        $this->assertEquals(0, count($arguments));

    }
    
    
    /**
     * Tests for: myself commandName -a -b -o somefile
     */
    public function testFive()
    {
        
        $shortOptions = 'vo:';
        $longOptions = array(
            array('authinfo', true),
            array('name', true),
            array('verbose', false, 'v'),
            array('output', true, 'o'),
        );

        // Test basic args
        $argv = array('myself', 'doSomething', '-abc', '-v', '-o', 'test.txt', 'argumentName');
        $this->cli->setEnv($shortOptions, $longOptions, $argv);
    
        // Test Program name
        $this->assertEquals('myself', basename($this->cli->program()));
        
        // Test Command name
        $this->assertEquals('doSomething', $argv[1]);

        $options = $this->cli->options(2);
        $this->assertInternalType('array', $options);
        $this->assertEquals(2, count($options));

        $this->assertArrayHasKey('v', $options);
        $this->assertTrue($options['v']);
        $this->assertArrayHasKey('o', $options);
        $this->assertEquals($options['o'], 'test.txt');

        // Test arguments
        $arguments = $this->cli->arguments();
        $this->assertInternalType('array', $arguments);
        $this->assertEquals(1, count($arguments));

    }
}
