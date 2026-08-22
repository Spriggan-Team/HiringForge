<?php

namespace App\Domain\Department;


use App\Domain\Company\Company;
use App\Domain\Department\Department;
use App\Domain\Department\DepartmentRepositoryInterface;


class DepartmentInitializerService
{
    private const DEFAULT_DEPARTMENTS = [
        [
            'label' => 'Ingénierie & Tech',
            'code'  => 'ENG',
            'description' => 'Développement logiciel, infrastructure, QA et sécurité.',
            'children' => [
                ['label' => 'Développement Backend', 'code' => 'ENG-BACK'],
                ['label' => 'Développement Frontend', 'code' => 'ENG-FRONT'],
                ['label' => 'DevOps & Infra', 'code' => 'ENG-OPS'],
            ]
        ],
        [
            'label' => 'Ventes & Business Development',
            'code'  => 'SALES',
            'description' => 'Équipes commerciales, grands comptes et avant-vente.',
        ],
        [
            'label' => 'Marketing & Communication',
            'code'  => 'MKT',
            'description' => 'Inbound marketing, branding, événementiel et growth.',
        ],
        [
            'label' => 'Ressources Humaines & Talent',
            'code'  => 'HR',
            'description' => 'Recrutement, gestion des talents, paie et culture d\'entreprise.',
        ],
        [
            'label' => 'Produit & Design',
            'code'  => 'PROD',
            'description' => 'Product Management, UX/UI Design et recherche utilisateur.',
        ],
        [
            'label' => 'Finance & Administration',
            'code'  => 'FIN',
            'description' => 'Comptabilité, contrôle de gestion, juridique et achats.',
        ],
        [
            'label' => 'Opérations & Support Client',
            'code'  => 'OPS',
            'description' => 'Customer Success, support technique et logistique.',
        ],
    ];

    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository
    ) {}

    public function initForCompany(Company $company): void
    {
        foreach (self::DEFAULT_DEPARTMENTS as $data) {
            $parentDepartment = Department::create(
                label: $data['label'],
                code: $data['code'],
                companyId: $company->id(),
                description: $data['description'] ?? null
            );

            $this->departmentRepository->save($parentDepartment);

            if (!empty($data['children'])) {
                foreach ($data['children'] as $childData) {
                    $childDepartment = Department::create(
                        label: $childData['label'],
                        code: $childData['code'],
                        companyId: $company->id(),
                        parent: $parentDepartment
                    );

                    $this->departmentRepository->save($childDepartment);
                }
            }
        }
    }
}