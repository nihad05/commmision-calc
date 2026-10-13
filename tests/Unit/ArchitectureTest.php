<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function domain_layer_does_not_depend_on_the_framework(): void
    {
        $offenders = [];

        foreach ($this->domainFiles() as $file) {
            if (preg_match('/\bIlluminate\\\\/', (string) file_get_contents($file->getPathname())) === 1) {
                $offenders[] = $file->getPathname();
            }
        }

        self::assertSame([], $offenders, 'Domain files must not reference Illuminate\\*.');
    }

    /**
     * @return list<SplFileInfo>
     */
    private function domainFiles(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/app/Domain', RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $files = array_values(array_filter(
            iterator_to_array($iterator, false),
            static fn (SplFileInfo $file): bool => $file->getExtension() === 'php',
        ));

        self::assertNotEmpty($files);

        return $files;
    }
}
