<?= "<?php\n" ?>

namespace App\Infrastructure\DTO\Enums;

enum <?= $enumName ?>: string
{
<?php foreach ($listOfPropertiesWithConstNameAndValue as $caseName => $propertyName): ?>
    case <?= $caseName ?> = "<?= $propertyName ?>";
<?php endforeach; ?>
}

