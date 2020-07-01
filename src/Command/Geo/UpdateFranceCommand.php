<?php

namespace App\Command\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UpdateFranceCommand extends Command
{
    private const FRANCE_CODE = 'FR';

    private const REGIONS_PATH = '/regions';
    private const DEPARTMENTS_PATH = '/departements';
    private const CITIES_PATH = '/communes';

    protected static $defaultName = 'app:geo:update-france';

    /**
     * @var HttpClientInterface
     */
    private $geoGouvApiClient;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Collection
     */
    private $entityCache;

    public function __construct(HttpClientInterface $geoGouvApiClient, EntityManagerInterface $em)
    {
        $this->geoGouvApiClient = $geoGouvApiClient;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update french administrative divisions')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without persisting any data.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entityCache = new ArrayCollection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating french administrative division');

        $this->executeCounty();
        $this->executeRegions();
        $this->executeDepartments();
        $this->executeCities();

        $this->io->section('Persisting in database');

        $dryRun = $input->getOption('dry-run');
        if ($dryRun) {
            $this->io->comment('Nothing was persisted in database');
        } else {
            foreach ($this->entityCache as $entities) {
                $this->em->persist($entities);
            }
            $this->em->flush();
        }

        $this->io->success('Done');

        return 0;
    }

    private function executeCounty(): void
    {
        $this->io->section('Country');

        $this->retrieveEntity(Country::class, self::FRANCE_CODE, static function () {
            return new Country(self::FRANCE_CODE, 'France');
        });
    }

    private function executeRegions(): void
    {
        $this->io->section('Regions');

        $france = $this->retrieveEntity(Country::class, self::FRANCE_CODE);

        $itemsRaw = $this->geoGouvApiClient->request('GET', self::REGIONS_PATH)->toArray();
        foreach ($itemsRaw as $raw) {
            $this->retrieveEntity(
                Region::class,
                $raw['code'],
                static function () use ($raw, $france): Region {
                    return new Region($raw['code'], $raw['nom'], $france);
                }
            );
        }
    }

    private function executeDepartments(): void
    {
        $this->io->section('Departments');

        $itemsRaw = $this->geoGouvApiClient->request('GET', self::DEPARTMENTS_PATH)->toArray();
        foreach ($itemsRaw as $raw) {
            $this->retrieveEntity(
                Department::class,
                $raw['code'],
                function () use ($raw): Department {
                    $region = $this->retrieveEntity(Region::class, $raw['codeRegion']);

                    return new Department($raw['code'], $raw['nom'], $region);
                }
            );
        }
    }

    private function executeCities(): void
    {
        $this->io->section('Cities');

        $itemsRaw = $this->geoGouvApiClient->request('GET', self::CITIES_PATH)->toArray();
        foreach ($itemsRaw as $raw) {
            $this->retrieveEntity(
                City::class,
                $raw['code'],
                function () use ($raw): City {
                    $department = $this->retrieveEntity(Department::class, $raw['codeDepartement']);

                    return new City($raw['code'], $raw['nom'], $department);
                }
            );
        }
    }

    /**
     * @return Country|Region|Department|City
     *
     * @throws \RuntimeException When entity doesn't exist in database and $factory argument isn't given
     */
    private function retrieveEntity(string $class, string $code, callable $factory = null): object
    {
        $key = $class.'#'.$code;

        if (!$this->entityCache->containsKey($key)) {
            $repository = $this->em->getRepository($class);

            /* @var Country|Region|Department|City $entity */
            $entity = $repository->findOneBy(['code' => $code]);
            if (!$entity) {
                if (!$factory) {
                    throw new \RuntimeException(sprintf('Entity %s not found', $key));
                }

                $this->io->writeln(sprintf('Creating %s', $key));
                $entity = $factory();
            }

            $this->entityCache->set($key, $entity);
        }

        return $this->entityCache->get($key);
    }
}
