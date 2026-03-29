<?php

use PHPUnit\Framework\TestCase;

class PhpCompatibilityTest extends TestCase {
    public function testFirstPartyPhpFilesLintCleanly(): void {
        $root = realpath(__DIR__ . '/../..');

        $this->assertNotFalse($root);

        $directories = [
            $root . '/Api',
            $root . '/Content',
            $root . '/Helpers',
            $root . '/Testing',
            $root . '/tests',
            $root . '/workbench',
        ];

        $failures = [];

        foreach ($this->phpFilesIn($directories) as $file) {
            $command = sprintf(
                '%s -d error_reporting=E_ALL -d display_errors=1 -l %s 2>&1',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($file)
            );

            $output   = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            $result = implode("\n", $output);
            if (
                $exitCode !== 0 ||
                str_contains($result, 'Deprecated:') ||
                str_contains($result, 'Fatal error:') ||
                str_contains($result, 'Parse error:')
            ) {
                $failures[] = $file . "\n" . $result;
            }
        }

        $this->assertSame([], $failures, implode("\n\n", $failures));
    }

    private function phpFilesIn(array $directories): array {
        $files = [];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
