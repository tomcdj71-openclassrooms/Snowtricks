<?php

namespace App\Factory;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Trick>
 *
 * @method        Trick|Proxy                     create(array|callable $attributes = [])
 * @method static Trick|Proxy                     createOne(array $attributes = [])
 * @method static Trick|Proxy                     find(object|array|mixed $criteria)
 * @method static Trick|Proxy                     findOrCreate(array $attributes)
 * @method static Trick|Proxy                     first(string $sortedField = 'id')
 * @method static Trick|Proxy                     last(string $sortedField = 'id')
 * @method static Trick|Proxy                     random(array $attributes = [])
 * @method static Trick|Proxy                     randomOrCreate(array $attributes = [])
 * @method static TrickRepository|RepositoryProxy repository()
 * @method static Trick[]|Proxy[]                 all()
 * @method static Trick[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Trick[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Trick[]|Proxy[]                 findBy(array $attributes)
 * @method static Trick[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Trick[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Trick> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Trick> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Trick> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Trick> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Trick> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Trick> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Trick> random(array $attributes = [])
 * @phpstan-method static Proxy<Trick> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Trick> repository()
 * @phpstan-method static list<Proxy<Trick>> all()
 * @phpstan-method static list<Proxy<Trick>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Proxy<Trick>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Proxy<Trick>> findBy(array $attributes)
 * @phpstan-method static list<Proxy<Trick>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Proxy<Trick>> randomSet(int $number, array $attributes = [])
 */
final class TrickFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
        self::faker()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(self::faker()));
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'author' => new LazyValue(fn () => UserFactory::random()),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'description' => self::faker()->text(),
            'featuredImage' => self::faker()->imageUrl(640, 480, true),
            'group' => new LazyValue(fn () => GroupFactory::random()),
            'slug' => self::faker()->text(255),
            'title' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Trick $trick): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Trick::class;
    }
}
