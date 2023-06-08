<?php

namespace App\Factory;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Image>
 *
 * @method        Image|Proxy                     create(array|callable $attributes = [])
 * @method static Image|Proxy                     createOne(array $attributes = [])
 * @method static Image|Proxy                     find(object|array|mixed $criteria)
 * @method static Image|Proxy                     findOrCreate(array $attributes)
 * @method static Image|Proxy                     first(string $sortedField = 'id')
 * @method static Image|Proxy                     last(string $sortedField = 'id')
 * @method static Image|Proxy                     random(array $attributes = [])
 * @method static Image|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ImageRepository|RepositoryProxy repository()
 * @method static Image[]|Proxy[]                 all()
 * @method static Image[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Image[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Image[]|Proxy[]                 findBy(array $attributes)
 * @method static Image[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Image[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Image> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Image> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Image> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Image> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Image> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Image> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Image> random(array $attributes = [])
 * @phpstan-method static Proxy<Image> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Image> repository()
 * @phpstan-method static list<Proxy<Image>> all()
 * @phpstan-method static list<Proxy<Image>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Proxy<Image>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Proxy<Image>> findBy(array $attributes)
 * @phpstan-method static list<Proxy<Image>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Proxy<Image>> randomSet(int $number, array $attributes = [])
 */
final class AvatarFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
        self::faker()->addProvider(new \Ottaviano\Faker\Gravatar(self::faker()));
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'path' => self::faker()->gravatarUrl(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Image $image): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Image::class;
    }
}
