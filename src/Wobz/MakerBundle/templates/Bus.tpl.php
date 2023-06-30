<?= "<?php\n" ?>

namespace App\Application\<?= $busType ?>\<?= $busFolder ?>\<?= $busName ?>;

<?php if ($hasDate): ?>
use DateTime;

<?php endif; ?>
final class <?= $busName ?>

{
<?php foreach ($variables as $variable): ?>
<?php if ($variable["type"] === "datetime"): ?>
    public Datetime $<?= $variable["name"] ?>;
<?php else: ?>
    public <?= $variable["type"] ?> $<?= $variable["name"] ?>;
<?php endif; ?>
<?php endforeach; ?>
}