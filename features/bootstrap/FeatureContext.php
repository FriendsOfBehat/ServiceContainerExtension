<?php

/*
 * This file is part of the ServiceContainerExtension package.
 *
 * (c) FriendsOfBehat
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class FeatureContext implements Context
{
    /**
     * @var string
     */
    private static $workingDir;

    /**
     * @var Filesystem
     */
    private static $filesystem;

    /**
     * @var string
     */
    private static $phpBin;

    /**
     * @var Process
     */
    private $process;

    /**
     * @BeforeFeature
     */
    public function beforeFeature()
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
        self::$phpBin = self::findPhpBinary();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        self::$filesystem->mkdir(self::$workingDir, 0777);
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        self::$filesystem->remove(self::$workingDir);
    }

    /**
     * @Given a Behat configuration containing:
     */
    public function thereIsConfiguration(PyStringNode $content)
    {
        self::$filesystem->dumpFile(self::$workingDir . '/behat.yml', $content->getRaw());
    }

    /**
     * @Given a|an (context|feature) file :file containing:
     */
    public function thereIsFile($file, PyStringNode $content)
    {
        self::$filesystem->dumpFile(self::$workingDir . '/' . $file, $content->getRaw());
    }

    /**
     * @When I run Behat
     */
    public function iRunBehat()
    {
        $this->process = new Process(sprintf('%s %s', self::$phpBin, escapeshellarg(BEHAT_BIN_PATH)));
        $this->process->setWorkingDirectory(self::$workingDir);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then it should pass
     */
    public function itShouldPass()
    {
        if (0 === $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to pass, but failed with the following output:' . PHP_EOL . PHP_EOL . $this->getProcessOutput()
        );
    }

    /**
     * @Then it should pass with:
     */
    public function itShouldPassWith(PyStringNode $expectedOutput)
    {
        $this->itShouldPass();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @Then it should fail
     */
    public function itShouldFail()
    {
        if (0 !== $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to fail, but passed with the following output:' . PHP_EOL . PHP_EOL . $this->getProcessOutput()
        );
    }

    /**
     * @Then it should fail with:
     */
    public function itShouldFailWith(PyStringNode $expectedOutput)
    {
        $this->itShouldFail();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @param string $expectedOutput
     */
    private function assertOutputMatches($expectedOutput)
    {
        $pattern = '/' . preg_quote($expectedOutput, '/') . '/sm';
        $output = $this->getProcessOutput();

        $result = preg_match($pattern, $output);
        if (false === $result) {
            throw new \InvalidArgumentException('Invalid pattern given:' . $pattern);
        }

        if (0 === $result) {
            throw new \DomainException(sprintf(
                'Pattern "%s" does not match the following output:' . PHP_EOL . PHP_EOL . '%s',
                $pattern,
                $output
            ));
        }
    }

    /**
     * @return string
     */
    private function getProcessOutput()
    {
        $this->assertProcessIsAvailable();

        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    /**
     * @return int
     */
    private function getProcessExitCode()
    {
        $this->assertProcessIsAvailable();

        return $this->process->getExitCode();
    }

    /**
     * @throws \BadMethodCallException
     */
    private function assertProcessIsAvailable()
    {
        if (null === $this->process) {
            throw new \BadMethodCallException('Behat proccess cannot be found. Did you run it before making assertions?');
        }
    }

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    private static function findPhpBinary()
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        if (false === $phpBinary) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $phpBinary;
    }
}
