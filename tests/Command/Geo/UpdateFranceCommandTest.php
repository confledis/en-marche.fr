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

        // Data (Mock)

        $regions = json_encode([
            ['nom' => 'Region A', 'code' => 'R-A'],
            ['nom' => 'Region B', 'code' => 'R-B'],
        ], \JSON_THROW_ON_ERROR);

        $departments = json_encode([
            ['nom' => 'Department A-1', 'code' => 'D-A-1', 'codeRegion' => 'R-A'],
            ['nom' => 'Department B-1', 'code' => 'D-B-1', 'codeRegion' => 'R-B'],
            ['nom' => 'Department B-2', 'code' => 'D-B-2', 'codeRegion' => 'R-B'],
        ], \JSON_THROW_ON_ERROR);

        $cities = json_encode([
            ['nom' => 'City A-1-1', 'code' => 'C-A-1-1', 'codeDepartement' => 'D-A-1'],
            ['nom' => 'City A-1-2', 'code' => 'C-A-1-2', 'codeDepartement' => 'D-A-1'],
            ['nom' => 'City B-2-1', 'code' => 'C-B-2-1', 'codeDepartement' => 'D-B-2'],
        ], \JSON_THROW_ON_ERROR);

        // HTTP Client (Mock)

        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse($regions),
            new MockResponse($departments),
            new MockResponse($cities),
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

        // Data (Mock)

        $regions = json_encode([
            ['nom' => 'Region A', 'code' => 'R-A'],
            ['nom' => 'Region B', 'code' => 'R-B'],
        ], \JSON_THROW_ON_ERROR);

        $departments = json_encode([
            ['nom' => 'Department A-1', 'code' => 'D-A-1', 'codeRegion' => 'R-A'],
            ['nom' => 'Department B-1', 'code' => 'D-B-1', 'codeRegion' => 'R-B'],
            ['nom' => 'Department B-2', 'code' => 'D-B-2', 'codeRegion' => 'R-B'],
        ], \JSON_THROW_ON_ERROR);

        $cities = json_encode([
            ['nom' => 'City A-1-1', 'code' => 'C-A-1-1', 'codeDepartement' => 'D-A-1'],
            ['nom' => 'City A-1-2', 'code' => 'C-A-1-2', 'codeDepartement' => 'D-A-1'],
            ['nom' => 'City B-2-1', 'code' => 'C-B-2-1', 'codeDepartement' => 'D-B-2'],
        ], \JSON_THROW_ON_ERROR);

        // HTTP Client (Mock)

        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse($regions),
            new MockResponse($departments),
            new MockResponse($cities),
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

        // Data (Mock)

        $regions = json_encode([
            ['nom' => 'Region A', 'code' => 'R-A'],
        ], \JSON_THROW_ON_ERROR);

        $departments = json_encode([
            ['nom' => 'Department X-1', 'code' => 'D-X-1', 'codeRegion' => 'R-X'],
        ], \JSON_THROW_ON_ERROR);

        // HTTP Client (Mock)

        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('geoGouvApiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse($regions),
            new MockResponse($departments),
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
