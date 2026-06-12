<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

final class TestContext implements Context
{
    private static string $workingDir;

    private static Filesystem $filesystem;

    private static string $phpBin;

    private ?Process $process = null;

    private array $mergedConfig = [];

    #[\Behat\Hook\BeforeFeature]
    public static function beforeFeature(): void
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
        self::$phpBin = self::findPhpBinary();
    }

    #[\Behat\Hook\BeforeScenario]
    public function beforeScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
        self::$filesystem->mkdir(self::$workingDir, 0777);
        $this->mergedConfig = [];
    }

    #[\Behat\Hook\AfterScenario]
    public function afterScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
    }

    #[\Behat\Step\Given('/^a Behat configuration containing(?: "([^"]+)"|:)$/')]
    public function thereIsConfiguration(?string $content): void
    {
        $parsed = Yaml::parse((string) $content);
        $parsed = $this->resolveExtensionClassNames($parsed);
        $this->mergedConfig = array_replace_recursive($this->mergedConfig, $parsed);

        self::$filesystem->dumpFile(
            sprintf('%s/behat.dist.php', self::$workingDir),
            sprintf(
                "<?php\nreturn new \\Tests\\Behat\\Config\\ArrayConfig(%s);\n",
                var_export($this->mergedConfig, true),
            ),
        );
    }

    #[\Behat\Step\Given('/^a (?:.+ |)file "([^"]+)" containing(?: "([^"]+)"|:)$/')]
    public function thereIsFile(?string $file, ?string $content): void
    {
        $content = (string) $content;

        if (str_ends_with((string) $file, '.php')) {
            $content = $this->replaceAnnotationsWithAttributes($content);
        }

        self::$filesystem->dumpFile(self::$workingDir . '/' . $file, $content);
    }

    #[\Behat\Step\Given('/^a feature file containing(?: "([^"]+)"|:)$/')]
    public function thereIsFeatureFile(?string $content): void
    {
        $this->thereIsFile(sprintf('features/%s.feature', md5(uniqid('', true))), $content);
    }

    #[\Behat\Step\When('/^I run Behat$/')]
    public function iRunBehat(): void
    {
        $this->process = new Process([self::$phpBin, BEHAT_BIN_PATH, '--strict', '-vvv', '--no-interaction', '--lang=en'], self::$workingDir);
        $this->process->start();
        $this->process->wait();
    }

    #[\Behat\Step\Then('/^it should pass$/')]
    public function itShouldPass(): void
    {
        if (0 === $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to pass, but failed with the following output:' . \PHP_EOL . \PHP_EOL . $this->getProcessOutput(),
        );
    }

    #[\Behat\Step\Then('/^it should fail$/')]
    public function itShouldFail(): void
    {
        if (0 !== $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to fail, but passed with the following output:' . \PHP_EOL . \PHP_EOL . $this->getProcessOutput(),
        );
    }

    private function assertOutputMatches(string $expectedOutput): void
    {
        $pattern = '/' . preg_quote($expectedOutput, '/') . '/sm';
        $output = $this->getProcessOutput();

        $result = preg_match($pattern, $output);
        if (false === $result) {
            throw new \InvalidArgumentException('Invalid pattern given:' . $pattern);
        }

        if (0 === $result) {
            throw new \DomainException(sprintf(
                'Pattern "%s" does not match the following output:' . \PHP_EOL . \PHP_EOL . '%s',
                $pattern,
                $output,
            ));
        }
    }

    private function getProcessOutput(): string
    {
        $this->assertProcessIsAvailable();

        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    private function getProcessExitCode(): int
    {
        $this->assertProcessIsAvailable();

        return $this->process->getExitCode();
    }

    private function assertProcessIsAvailable(): void
    {
        if (null === $this->process) {
            throw new \BadMethodCallException('Behat process cannot be found. Did you run it before making assertions?');
        }
    }

    private function replaceAnnotationsWithAttributes(string $code): string
    {
        return (string) preg_replace_callback(
            '/^( *)\/\*\*\s*@(Given|When|Then|BeforeScenario|AfterScenario|BeforeFeature|AfterFeature)(?:\s+(.+?))?\s*\*\/$/m',
            static function (array $m): string {
                $indent = $m[1];
                $name = $m[2];
                $arg = isset($m[3]) && $m[3] !== '' ? "('" . str_replace("'", "\\'", $m[3]) . "')" : '';
                $ns = in_array($name, ['Given', 'When', 'Then'], true) ? 'Step' : 'Hook';

                return "{$indent}#[\\Behat\\{$ns}\\{$name}{$arg}]";
            },
            $code,
        );
    }

    /**
     * Resolve short extension names (e.g. FriendsOfBehat\ServiceContainerExtension)
     * to their fully qualified class names (e.g. FriendsOfBehat\ServiceContainerExtension\ServiceContainer\ServiceContainerExtension).
     */
    private function resolveExtensionClassNames(array $config): array
    {
        foreach ($config as &$profile) {
            if (!is_array($profile) || !isset($profile['extensions'])) {
                continue;
            }

            $resolved = [];
            foreach ($profile['extensions'] as $name => $extensionConfig) {
                $resolved[$this->resolveExtensionClassName($name)] = $extensionConfig;
            }
            $profile['extensions'] = $resolved;
        }

        return $config;
    }

    private function resolveExtensionClassName(string $name): string
    {
        if (class_exists($name)) {
            return $name;
        }

        $parts = explode('\\', $name);
        $last = preg_replace('/Extension$/', '', end($parts)) . 'Extension';
        $guessed = $name . '\\ServiceContainer\\' . $last;

        if (class_exists($guessed)) {
            return $guessed;
        }

        return $name;
    }

    private static function findPhpBinary(): string
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        if (false === $phpBinary) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $phpBinary;
    }
}
