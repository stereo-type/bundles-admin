<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Presentation\Controller;

use Slcorp\AdminBundle\Application\Service\TableUserPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user-preferences')]
class UserPreferenceController extends AbstractController
{
    #[Route('/set', methods: ['POST'])]
    public function setUserPreferences(
        TableUserPreferenceService $tablePrefs,
        Request $request,
    ): Response {
        try {
            $data = $request->request->all();
            $tablePrefs->setTableData($data['table'], $data);

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(
                ['success' => false, 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
