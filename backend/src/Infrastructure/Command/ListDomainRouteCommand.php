<?php

namespace App\Infrastructure\Command;

use Override;
use ReflectionClass;
use ReflectionMethod;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[AsCommand(
    name: 'app:routes:domain-summary',
    description: 'Display domain/business summary of all routes of the application'
)]
class ListDomainRouteCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly AccessMapInterface $accessMap
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $routes = $this->router->getRouteCollection()->all();

        $tableRows = [];

        foreach ($routes as $name => $route) {
            // Ignore symfony route
            if (str_starts_with($name, '_')) {
                continue;
            }

            $path = $route->getPath();
            $methods = $route->getMethods() ? implode('|', $route->getMethods()) : 'ANY';

            //-- Guess Entity
            $entity = $this->guessEntityFromName($name);

            // Resovlves roles
            $roles = $this->resolveRequiredRoles($route);

            // Table views
            $tableRows[] = [
                $name,
                $methods,
                $path,
                $entity,
                $roles,
            ];
        }

        $io->title('Analyze HiringForge Domain Routes');
        $io->table(
            ['Route name', 'Methods', 'Path', 'Target Entity', 'Roles / Access'],
            $tableRows
        );

        return Command::SUCCESS;
    }

    private function guessEntityFromName(string $routeName): string
    {
        $normalizedName = mb_strtolower($routeName);

        if (str_contains($normalizedName, 'job_offer') || str_contains($normalizedName, 'joboffer')) {
            return 'JOB OFFER';
        }
        if (str_contains($normalizedName, 'application')) {
            return 'APPLICATION';
        }
        if (str_contains($normalizedName, 'user')) {
            return 'USER';
        }
        if (str_contains($normalizedName, 'interview')) {
            return 'INTERVIEW';
        }

        return 'Générique / Autre';
    }

    private function resolveRequiredRoles(Route $route): string
    {
        $path = $route->getPath();
        $primaryMethod = $route->getMethods()[0] ?? 'GET';

        //  Guess through AccessMap (security.yaml)
        $request = Request::create($path, $primaryMethod);
        [$yamlRoles] = $this->accessMap->getPatterns($request);

        //  Guessing through #[IsGranted] on controllers
        $attributeRoles = $this->extractIsGrantedAttributes($route);

        $resolvedPermissions = [];

        if (!empty($yamlRoles)) {
            $resolvedPermissions[] = '[Config] ' . implode(', ', $yamlRoles);
        }

        if (!empty($attributeRoles)) {
            $resolvedPermissions[] = '[Attribute] ' . implode(', ', $attributeRoles);
        }

        if (empty($resolvedPermissions)) {
            return 'PUBLIC / Non restreint';
        }

        return implode(' | ', $resolvedPermissions);
    }

    /**
     * Read is "IsGranted" props on the associated class & methode
     */
    private function extractIsGrantedAttributes(Route $route): array
    {
        $controller = $route->getDefault('_controller');

        if (!$controller || !str_contains($controller, '::')) {
            return [];
        }

        [$class, $method] = explode('::', $controller);

        if (!class_exists($class) || !method_exists($class, $method)) {
            return [];
        }

        $roles = [];

        // Class' Attribut
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getAttributes(IsGranted::class) as $attribute) {
            /** @var IsGranted $isGranted */
            $isGranted = $attribute->newInstance();
            $roles[] = (string) $isGranted->attribute;
        }

        //-- Methodes Attributs
        $reflectionMethod = new ReflectionMethod($class, $method);
        foreach ($reflectionMethod->getAttributes(IsGranted::class) as $attribute) {
            /** @var IsGranted $isGranted */
            $isGranted = $attribute->newInstance();
            $roles[] = (string) $isGranted->attribute;
        }

        return array_unique($roles);
    }
}