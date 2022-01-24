<?php

namespace Digbang\Money;

use Digbang\Money\Doctrine\Mappings\Embeddables\CurrencyMapping;
use Digbang\Money\Doctrine\Mappings\Embeddables\MoneyMapping;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\MoneyFormatter;

class MoneyServiceProvider extends ServiceProvider
{
    private const PACKAGE = 'money';

    public function boot(ManagerRegistry $managerRegistry, MetaDataManager $metadata)
    {
        /** @var EntityManager $entityManager */
        foreach ($managerRegistry->getManagers() as $entityManager) {
            $this->doctrineMappings($entityManager, $metadata);
        }
        $this->resources();
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), static::PACKAGE);
        $this->registerMoney();
    }

    private function registerMoney()
    {
        $this->app->bind(MoneyFormatter::class, function () {
            return new DecimalMoneyFormatter(new ISOCurrencies());
        });

        $this->app->bind(IntlMoneyFormatter::class, function () {
            $numberFormatter = new \NumberFormatter($this->app['config']->get(static::PACKAGE . '.locale'), \NumberFormatter::CURRENCY);
            $numberFormatter->setPattern(str_replace('¤', '', $numberFormatter->getPattern()));

            return new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
        });

        $this->app->bind(DecimalMoneyFormatter::class, function () {
            return new DecimalMoneyFormatter(new ISOCurrencies());
        });

        $this->app->make('view')
            ->getEngineResolver()->resolve('blade')->getCompiler()
            ->directive('money', function (Money $expression) {
                return "<?php echo prettyMoney($expression); ?>";
            });
    }

    protected function doctrineMappings(EntityManagerInterface $entityManager, MetaDataManager $metadata): void
    {
        /** @var FluentDriver $fluentDriver */
        $fluentDriver = $metadata->driver('fluent', [
            'mappings' => [
                CurrencyMapping::class,
                MoneyMapping::class,
            ],
        ]);

        /** @var MappingDriverChain $chain */
        $chain = $entityManager->getConfiguration()->getMetadataDriverImpl();
        $chain->addDriver($fluentDriver, 'Money');
    }

    protected function resources(): void
    {
        $this->publishes([
            $this->configPath() => config_path(static::PACKAGE . '.php'),
        ],
            'config');
    }

    /**
     * @return string
     */
    private function configPath(): string
    {
        return realpath(dirname(__DIR__)) . '/config/' . static::PACKAGE . '.php';
    }
}
