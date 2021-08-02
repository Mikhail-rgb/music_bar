<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Visitor;
use App\Enum\ErrorCodeEnum;
use App\Enum\VisitorStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method Visitor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visitor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visitor[]    findAll()
 * @method Visitor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visitor::class);
    }


    /**
     * @param array $body
     * @return Visitor
     */
    public function create(array $body): Visitor
    {
        $visitor = new Visitor();

        if (isset($body['name'])) {
            $visitor->setName($body['name']);
        } else {
            $visitor->setName("Unknown");
        }

        if (isset($body['surname'])) {
            $visitor->setSurname($body['surname']);
        } else {
            $surname = "Person ";
            $visitor->setSurname($surname);
        }

        $visitor->setGenre($body['genre']);

        $visitor->setMoney($body['money']);

        $visitor->setStatus(VisitorStatusEnum::CAME);

        $this->save($visitor);

        return $visitor;
    }

    public function updateVisitorInDB(Visitor $visitor)
    {
        $updatedVisitor = $this->updateProperties($visitor);

        $this->save($updatedVisitor);
    }

    private function save(Visitor $visitor): void
    {
        $this->_em->persist($visitor);
        $this->_em->flush();
    }

    /**
     * @return Visitor[]
     */
    public function getVisitorsArray(): array
    {
        $visitors = $this->findAll();

        if (!$visitors) {
            throw new RuntimeException(
                'can`t find any visitor',
                ErrorCodeEnum::NOT_FOUND
            );
        }

        return $visitors;
    }

    public function deleteAll(): void
    {
        $visitors = $this->findAll();

        foreach ($visitors as $visitor) {
            $this->delete($visitor);
        }
    }

    private function delete(Visitor $visitor): void
    {
        $this->_em->remove($visitor);
        $this->_em->flush();
    }

    private function updateProperties(Visitor $visitor): Visitor
    {
        $newVisitor = $this->find($visitor->getId());

        if (!$newVisitor) {
            throw new RuntimeException(
                sprintf(
                    'Can`t find visitor with id `%d` in database',
                    $visitor->getId()
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        $newVisitor->setStatus($visitor->getStatus());
        $newVisitor->setMoney($visitor->getMoney());

        return $newVisitor;
    }
}
