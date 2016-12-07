<?php

use LaravelSanity\Config\Checker;
use PHPUnit\Framework\TestCase;

class CheckerTest extends TestCase
{
    /**
     * @var \LaravelSanity\Config\Checker $checker
     */
    private $checker;

    public function setUp()
    {
        parent::setUp();
        $this->checker = new Checker();
    }

    /** @test */
    public function it_throws_an_error_on_incorrect_target()
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Unknown target environment');

        $this->checker->check(['checks' => ['bar' => []]], 'foo');
    }

    /** @test */
    public function it_returns_false_when_a_value_is_unexpected()
    {
        $config = [
            'app'    => ['env' => 'production'],
            'checks' => [
                'foo' => ['app.env' => 'local'],
            ],
        ];

        static::assertFalse($this->checker->check($config, 'foo'));
    }

    /** @test */
    public function it_checks_correctly_the_rule_not()
    {
        $config = [
            'app'    => ['env' => 'production'],
            'checks' => [
                'foo' => ['app.env' => '__not__production'],
            ],
        ];
        static::assertFalse($this->checker->check($config, 'foo'));

        $config = [
            'app'    => ['env' => 'production'],
            'checks' => [
                'foo' => ['app.env' => '__not__local'],
            ],
        ];
        static::assertTrue($this->checker->check($config, 'foo'));

        $config = [
            'app'    => ['env' => 'local'],
            'checks' => [
                'foo' => ['app.env' => '__not____regex__/duc/'],
            ],
        ];
        static::assertTrue($this->checker->check($config, 'foo'));
    }

    /** @test */
    public function it_checks_correctly_the_rule_regex()
    {
        $config = [
            'app'    => ['env' => 'production'],
            'checks' => [
                'foo' => ['app.env' => '__regex__/od.{3}ti.+$/'],
            ],
        ];
        static::assertFalse($this->checker->check($config, 'foo'));

        $config = [
            'app'    => ['env' => 'production'],
            'checks' => [
                'foo' => ['app.env' => '__regex__/od.{2}ti.+$/'],
            ],
        ];
        static::assertTrue($this->checker->check($config, 'foo'));
    }

    /** @test */
    public function it_gives_good_explanation_about_what_is_wrong_in_the_configuration()
    {
        $config = [
            'app'    => ['env' => 'production', 'url' => 'localhost'],
            'checks' => [
                'foo' => ['app.env' => 'local', 'app.url' => '__not____regex__/localhost/'],
            ],
        ];

        $this->checker->check($config, 'foo');
        $errors = $this->checker->getErrors();

        static::assertContains('[app.env] expected to be "local", "production" found.', $errors);
        static::assertContains('[app.url] expected to NOT match "/localhost/", "localhost" found.', $errors);
    }
}
