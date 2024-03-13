<?php

namespace App\Wobz\MakerBundle;

use App\Wobz\MakerBundle\Trait\MakerOptionsTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @method string getCommandDescription()
 */
class BakeryMaker extends AbstractMaker
{
    use MakerOptionsTrait;

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return 'make:bakery';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Bakery to the game and updates service.yaml';
    }

    /**
     * @inheritDoc
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setHelp('This command creates a new Bakery of a given class.')
            ->addArgument(
                'bake-name',
                InputArgument::REQUIRED,
                sprintf('🥨 Name of the class that needs baking... (e.g. <fg=yellow>%s</>)', "User, Bread or Croissant etc...")
            );
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $io->title('🥐 Welcome to the Lyonnaise Bakery 🥖🥖 !');
    }

    /**
     * @inheritDoc
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // faker,
    }
    
    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $bakeName = $input->getArgument('bake-name');
        $bakeryName = Str::asClassName($bakeName);
        
        $io->text('🍪 Bakery is up and running !' . PHP_EOL . 'Start baking with 🍞 (new ' . $bakeryName . '())->gimmeOne(); 🍞');

        $classNameDetails = $generator->createClassNameDetails($bakeName, "Application\\Bakery\\");

        $generator->generateClass($classNameDetails->getFullName(), $bakeryName);
    }
    
    public function __call(string $name, array $arguments)
    {
        return self::getCommandDescription();
    }
}