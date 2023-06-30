<?= "<?php\n" ?>

namespace App\Tests\FunctionalTest\<?= $busType ?>\<?= $busFolder ?>;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class <?= $busName ?>Test extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // TODO: Implement setUp() method.
    }

    public function test<?= $busName ?>(): void
    {
        $this->fail("Make your test, COME TRUE !");
        // Hope you see it before CI/CD :) !
       
        // TODO: Implement testGetSomething() method. (Road to 100% code coverage).
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // TODO: Implement tearDown() method.
    }
}