<?php

namespace App\Wobz\MakerBundle;

use App\Wobz\MakerBundle\Trait\MakerOptionsTrait;
use ReflectionClass;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @method string getCommandDescription()
 */
class EnumOfPropertiesFromEntityMaker implements MakerInterface
{
    use MakerOptionsTrait;

    public static function getCommandName(): string
    {
        return "make:enum-from-entity";
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setHelp('This command allows you to generate enum ')
            ->addArgument(
                'entity-name',
                InputArgument::REQUIRED,
                sprintf('Entity name (e.g. <fg=yellow>%s</>)', "Order, OrdersContent, etc...")
            )
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // useless
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $io->title('Welcome to the Enum Maker ! Useful for DTOs !');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityName = $input->getArgument('entity-name');

        $reflectionClass = new ReflectionClass("App\\Infrastructure\\Persistence\\Entity\\$entityName");
        $listOfProperties = $reflectionClass->getProperties();
        $listOfPropertiesWithConstNameAndValue = array_map(function ($property) {
            return [$this->camelCaseToUpperCaseWithUnderscores($property) => $property];
        }, $listOfProperties);

        $classNameDetails = $generator->createClassNameDetails(
            $entityName . "Assembler",
            'App\\Infrastructure\\DTO\\Enums\\',
            'Enum'
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EnumOfProperties.tpl.php',
            [
                'listOfProperties' => $listOfPropertiesWithConstNameAndValue,
                'entityName' => $entityName,
                'enumName' => $classNameDetails->getShortName(),
            ]
        );

        $generator->writeChanges();
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        return null;
    }

    private function camelCaseToUpperCaseWithUnderscores(string $property): string
    {
        $property = preg_replace('/(?<!^)[A-Z]/', '_$0', $property);
        return strtoupper($property);
    }
}
