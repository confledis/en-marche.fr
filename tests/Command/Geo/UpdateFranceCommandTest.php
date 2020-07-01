<?php

namespace Tests\App\Command\Geo;

use App\Command\Geo\UpdateFranceCommand;
use App\Entity\Geo\City;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\Region;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @group command
 * @group geo
 */
final class UpdateFranceCommandTest extends WebTestCase
{
    public function testDryRunDoNotWrite(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /* @var UpdateFranceCommand $command */
        $command = $application->find('app:geo:update-france');

        // Inject mock client
        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/regions.json')),
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/departments.json')),
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/cities.json')),
        ], 'http://null'));

        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertContains('Nothing was persisted in database', $output);

        // @todo remove it once it's present in fixtures
        $this->assertFalse($this->exists(Country::class, 'FR'));

        $this->assertFalse($this->exists(Region::class, 'R-A'));
        $this->assertFalse($this->exists(Region::class, 'R-B'));

        $this->assertFalse($this->exists(Department::class, 'D-A-1'));
        $this->assertFalse($this->exists(Department::class, 'D-B-1'));
        $this->assertFalse($this->exists(Department::class, 'D-B-2'));

        $this->assertFalse($this->exists(City::class, 'C-A-1-1'));
        $this->assertFalse($this->exists(City::class, 'C-A-1-2'));
        $this->assertFalse($this->exists(City::class, 'C-B-2-1'));
    }

    public function testPersistingCommand(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /* @var UpdateFranceCommand $command */
        $command = $application->find('app:geo:update-france');

        // Inject mock client
        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/regions.json')),
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/departments.json')),
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/ok/cities.json')),
        ], 'http://null'));

        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertNotContains('Nothing was persisted in database', $output);

        $this->assertTrue($this->exists(Country::class, 'FR'));

        $this->assertTrue($this->exists(Region::class, 'R-A'));
        $this->assertTrue($this->exists(Region::class, 'R-B'));

        $this->assertTrue($this->exists(Department::class, 'D-A-1'));
        $this->assertTrue($this->exists(Department::class, 'D-B-1'));
        $this->assertTrue($this->exists(Department::class, 'D-B-2'));

        $this->assertTrue($this->exists(City::class, 'C-A-1-1'));
        $this->assertTrue($this->exists(City::class, 'C-A-1-2'));
        $this->assertTrue($this->exists(City::class, 'C-B-2-1'));
    }

    public function testBrokenDependency(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /* @var UpdateFranceCommand $command */
        $command = $application->find('app:geo:update-france');

        // Inject mock client
        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/broken/regions.json')),
            new MockResponse(file_get_contents(__DIR__.'/geo-api-payload/broken/departments.json')),
            new MockResponse('[]'),
        ], 'http://null'));

        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('App\Entity\Geo\Region#R-X not found');

        $commandTester->execute([]);
    }

    private function exists(string $class, string $code): bool
    {
        /* @var EntityManagerInterface $repository */
        $repository = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        return (bool) $repository->getRepository($class)->findOneBy([
            'code' => $code,
        ]);
    }
}
