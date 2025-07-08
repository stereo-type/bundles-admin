<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace AcademCity\AdminBundle\Presentation\Controller;

use AcademCity\AdminBundle\Application\Service\TableUserPreferenceService;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-template T of object
 *
 * @phpstan-extends CRUDController<T>
 */
class BaseCRUDController extends CRUDController
{
    /**Метод получения регистра шаблонов, для переопределения определенного action  в определенном справочнике
     * @return TemplateRegistryInterface
     */
    protected function getTemplateRegistry(): TemplateRegistryInterface
    {
        // Устанавливаем кастомный шаблон для редактирования
        $reflectionClass = new \ReflectionClass(CRUDController::class);
        $property = $reflectionClass->getProperty('templateRegistry');
        $property->setAccessible(true); // Делаем приватное свойство доступным

        return $property->getValue($this);
    }

    public function getSettingsAction(Request $request, TableUserPreferenceService $tableUserPreferenceService): Response
    {
        try {
            $data = $request->request->all();
            $tableName = $data['table_name'] ?? null;

            if (!$tableName) {
                return $this->json(
                    ['success' => false, 'message' => 'Не указано название таблицы'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $this->json(['success' => true, 'data' => $tableUserPreferenceService->getTableData($tableName)]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function setSettingsAction(Request $request, TableUserPreferenceService $tableUserPreferenceService): Response
    {
        try {
            $requestData = $request->request->all();
            $tableName = $requestData['table_name'] ?? null;

            if (!$tableName) {
                return $this->json(
                    ['success' => false, 'message' => 'Не указано название таблицы'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $data = [
                'order' => explode(',', $requestData['columns_order'] ?? ''),
                'hidden' => explode(',', $requestData['columns_hidden'] ?? ''),
            ];
            $tableUserPreferenceService->setTableData($tableName, $data);

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function orderChangedAction(Request $request, TableUserPreferenceService $tableUserPreferenceService): Response
    {
        try {
            $requestData = $request->request->all();
            $tableName = $requestData['table_name'] ?? null;

            if (!$tableName) {
                return $this->json(
                    ['success' => false, 'message' => 'Не указано название таблицы'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $oldIndex = $requestData['old_index'] ?? null;
            $newIndex = $requestData['new_index'] ?? null;
            if (is_null($oldIndex) || is_null($newIndex)) {
                return $this->json(
                    ['success' => false, 'message' => 'Некорректные индексы'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $oldIndex = (int) $oldIndex;
            $newIndex = (int) $newIndex;

            $userData = $tableUserPreferenceService->getTableData($tableName);
            $oldOrder = $userData['order'];

            /**Получаем список колонок таблицы*/
            $columns = $this->admin->getDatagrid()->getColumns();
            $columnsData = array_values(array_map(static fn (FieldDescriptionInterface $i) => ['name' => $i->getName(), 'type' => $i->getType()], $columns->getElements()));

            /**Свапаем колонки по пришедшим индексам**/
            $newColumnsData = $columnsData;
            [$newColumnsData[$oldIndex], $newColumnsData[$newIndex]] = [$newColumnsData[$newIndex], $newColumnsData[$oldIndex]];
            /**Батч и экшены не сохраняются, отсеиваем**/
            $filteredNewColumns = array_values(array_map(
                static fn ($i) => $i['name'],
                array_filter($newColumnsData, static fn ($i) => !in_array($i['type'], ['actions', 'batch']))
            ));
            /**Пересортировываем пользовательские настройки**/
            $newOrder = $this->reorderArray($userData['order'], $filteredNewColumns);

            /**Сохраняем новое значение*/
            if ($newOrder !== $oldOrder) {
                $userData['order'] = $newOrder;
                $tableUserPreferenceService->setTableData($tableName, $userData);
            }

            return $this->json(
                [
                    'success' => true,
                    'old_index' => $oldIndex,
                    'new_index' => $newIndex,
                    'old_order' => $oldOrder,
                    'new_order' => $newOrder,
                ]
            );
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function reorderArray(array $original, array $order): array
    {
        $priority = array_flip($order);

        usort($original, static function ($a, $b) use ($priority) {
            $priorityA = $priority[$a] ?? count($priority);
            $priorityB = $priority[$b] ?? count($priority);

            return $priorityA <=> $priorityB;
        });

        return $original;
    }

    public function batchAction(Request $request): Response
    {
        $restMethod = $request->getMethod();

        /**Перехватываем ошибку тут, она в базовом методе иначе будет не обработана*/
        if (Request::METHOD_POST !== $restMethod) {
            $this->addFlash('sonata_flash_error', 'Повторите отправку формы');

            return $this->redirectToList();
        }

        return parent::batchAction($request);
    }

    protected function handleModelManagerThrowable(ModelManagerThrowable $exception)
    {
        $debug = $this->getParameter('kernel.debug');
        \assert(\is_bool($debug));
        /**Не надо выкидывать не обработанную ошибку*/
        //        if ($debug) {
        //            throw $exception;
        //        }

        $context = ['exception' => $exception];
        if (null !== $exception->getPrevious()) {
            $context['previous_exception_message'] = $exception->getPrevious()->getMessage();
        }
        $this->getLogger()->error($exception->getMessage(), $context);

        /**Метода скопирован из базового контроллера, только из-за этого**/
        return $exception->getMessage();
    }
}
