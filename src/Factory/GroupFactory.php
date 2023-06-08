<?php

namespace App\Factory;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Group>
 *
 * @method        Group|Proxy                     create(array|callable $attributes = [])
 * @method static Group|Proxy                     createOne(array $attributes = [])
 * @method static Group|Proxy                     find(object|array|mixed $criteria)
 * @method static Group|Proxy                     findOrCreate(array $attributes)
 * @method static Group|Proxy                     first(string $sortedField = 'id')
 * @method static Group|Proxy                     last(string $sortedField = 'id')
 * @method static Group|Proxy                     random(array $attributes = [])
 * @method static Group|Proxy                     randomOrCreate(array $attributes = [])
 * @method static GroupRepository|RepositoryProxy repository()
 * @method static Group[]|Proxy[]                 all()
 * @method static Group[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Group[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Group[]|Proxy[]                 findBy(array $attributes)
 * @method static Group[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Group[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Group> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Group> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Group> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Group> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Group> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Group> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Group> random(array $attributes = [])
 * @phpstan-method static Proxy<Group> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Group> repository()
 * @phpstan-method static list<Proxy<Group>> all()
 * @phpstan-method static list<Proxy<Group>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Proxy<Group>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Proxy<Group>> findBy(array $attributes)
 * @phpstan-method static list<Proxy<Group>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Proxy<Group>> randomSet(int $number, array $attributes = [])
 */
final class GroupFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Group $group): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Group::class;
    }
}
