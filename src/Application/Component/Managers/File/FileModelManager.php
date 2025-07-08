<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace AcademCity\AdminBundle\Application\Component\Managers\File;

use AcademCity\AdminBundle\Kernel;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * @implements ModelManagerInterface<FileItem>
 **/
class FileModelManager implements ModelManagerInterface
{
    private string $directory = '/';

    public function __construct(private readonly Kernel $kernel)
    {
    }

    private function projectDirectory(): string
    {
        return $this->kernel->getProjectDir();
    }

    private function fullPath(): string
    {
        return $this->projectDirectory() . $this->directory;
    }

    /**
     * Используется в конфиге, для установки директории.
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = str_starts_with($directory, '/') ? $directory : '/' . $directory;

        return $this;
    }

    public function createQuery($class): ProxyQueryInterface
    {
        return new FileProxyQuery($this->fullPath());
    }

    public function getClass(): string
    {
        return FileItem::class;
    }

    public function find($class, $id): ?object
    {
        foreach (glob($this->fullPath() . '/*') as $path) {
            if (basename($path) === $id) {
                return new FileItem(
                    basename($path),
                    filesize($path),
                    new \DateTimeImmutable('@' . filemtime($path))
                );
            }
        }

        return null;
    }

    public function findBy($class, array $criteria = []): array
    {
        return $this->createQuery($class)->execute();
    }

    public function findOneBy($class, array $criteria = []): ?object
    {
        $all = $this->findBy($class, $criteria);

        return $all[0] ?? null;
    }

    public function getNormalizedIdentifier(object $model): string
    {
        return $model->name;
    }

    public function create(object $object): void
    {
    }

    public function update(object $object): void
    {
        // Не поддерживается
    }

    public function delete(object $object): void
    {
        $path = $this->fullPath() . '/' . $object->name;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function batchDelete(string $class, ProxyQueryInterface $query): void
    {
        foreach ($query->execute() as $item) {
            $this->delete($item);
        }
    }

    public function getIdentifierValues(object $model): array
    {
        return ['name' => $model->name];
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return ['name'];
    }

    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $model->name;
    }

    public function reverseTransform(object $object, array $array = []): void
    {
        // Не требуется
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof FileProxyQuery;
    }

    public function executeQuery(object $query)
    {
        return $query->execute();
    }

    public function getExportFields(string $class): array
    {
        return ['name', 'size', 'modified'];
    }

    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void
    {
        // Не требуется для файлов
    }
}
