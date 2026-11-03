<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'caching-proxy',
    description: 'Start a caching proxy server that forwards requests to an origin and caches responses',
)]
class CachingProxyCommand
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Option('The port the caching proxy server will listen on')] string $port = '',
        #[Option('The origin server URL to forward requests to')] string $origin = '',
        #[Option('Clear the cache and exit')] bool $clearCache = false,
    ): int {

        if ($clearCache) {
            $io->success('Cache cleared.');

            return Command::SUCCESS;
        }

        if ($port === '' || $origin === '') {
            $io->error('Both --port and --origin are required (or use --clear-cache).');

            return Command::FAILURE;
        }

        if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            $io->error(sprintf('Invalid port "%s". Use a number between 1 and 65535.', $port));

            return Command::FAILURE;
        }

        $scheme = parse_url($origin, PHP_URL_SCHEME);
        if (filter_var($origin, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
            $io->error(sprintf('Invalid origin "%s". Use a full URL like http://dummyjson.com', $origin));

            return Command::FAILURE;
        }

        $origin = rtrim($origin, '/');

        $process = new Process(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', 'public', 'public/index.php'],
            $this->projectDir,
            ['PROXY_ORIGIN' => $origin],
        );
        $process->setTimeout(null);

        $io->success(sprintf('Caching proxy listening on http://127.0.0.1:%s → %s', $port, $origin));
        $io->comment('Press Ctrl+C to stop.');

        $process->run(function (string $type, string $buffer) use ($io): void {
            $io->write($buffer);
        });

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
