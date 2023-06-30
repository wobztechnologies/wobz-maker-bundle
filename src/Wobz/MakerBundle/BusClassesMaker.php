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
use Symfony\Component\Console\Question\Question;

/**
 * @method string getCommandDescription()
 */
class BusClassesMaker extends AbstractMaker
{
    use MakerOptionsTrait;

    public static function getCommandName(): string
    {
        return 'make:bus';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new bus with necessary classes and update services.yaml';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setHelp('This command allows you to generate bus classes')
            ->addArgument(
                'bus-name',
                InputArgument::REQUIRED,
                sprintf('Bus name for example, action, use case... (e.g. <fg=yellow>%s</>)', "CreateOrder, GetOrder or UpdateOrder etc...")
            )
            ->addArgument(
                'bus-type',
                InputArgument::REQUIRED,
                sprintf('Bus type (e.g. <fg=yellow>%s</>)', "Command, Query or Async"),
            )
            ->addArgument(
                'bus-folder',
                InputArgument::REQUIRED,
                sprintf('Bus folder (Where in bus type, if the folder already exist, the classes will be in it) (e.g. <fg=yellow>%s</>)', "Orders, File, Invoice etc...")
            );
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $io->title('Welcome to the Bus Maker !');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $busName = $input->getArgument('bus-name');
        $busType = $input->getArgument('bus-type');
        $busFolder = $input->getArgument('bus-folder');
        $busNamePascalCase = Str::asClassName($busName);
        $busFolderPascalCase = Str::asClassName($busFolder);

        if (!in_array($busType, ['Command', 'Query', 'Async'])) {
            $io->error('Bus type must be one of Command, Query or Async');
            exit;
        }

        $io->text([
            'Bus generated! Now let\'s add some property(ies)!',
            'By default, all the properties will be public.',
            'For specifics types, you should create it by yourself.',
        ]);

        $isFirstField = true;
        $fields = [];
        while (true) {
            $newField = $this->askForNextField($io, $isFirstField);
            $fields[] = $newField;
            $isFirstField = false;

            if (null === $newField)
                break;
        }

        $classNameDetails = $generator->createClassNameDetails(
            $busName,
            "Application\\$busType\\$busFolder\\$busName\\",
        );

        $classNameDetailsHandler = $generator->createClassNameDetails(
            $busName . 'Handler',
            "Application\\$busType\\$busFolder\\$busName\\",
        );

        $classNameDetailsTest = $generator->createClassNameDetails(
            $busName . 'Test',
            "Tests\\FunctionalTest\\$busType\\$busFolder\\",
        );

        // Remove the last element of the array (null)
        unset($fields[array_key_last($fields)]);

        $hasDate = false;
        foreach ($fields as &$value) {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $value['name'])) {
                $io->error(sprintf('%s The variable name invalid, please try again!', $value['name']));
                exit;
            }

            $value['name'] = lcfirst(Str::asClassName($value['name']));

            if ($value['type'] === "datetime")
                $hasDate = true;
        }

        $generator->generateClass(
            $classNameDetails->getFullName(),
            'src/Wobz/MakerBundle/templates/Bus.tpl.php',
            [
                'busFolder' => $busFolderPascalCase,
                'busType' => $busType,
                'busName' => $busNamePascalCase,
                'variables' => $fields,
                'hasDate' => $hasDate,
            ]
        );

        $generator->generateClass(
            $classNameDetailsHandler->getFullName(),
            'src/Wobz/MakerBundle/templates/BusHandler.tpl.php',
            [
                'busFolder' => $busFolderPascalCase,
                'busType' => $busType,
                'busName' => $busNamePascalCase,
                'busNameCamelCase' => lcfirst($busNamePascalCase),
                'variables' => $fields,
            ]
        );

        $generator->generateClass(
            $classNameDetailsTest->getFullName(),
            'src/Wobz/MakerBundle/templates/BusTest.tpl.php',
            [
                'busFolder' => $busFolderPascalCase,
                'busType' => $busType,
                'busName' => $busNamePascalCase,
            ]
        );

        $generator->writeChanges();
        $this->writeYamlChanges($busType, $busFolderPascalCase, $busNamePascalCase);

        $message = [
            "path" => "src/Application/$busType/$busFolder/$busName/$busName.php",
            "name" => "$busName",
        ];

        $handler = [
            "path" => "src/Application/$busType/$busFolder/$busName/$busName" . "Handler.php",
            "name" => "$busName" . "Handler",
        ];

        $test = [
            "path" => "tests/FunctionalTest/$busType/$busFolder/$busName" . "Test.php",
            "name" => "$busName" . "Test",
        ];

        $filesInfo = [$message, $handler, $test];
        $comment = "Don't forget to make a test on this bus!!";

        $this->end($io, $filesInfo, $comment, "security.yaml");
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // useless
    }

    private function askForNextField(ConsoleStyle $io, bool $isFirstField): ?array
    {
        $fields = [];
        $io->writeln('');

        if ($isFirstField) {
            $questionText = 'New property in your bus ? (press <return> to stop adding fields)';
        } else {
            $questionText = 'Add another property? Enter the property here (or press <return> to stop adding fields)';
        }

        $fieldName = $io->ask($questionText, null, function ($name) use ($fields) {
            if (!$name)
                return $name;

            if (in_array($name, $fields))
                throw new \InvalidArgumentException(sprintf('The "%s" property already exists.', $name));

            return $name;
        });

        if (!$fieldName)
            return null;

        $defaultType = 'string';
        $snakeCasedField = Str::asSnakeCase($fieldName);

        if ('_at' == $suffix = substr($snakeCasedField, -3)) {
            $defaultType = 'datetime';
        } elseif ('_id' == $suffix) {
            $defaultType = 'int';
        } elseif (str_starts_with($snakeCasedField, 'is_')) {
            $defaultType = 'bool';
        } elseif (str_starts_with($snakeCasedField, 'has_')) {
            $defaultType = 'bool';
        }

        $type = null;
        $allValidTypes = ["string", "int", "array", "float", "bool", "mixed", "datetime"];

        while (null === $type) {
            $question = new Question('Field type (php types only)', $defaultType);
            $question->setAutocompleterValues($allValidTypes);
            $type = $io->askQuestion($question);

            if (!in_array($type, $allValidTypes)) {
                $io->error(sprintf('Invalid type "%s".', $type));
                $io->writeln('');
                $io->writeln('Choose one of these types:');
                $io->listing($allValidTypes);
                $type = null;
            }
        }

        $fields["name"] = $fieldName;
        $fields["type"] = $type;

        return $fields;
    }

    private function writeYamlChanges(string $busType, string $busFolder, string $busName): void
    {
        $busTypeNormalized = Str::asSnakeCase($busType);
        $yamlContent = <<<YAML
                App\Application\\$busType\\$busFolder\\$busName\\{$busName}Handler:
                    tags: [ { name: messenger.message_handler, bus: $busTypeNormalized.bus } ]
                    autoconfigure: false
            YAML;

        $yamlFile = "%kernel.project_dir%/config/services.yaml";
        $existingContent = file_get_contents($yamlFile);
        $updatedContent = $existingContent . PHP_EOL . $yamlContent;
        file_put_contents($yamlFile, $updatedContent);
    }
}
