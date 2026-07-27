<?php


namespace App\Api\Controllers\Department;

use App\Api\Responder\ApiResponse;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Application\Usecases\Department\DepartmentGenerator;

use App\Api\Controllers\Department\Mapper\CreateDepartmentRequestMapper;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/departments")]
class DepartmentController extends AbstractController
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private LoggerInterface $logger
    ) {
        ApiResponse::init($logger);
    }


    /**
     * GET /api/departments?companyId=12
     * Retrieves the department tree for a company
     */
    #[Route('', methods: ['GET'])]
    public function list(
        Request $request,

    ): JsonResponse
    {
        try{
            $companyId = $request->query->get('companyId');

            if (!$companyId) {
                return ApiResponse::error(
                    message: 'companyId is required',
                    statusCode: Response::HTTP_BAD_REQUEST
                )->toJsonResponse();
            }

            // Only active departments are included in the selection in the forms
            $onlyActive = $request->query->getBoolean('activeOnly', true);
            $departments = $this->departmentRepository->findTreeByCompany((string) $companyId, $onlyActive);

            return ApiResponse::success(
                data: $departments,
                statusCode: Response::HTTP_OK
            )->toJsonResponse();
        }
        catch(\Exception $e){
            return ApiResponse::error(message: "Something went wrong", statusCode: Response::HTTP_BAD_REQUEST ,throwable: $e)->toJsonResponse();
        }
    }
    


    /**
     * POST /api/departments
     * Creation of a New Department
     */
    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        CreateDepartmentRequestMapper $mapper, 
        DepartmentGenerator $generator   
    ): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true);
            $command = $mapper->fromArray($data);

            if (empty($command->label) || empty($command->companyId)) {
                return ApiResponse::error(
                    message: 'label and companyId are required', 
                    statusCode: Response::HTTP_UNPROCESSABLE_ENTITY
                )->toJsonResponse();
            }

            $department = $generator->execute($command);
            return ApiResponse::success(data: $department, statusCode:  Response::HTTP_CREATED)->toJsonResponse();
        }
        catch(RessourceNotFound $e){
            return ApiResponse::error(message: 'Company not found', statusCode:  Response::HTTP_NOT_FOUND )->toJsonResponse();
        }   
        catch(\Exception $e){
            return ApiResponse::error(message: "Something went wrong", statusCode: Response::HTTP_BAD_REQUEST)->toJsonResponse();
        }
    }


    /**
     * PATCH /api/departments/{id}/toggle-status
     * Deactivate/Archive a department instead of deleting it
     */
    #[Route('/{id}/toggle-status', methods: ['PATCH'])]
    public function toggleStatus(int $id): JsonResponse
    {
        $department = $this->departmentRepository->get($id);
        if (!$department) {
            return ApiResponse::error(
                message: 'Department not found',
                statusCode:Response::HTTP_NOT_FOUND
            )->toJsonResponse();
        }

        // Toggle status (Soft Disable)
        $department->setIsActive(!$department->isActive());
        $this->departmentRepository->save($department);
        return ApiResponse::success(
            data: [
                'id' => $department->id(),
                'isActive' => $department->isActive()
            ],
            statusCode:  Response::HTTP_OK
        )->toJsonResponse();
    }
}