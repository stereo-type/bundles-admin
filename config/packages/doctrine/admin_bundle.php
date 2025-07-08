<?php

/**
 * @copyright  2024 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

return [
    'AcademCity\AdminBundle' => [
        'type' => 'attribute',
        'is_bundle' => false,
        'dir' => '%kernel.project_dir%/vendor/academcity/admin-bundle/src/Domain/Entity',
        'prefix' => 'AcademCity\AdminBundle\Domain\Entity',
        'alias' => 'AcademCity\AdminBundle',
    ],
];
