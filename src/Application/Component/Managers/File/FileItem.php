<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component\Managers\File;

readonly class FileItem
{
    public function __construct(
        public string $name,
        public int $size,
        public \DateTimeInterface $modified,
    ) {
    }
}
