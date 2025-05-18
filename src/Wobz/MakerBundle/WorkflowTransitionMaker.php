<?php

namespace App\Wobz\MakerBundle;

use App\Wobz\MakerBundle\Trait\MakerOptionsTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Exception;

/**
 * @method string getCommandDescription()
 */
final class WorkflowTransitionMaker extends AbstractMaker
{
    use MakerOptionsTrait;

    public static function getCommandName(): string
    {
        return 'make:workflow-transition';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new workflow transition class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setHelp('This command allows you to generate transition class')
            ->addArgument(
                'workflow-name',
                InputArgument::REQUIRED,
                sprintf('Workflow name (name of the entity) (e.g. <fg=yellow>%s</>)', "Order, Batch or OrderContent")
            )
            ->addArgument(
                'start-place',
                InputArgument::REQUIRED,
                sprintf('Start place (name in entities (e.g. <fg=yellow>%s</>)', "Adv, Graphiste or Production")
            )
            ->addArgument(
                'end-place',
                InputArgument::REQUIRED,
                sprintf('End place (name in entities (e.g. <fg=yellow>%s</>)', "Adv, Graphiste or Production")
            )
            ->addArgument(
                'dynamic-places',
                InputArgument::REQUIRED,
                sprintf('Do you want dynamic name for your places of the workflow ? (e.g. <fg=yellow>%s</>)', "no"),
            );
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $io->title('Welcome to the Workflow Transition Maker (We consider that the basic installation of the workflow is done (including yaml) and that the basics are understood.)');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $workflowName = $input->getArgument('workflow-name');
        $startPlaceInitial = $input->getArgument('start-place');
        $endPlaceInitial = $input->getArgument('end-place');
        $dynamicPlaces = $input->getArgument('dynamic-places');

        $startPlace = $startPlaceInitial;
        $endPlace = $endPlaceInitial;

        if ($dynamicPlaces === "yes") {
            $configEntity = "./config/services.yaml";
            $configEntityContent = Yaml::parseFile($configEntity)['parameters']['wobz_workflow'];

            $startPlace = $this->getDynamicPlaceName($startPlaceInitial, $configEntityContent, $workflowName);
            $endPlace = $this->getDynamicPlaceName($endPlace, $configEntityContent, $workflowName);
        }

        $transitionName = "From{$startPlace}To{$endPlace}";
        $workflowNamePascalCase = ucwords($workflowName);
        $workflowNameSnackCase = $this->camelCaseToSnakeCase($workflowName);
        $transitionNameSnackCase = $this->camelCaseToSnakeCase($transitionName);

        $classNameDetails = $generator->createClassNameDetails(
            $transitionName,
            "Infrastructure\\Workflow\\$workflowNamePascalCase\\$transitionName\\",
        );

        $useStatements = $this->addDependencies([
            'App\\Infrastructure\\Workflow\\' . $workflowNamePascalCase . '\\Abstract' . $workflowNamePascalCase . 'Workflow',
            "App\Domain\Exception\Workflow\WorkflowMakeException",
            "Symfony\Component\EventDispatcher\EventSubscriberInterface",
            "Symfony\Component\Workflow\Event\CompletedEvent",
            "Symfony\Component\Workflow\Event\GuardEvent",
            "Symfony\Component\Workflow\Event\TransitionEvent",
            "Throwable",
        ]);

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/WorkflowTransition.tpl.php',
            [
                'useStatements' => $useStatements,
                'workflowNameCameCase' => lcfirst($workflowName),
                'workflowNameNormalCase' => strtolower(implode(' ', preg_split('/(?=[A-Z])/', $workflowNamePascalCase))),
                'workflowNamePascalCase' => $workflowNamePascalCase,
                'workflowNameSnackCase' => $workflowNameSnackCase,
                'transitionName' => $transitionName,
                'transitionNameSnackCase' => $transitionNameSnackCase,
            ]
        );

        $generator->writeChanges();
        $this->writeYamlChanges($workflowName, $transitionName, $startPlaceInitial, $endPlaceInitial);

        $filepath = 'src/Infrastructure/Workflow/' . $workflowNamePascalCase . '/' . $transitionName . '/' . $transitionName . '.php';
        $filesInfo[] = [
            "path" => $filepath,
            "name" => $transitionName,
        ];

        $comment = "Do not forget to explain your transition in the created file class.";

        $this->end($io, $filesInfo, $comment, "workflow.yaml");
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        return null;
    }

    protected function addDependencies(array $dependencies, string $message = null): string
    {
        $useStatements = '';
        foreach ($dependencies as $dependency) {
            $useStatements .= "use $dependency;\n";
        }

        return $useStatements;
    }

    private function writeYamlChanges(string $workflowName, string $transitionName, string $startStatus, string $endStatus): void
    {
        $yamlWorkflowName = $this->camelCaseToSnakeCase($workflowName) . "_workflow";
        $yamlTransitionName = $this->camelCaseToSnakeCase($transitionName);
        $yamlFile = "./config/packages/workflow.yaml";

        $existingData = Yaml::parseFile($yamlFile);
        $transitions = $existingData['framework']['workflows'][$yamlWorkflowName]['transitions'];

        if (array_key_exists(0, $transitions) && $transitions[0] === null) {
            $existingData['framework']['workflows'][$yamlWorkflowName]['transitions'] = [];
        }

        $newTransition = [
            'framework' => [
                'workflows' => [
                    "$yamlWorkflowName" => [
                        'transitions' => [
                            "$yamlTransitionName" => [
                                'from' => "$startStatus",
                                'to' => "$endStatus"
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $updatedData = array_merge_recursive($existingData, $newTransition);

        $yaml = Yaml::dump($updatedData, 8);
        $filesystem = new Filesystem();
        $filesystem->dumpFile($yamlFile, $yaml);
    }

    private function camelCaseToSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    private function getDynamicPlaceName(string $initialPlace, array $configEntityContent, string $workflowName): string
    {
        foreach ($configEntityContent['entity'] as $entity) {
            if (key($entity) !== $workflowName) {
                continue;
            }

            $entityName = key($entity);
            $function = $entity[$entityName]['enum']['method'];
            if ($function !== null) {
                $place = $entity[$entityName]['enum']['fqcn']::from($initialPlace)->$function();
            } else {
                $place = $entity[$entityName]['enum']['fqcn']::from($initialPlace)->value;
            }
        }

        if (!isset($place)) {
            throw new Exception("The workflow name $workflowName (and entities) is not found in the config file.");
        }

        return $place;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // TODO: Implement configureDependencies() method.
    }
}