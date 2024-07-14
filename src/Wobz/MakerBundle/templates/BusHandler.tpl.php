<?= "<?php\n" ?>

namespace App\Application\<?= $busType ?>\<?= $busFolder ?>\<?= $busName ?>;

// TODO: Infrastructure forbidden here, only Domain/Application.
<?php if (!$isYaml):?>
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
<?php endif; ?>

<?php if (!$isYaml):?>
#[AsMessageHandler(bus: <?= $busTypeMessage ?>)]
<?php endif; ?>
final readonly class <?= $busName ?>Handler
{
    public function __construct()
    {
        // TODO: Implement __construct() method.
    }

    public function __invoke(<?= $busName ?> $<?= $busNameCamelCase ?>)
    {
<?php foreach ($variables as $variable): ?>
        $<?= $variable["name"] ?> = $<?= $busNameCamelCase ?>-><?= $variable["name"] ?>;
<?php endforeach; ?>

        // TODO: Implement __invoke() method.
    }
}