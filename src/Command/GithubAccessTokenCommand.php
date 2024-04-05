<?php

use Firebase\JWT\JWT;
use Github\AuthMethod;
use Github\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:github',
    description: 'Get a access token via GitHub App.',
)]
class GithubAccessTokenCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to get a access token via GitHub App using app ID, private key, and installation ID.')
            ->addArgument('appId', InputArgument::REQUIRED, 'The GitHub App ID.')
            ->addArgument('privateKeyPath', InputArgument::REQUIRED, 'Path to the GitHub App private key.')
            ->addArgument('installationId', InputArgument::REQUIRED, 'The installation ID.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appId = $input->getArgument('appId');
        $privateKeyPath = $input->getArgument('privateKeyPath');
        $installationId = $input->getArgument('installationId');

        $privateKey = file_get_contents($privateKeyPath);

        $payload = [
            'iat' => time(),
            'exp' => time() + (10 * 60),
            'iss' => $appId,
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');

        $client = new Client();
        $client->authenticate($jwt, null, AuthMethod::JWT);
        $token = $client->apps()->createInstallationToken($installationId);

        $output->writeln("Access Token: " . $token['token']);

        return Command::SUCCESS;
    }
}