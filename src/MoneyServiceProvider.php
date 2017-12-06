<?php

namespace Digbang\Money;

use Digbang\Money\Doctrine\Mappings\Embeddables\CurrencyMapping;
use Digbang\Money\Doctrine\Mappings\Embeddables\MoneyMapping;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
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

    public function boot(EntityManagerInterface $entityManager, MetaDataManager $metadata)
    {
        $this->registerDoctrineMappings($entityManager, $metadata);
        $this->resources();
    }

    public function register(Config $config)
    {
        $this->mergeConfigFrom($this->configPath(), static::PACKAGE);
        $this->registerMoney($config);
    }

    private function registerMoney(Config $config)
    {
        $this->app->bind(MoneyFormatter::class, function () {
            return new DecimalMoneyFormatter(new ISOCurrencies());
        });

        $this->app->bind(IntlMoneyFormatter::class, function () use ($config) {
            return new IntlMoneyFormatter(
                new \NumberFormatter($config->get(static::PACKAGE . '.locale'), \NumberFormatter::CURRENCY),
                new ISOCurrencies()
            );
        });

        $this->app
            ->make(BladeCompiler::class)
            ->directive('money', function (Money $expression) {
                return "<?php echo prettyMoney($expression); ?>";
            });
    }

    protected function registerDoctrineMappings(EntityManagerInterface $entityManager, MetaDataManager $metadata): void
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
        $chain->addDriver($fluentDriver, 'Digbang\Money');
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
