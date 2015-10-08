<?php
namespace ShopwareCli\Services;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ProcessExecutor
{
    /**
     * There is no need to set a limit here, because builds would fail based on networking issues
     * instead of application issues.
     */
    const DEFAULT_TIMEOUT = 0;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param  string            $commandline
     * @param  bool              $allowFailure
     * @param  string            $cwd
     * @throws \RuntimeException
     * @return int|null
     */
    public function execute($commandline, $cwd = null, $allowFailure = false)
    {
        $process = new Process($commandline, $cwd);
        $process->setTimeout(self::DEFAULT_TIMEOUT);

        $output = $this->output; // tmp var needed for php < 5.4
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (!$allowFailure && !$process->isSuccessful()) {
            throw new \RuntimeException("Command failed. Error Output:\n\n" . $process->getErrorOutput(), $process->getExitCode());
        }

        return $process->getExitCode();
    }
}
