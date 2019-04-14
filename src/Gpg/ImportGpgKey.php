<?php

declare(strict_types=1);

namespace Doctrine\AutomaticReleases\Gpg;

use Assert\Assert;
use Symfony\Component\Process\Process;
use function assert;
use function is_array;
use function Safe\file_put_contents;
use function Safe\preg_match;
use function Safe\tempnam;
use function sys_get_temp_dir;

final class ImportGpgKey
{
    public function __invoke(string $keyContents) : SecretKeyId
    {
        $keyFileName = tempnam(sys_get_temp_dir(), 'imported-key');

        file_put_contents($keyFileName, $keyContents);

        $output = (new Process(['gpg', '--import', $keyFileName]))
            ->mustRun()
            ->getErrorOutput();

        Assert::that($output)
              ->regex('/key\\s+([A-F0-9]+):\\s+secret\\s+key\\s+imported/im');

        preg_match('/key\\s+([A-F0-9]+):\\s+secret\\s+key\\s+imported/im', $output, $matches);

        assert(is_array($matches));

        return SecretKeyId::fromBase16String($matches[1]);
    }
}
