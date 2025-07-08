<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace AcademCity\AdminBundle\Application\Component\Managers\File;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @template T of object
 *
 * @implements ProxyQueryInterface<T>
 **/
class FileProxyQuery implements ProxyQueryInterface
{
    private array $files;
    private array $where = [];

    private ?string $sortBy = null;
    private ?string $sortOrder = null;

    private ?int $firstResult = null;
    private ?int $maxResults = null;

    public function __construct(string $directory)
    {
        $paths = glob($directory . '/*');
        $this->files = array_map(function ($filePath) {
            return new FileItem(
                basename($filePath),
                filesize($filePath),
                new \DateTimeImmutable('@' . filemtime($filePath))
            );
        }, $paths);
    }

    public function execute(): array
    {
        $results = $this->files;

        // Фильтрация (только по name, упрощённо)
        if (!empty($this->where)) {
            $results = array_filter($results, function (FileItem $item) {
                foreach ($this->where as $condition) {
                    if (!str_contains(mb_strtolower($item->name), mb_strtolower($condition))) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Сортировка
        if (null !== $this->sortBy) {
            usort($results, function (FileItem $a, FileItem $b) {
                $valueA = $a->{$this->sortBy};
                $valueB = $b->{$this->sortBy};

                $cmp = $valueA <=> $valueB;

                return 'DESC' === $this->sortOrder ? -$cmp : $cmp;
            });
        }

        // Пагинация
        if (null !== $this->firstResult || null !== $this->maxResults) {
            $offset = $this->firstResult ?? 0;
            $length = $this->maxResults ?? null;
            $results = array_slice($results, $offset, $length);
        }

        return array_values($results); // пересобрать индексы
    }

    public function setSortBy(array $parentAssociationMappings, array $fieldMapping): ProxyQueryInterface
    {
        $this->sortBy = $fieldMapping['fieldName'] ?? null;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortOrder(string $sortOrder): ProxyQueryInterface
    {
        $this->sortOrder = strtoupper($sortOrder);

        return $this;
    }

    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    public function setFirstResult(?int $firstResult): ProxyQueryInterface
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setMaxResults(?int $maxResults): ProxyQueryInterface
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function getUniqueParameterId(): int
    {
        return spl_object_id($this);
    }

    public function entityJoin(array $associationMappings): string
    {
        return ''; // не используется, т.к. нет сущностей
    }

    public function getQueryBuilder(): QueryBuilder
    {
        throw new \LogicException('This proxy query is not based on Doctrine ORM.');
    }

    /**
     * @return Query<int, FileItem>
     */
    public function getDoctrineQuery(): Query
    {
        throw new \LogicException('This proxy query is not based on Doctrine ORM.');
    }
}
