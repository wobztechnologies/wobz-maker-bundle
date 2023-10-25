<?= "<?php\n" ?>

namespace App\Infrastructure\DTO\Enums;

enum <?= $enumName ?>: string
{
<?php foreach ($lists as $property): ?>
    const <?= $property["constNae"] ?> = <?= $property["value"] ?>;
<?php endforeach; ?>
}

