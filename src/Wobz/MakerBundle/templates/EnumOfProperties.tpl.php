<?= "<?php\n" ?>

namespace App\Infrastructure\DTO\Enums;

enum <?= $enumName ?>: string
{
<?php foreach (listOfProperties as $caseName => $propertyName): ?>
    case <?= $caseName ?> = "<?= $propertyName ?>";
<?php endforeach; ?>
}

