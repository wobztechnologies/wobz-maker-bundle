<?= "<?php\n" ?>

namespace App\Infrastructure\Workflow\<?= $workflowNamePascalCase ?>\<?= $transitionName ?>;

<?= $useStatements ?>

final class <?= $transitionName ?> extends Abstract<?= $workflowNamePascalCase ?>Workflow implements EventSubscriberInterface
{
    // +--------------------------------------------------------+
    // | Do not forget to explain your transition below.        |
    // | Now, transitions names are less explicit than before   |
    // +--------------------------------------------------------+
    //
    // The transition <?= $transitionNameSnackCase ?> is used to ...
    //

    public function guard<?= $transitionName ?>(GuardEvent $event): void
    {
        try {
            // TODO: to implement
            //  ⚠️ The guard use business rules to check if the transition can be done
            //  Remember that business rules must be declared in Domain
        } catch (Throwable $t) {
            $event->setBlocked(true, $t->getMessage());
        }
    }

    public function make<?= $transitionName ?>(TransitionEvent $event): void
    {
        try {
            // TODO: to implement
        } catch (Throwable $t) {
            $errorNumber = uuid_create();
            $this-><?= $workflowNameCameCase ?>WorkflowLogger->critical("Error number : $errorNumber. Message : " .
            $t->getMessage());
            throw new WorkflowMakeException($event->getTransition()->getName(), $errorNumber);
        }
    }

    public function complete<?= $transitionName ?>(CompletedEvent $event): void
    {
        try {
            // TODO: to implement
        } catch (Throwable $t) {
            $this-><?= $workflowNameCameCase ?>WorkflowLogger->critical("Error when complete<?= $workflowNameNormalCase ?> workflow. Message : " . $t->getMessage());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            "workflow.<?= $workflowNameSnackCase ?>_workflow.guard.<?= $transitionNameSnackCase ?>" => "guard<?= $transitionName ?>",
            "workflow.<?= $workflowNameSnackCase ?>_workflow.transition.<?= $transitionNameSnackCase ?>" => "make<?= $transitionName ?>",
            "workflow.<?= $workflowNameSnackCase ?>_workflow.completed.<?= $transitionNameSnackCase ?>" => "complete<?= $transitionName ?>",
        ];
    }
}