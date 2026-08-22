<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\CandidateSkillRepositoryInterface;
use App\Domain\Shared\Skill\BasicSkillModel;
use App\Domain\Shared\Skill\SkillMatchMethod;
use App\Domain\Shared\Skill\SkillResolution;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateSkillsEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Override;


class CandidateSkillRepository 
    extends ServiceEntityRepository
    implements CandidateSkillRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registery
    ){
        parent::__construct($registery, CandidateSkillsEntity::class);
    }

    #[Override]
    public function link(
        string $candidateId,
        string $skillId,
        ?string $candidateResumeId = null
    ): void
    {
        $em = $this->getEntityManager();
        $candidate = $em->getReference(CandidateEntity::class, $candidateId);
        $skill = $em->getReference(SkillEntity::class, $skillId);

        $candidateResume = null;
        if($candidateResumeId){
            $candidateResume = $em->getReference(CandidateResumeEntity::class, $candidateResumeId);
        }

        $candidateSkill = CandidateSkillsEntity::create(
            candidate: $candidate,
            skill: $skill,
            candidateResume: $candidateResume
        );

        $em->persist($candidateSkill);
        $em->flush();
    }



    #[Override]
    public function hasSkill(string $candidateId, string $skillId): bool
    {
        $result = $this->createQueryBuilder('cs')
            ->select('cs.id')
            ->where('cs.candidate = :candidateId')
            ->andWhere('cs.skill = :skillId')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('skillId', $skillId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }


    #[Override]
    public function resolveSkill(
        string $candidateId,
        string $text,
        string $locale = 'fr',
    ): ?SkillResolution {
        //  Exact match : name / slug
        $result = $this->createQueryBuilder('cs')
            ->select('s.id AS skillId')
            ->innerJoin('cs.skill', 's')
            ->leftJoin('s.translations', 'st')
            ->where('cs.candidate = :candidateId')
            ->andWhere(
                'LOWER(st.name) = LOWER(:text)
                OR LOWER(st.slug) = LOWER(:text)'
            )
            ->andWhere('st.locale = :locale')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('text', $text)
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result !== null) {
            return new SkillResolution(
                skillId: (string) $result['skillId'],
                method: SkillMatchMethod::EXACT,
            );
        }

        // 2. Alias match
        $result = $this->createQueryBuilder('cs')
            ->select('s.id AS skillId')
            ->innerJoin('cs.skill', 's')
            ->innerJoin('s.skillAliases', 'sa')
            ->where('cs.candidate = :candidateId')
            ->andWhere('LOWER(sa.alias) = LOWER(:text)')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('text', $text)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result !== null) {
            return new SkillResolution(
                skillId: (string) $result['skillId'],
                method: SkillMatchMethod::ALIAS,
            );
        }

        return null;
    }

    #[Override]
    public function getCandidateSkills(string $candidateId): array
    {
        $results = $this->createQueryBuilder('cs')
            ->select('s.id AS id, s.canonicalName AS name')
            ->innerJoin('cs.skill', 's')
            ->where('cs.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $item): BasicSkillModel => new BasicSkillModel(
                id: (string) $item['id'],
                name: (string) $item['name'],
            ),
            $results
        );
    }
}


