<?php

namespace LaravelSanity\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;
use LaravelSanity\Config\Checker;

class SanityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity {target : The target environment config should be checked against.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check config values for a given target.';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \LaravelSanity\Config\Checker
     */
    private $checker;
    /**
     * SanityCommand constructor.
     *
     * @param \LaravelSanity\Config\Checker               $checker
     * @param \Illuminate\Contracts\Config\Repository     $config
     */
    public function __construct(Checker $checker, Repository $config)
    {
        parent::__construct();

        $this->config = $config;
        $this->checker = $checker;
    }

    /**
     * Execute the console command.
     *
     * @return number
     */
    public function handle()
    {
        $config = $this->config->all();
        if (! isset($config['checks'])) {
            $config = array_merge([
                'checks' => require(__DIR__ . '/../../../resources/config/checks.php'),
            ], $config);
        }

        if ($this->checker->check($config, $this->argument('target'))) {
            return 0;
        }

        foreach ($this->checker->getErrors() as $error) {
            $this->error($error);
        }

        return count($this->checker->getErrors());
    }
}
