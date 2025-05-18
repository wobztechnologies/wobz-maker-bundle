<?php

namespace App\Wobz\MakerBundle\Trait;

use Symfony\Bundle\MakerBundle\ConsoleStyle;

trait MakerOptionsTrait
{
    protected function end(ConsoleStyle $io, array $filesInfo, string $comment = null, string $yamlFile = null): void
    {
        $io->newLine();

        if (!is_null($yamlFile)) {
            $io->writeln("<info> $yamlFile</info> is updated");
        }
        $io->newLine();

        foreach ($filesInfo as $fileInfo) {
            exec("git add {$fileInfo['path']}");
            $io->writeln("<info> {$fileInfo["name"]}.php</info> added to git.");
        }

        $io->newLine();
        if (!is_null($comment)) {
            $io->writeln("<comment> $comment</comment>");
        }

        $io->newLine();
        $io->success("Have fun Wobzer!");
    }
}