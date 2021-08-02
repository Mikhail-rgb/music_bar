<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bar;
use App\Entity\Visitor;
use App\Enum\BarStatusEnum;
use App\Enum\ErrorCodeEnum;
use App\Enum\VisitorStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method Bar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bar[]    findAll()
 * @method Bar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BarRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bar::class);
    }

    // /**
    //  * @return Bar[] Returns an array of Bar objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Bar
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function create(array $body): Bar
    {
        $bar = new Bar();

        $bar->setTitle($body['title']);
        $bar->setCapacity($body['capacity']);
        $bar->setAmountOfBartenders(1);
        $bar->setAmountOfVisitors(0);
        $bar->setRepertoire($body['repertoire']);
        $bar->setVisitors(null);
        $bar->setStatus('open');

        $this->save($bar);

        return $bar;
    }

    public function closeBar(Bar $bar): Bar
    {
        $status = $bar->getStatus();

        if ($status === BarStatusEnum::BAR_CLOSED) {
            throw new RuntimeException(
                sprintf(
                    'Bar `%s` is already closed',
                    $bar->getTitle()
                ),
                ErrorCodeEnum::CANNOT_CLOSE_BAR
            );
        }

        $bar->setStatus(BarStatusEnum::BAR_CLOSED);
        $bar->askVisitorsToLeave();
        $bar->setCurrentGenre("");
        $this->save($bar);

        return $bar;
    }


    private function save(Bar $bar): void
    {
        $this->_em->persist($bar);
        $this->_em->flush();
    }

    /**
     * @return Bar[]
     */
    public function getBarsArray(): array
    {
        $bars = $this->findAll();

        if (!$bars) {
            throw new RuntimeException(
                'Cannot find any bar',
                ErrorCodeEnum::NOT_FOUND
            );
        }

        return $bars;
    }

    public function newVisitor(): void
    {

    }

    public function returnBarByTitle(string $title): Bar
    {
        $bar = $this->findOneBy(['title' => $title]);

        if (!$bar) {
            throw new RuntimeException(
                sprintf(
                    'Bar with title `%s` not found',
                    $title
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        return $bar;

    }

    public function barExists(array $body): bool
    {
        $title = $body['title'];
        $bar = $this->findOneBy(['title' => $title]);
        if (!$bar) {
            return false;
        } else {
            return true;
        }
    }

    public function openBar(Bar $bar): Bar
    {
        $status = $bar->getStatus();

        if ($status === BarStatusEnum::BAR_OPENED) {
            throw new RuntimeException(
                sprintf(
                    'Bar `%s` is already opened',
                    $bar->getTitle()
                ),
                ErrorCodeEnum::CANNOT_OPEN_BAR
            );
        }

        $bar->setStatus(BarStatusEnum::BAR_OPENED);
        $this->save($bar);

        return $bar;
    }

    public function barOpened(Bar $bar): bool
    {
        if ($bar->getStatus() === BarStatusEnum::BAR_CLOSED) {
            return false;
        }

        return true;
    }

    public function faceControl(Bar $bar, Visitor $visitor, VisitorRepository &$visitorRepository): void
    {
        $this->visitorEntered($bar, $visitor, $visitorRepository);
        $this->save($bar);
    }

    private function visitorEntered(Bar $bar, Visitor $visitor, VisitorRepository &$visitorRepository): void
    {
        $visitorsCounter = $bar->getAmountOfVisitors();

        $id = $visitor->getId();
        $title = $bar->getTitle();

        if ($visitorsCounter >= $bar->getCapacity()) {
            $visitor->setStatus(VisitorStatusEnum::GONE);

            echo "Visitor with id `$id` gone because there are no more places at the `$title`. \n";
            //(new VisitorRepository)->fastUpdate($visitor);
            /*throw new RuntimeException(
                sprintf(
                    'No places for new guests at the `%s`. Maximum amount of guests: %d.',
                    $bar->getTitle(),
                    $bar->getCapacity()
                ),
                ErrorCodeEnum::BAR_IS_FULL
            );*/
        } else {
            //(new VisitorRepository)->fastUpdate($visitor);
            $bar->oneMoreVisitor($visitor);
            echo "Visitor with id `$id` entered the `$title`. \n";
            $visitor->setStatus(VisitorStatusEnum::ENTERED);
        }

        $visitorRepository->updateVisitorInDB($visitor);
    }

    public function visitorsProcessing(string $title, VisitorRepository &$visitorRepository): void
    {
        $bar = $this->findOneBy(['title' => $title]);

        if (!$bar) {
            throw new RuntimeException(
                sprintf(
                    'Bar with title `%s` not found',
                    $title
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        $visitors = $bar->getVisitors();

        if (!$visitors) {
            throw new RuntimeException(
                sprintf(
                    'There are no visitors at the `%s`',
                    $title
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        $bar = $this->checkVisitorsForMusic($bar, $visitors, $visitorRepository);
        $musicPrice = 50;
        $drinkPrice = 100;
        foreach ($visitors as $visitor) {
            $bar = $this->switchOnVisitorsMusic($bar, $visitor, $visitorRepository, $musicPrice);
            $this->actionAtTheBar($bar, $visitors, $visitorRepository);
            $bar = $this->checkVisitorsForDrink($bar, $visitors, $visitorRepository, $drinkPrice);
        }
    }

    /**
     * @param Bar $bar
     * @param array $visitors
     * @param VisitorRepository $visitorRepository
     * @return Bar
     */
    private function checkVisitorsForMusic(Bar $bar, array $visitors, VisitorRepository &$visitorRepository): Bar
    {
        $berRepertoire = $bar->getRepertoire();

        foreach ($visitors as &$visitor) {
            $visitorsGenre = $visitor->getGenre();

            $id = $visitor->getId();
            $title = $bar->getTitle();

            if (!in_array($visitorsGenre, $berRepertoire)) {
                $visitor->setStatus(VisitorStatusEnum::GONE);

                echo "Visitor with id `$id` gone because the `$title` hasn't his favorite genre. \n";
                $bar->oneLessVisitor($visitor->getId());
                $this->save($bar);
                /*throw new RuntimeException(
                    sprintf(
                        'Unexpected genre `%s`. Need one of this genres: %s',
                        $visitorsGenre,
                        print_r($berRepertoire, true)
                    ),
                    ErrorCodeEnum::UNEXPECTED_PROPERTY
                );*/
            } else {
                $visitor->setStatus(VisitorStatusEnum::WANTS_MUSIC);
                echo "Visitor with id `$id` got in line to order the music. \n";
            }

            $visitorRepository->updateVisitorInDB($visitor);
        }

        return $bar;
    }

    private function switchOnVisitorsMusic(
        Bar $bar,
        Visitor &$visitor,
        VisitorRepository &$visitorRepository,
        int $musicPrice
    ): Bar
    {

        if ($bar->getCurrentGenre()) {
            if ($visitor->getGenre() === $bar->getCurrentGenre()) {
                $visitor->setStatus(VisitorStatusEnum::DANCING);
                $visitorRepository->updateVisitorInDB($visitor);
            }
        }

        $bar = $this->service(
            $bar,
            $visitor,
            $visitorRepository,
            $musicPrice,
            VisitorStatusEnum::WANTS_MUSIC,
            VisitorStatusEnum::DANCING,
            VisitorStatusEnum::GONE
        );

        if ($visitor->getStatus() === VisitorStatusEnum::DANCING) {
            $genre = $visitor->getGenre();
            $bar->setCurrentGenre($genre);
            $id = $visitor->getId();
            echo "Now playing the songs in genre `$genre`. And Visitor with id `$id` is going to dance. \n";
        }

        $this->save($bar);

        return $bar;
    }

    private function visitorHasMoney(int $money): bool
    {
        if ($money <= 0) {
            return false;
        }

        return true;
    }

    private function askVisitorForPayment(Visitor $visitor, int $price): bool
    {
        $visitorsMoney = $visitor->getMoney();

        if ($visitorsMoney < $price) {
            return false;
        }

        $visitorsMoney -= $price;
        $visitor->setMoney($visitorsMoney);

        return true;
    }

    private function checkVisitorsForDrink(
        Bar $bar,
        array $visitors,
        VisitorRepository &$visitorRepository,
        int $drinkPrice
    ): Bar
    {
        foreach ($visitors as &$visitor) {
            $bar = $this->service(
                $bar,
                $visitor,
                $visitorRepository,
                $drinkPrice,
                VisitorStatusEnum::WANTS_DRINK,
                VisitorStatusEnum::DRINKING,
                VisitorStatusEnum::GONE
            );
            $this->save($bar);
        }

        return $bar;
    }

    private function actionAtTheBar(Bar $bar, array &$visitors, VisitorRepository &$visitorRepository): void
    {
        static $previousGenre = null;
        $currentGenre = $bar->getCurrentGenre();
        /*
         * If playing genre at the bar has changed then:
         *  - visitors who was dancing going to order drink;
         *  - visitors who was drinking and waiting for music or drink
         * going to dance, if it's their lovely genre is playing right now,
         * if it isn't their lovely genre they are still doing the same what they do;
         *  - visitors who was drinking, and changed genre isn't their favorite one,
         * they are going to order new drink.
        */
        if ($currentGenre) {
            if ($previousGenre && $previousGenre !== $currentGenre) {
                echo "Genre of music changed from `$previousGenre` to `$currentGenre`. \n";
                foreach ($visitors as &$visitor) {
                    $id = $visitor->getId();
                    switch ($visitor->getStatus()) {
                        case VisitorStatusEnum::DANCING:
                            if ($visitor->getGenre() !== $currentGenre) {
                                $visitor->setStatus(VisitorStatusEnum::WANTS_DRINK);
                                $visitorRepository->updateVisitorInDB($visitor);
                                echo "Visitor with id `$id` stopped dancing and went to order some drink. \n";
                            } else {
                                echo "Visitor with id `$id` continues to dance. \n";
                            }
                            break;
                        case VisitorStatusEnum::DRINKING:
                            if ($visitor->getGenre() === $currentGenre) {
                                $visitor->setStatus(VisitorStatusEnum::DANCING);
                                echo "Visitor with id `$id` stopped drinking and went to dance. \n";
                            } else {
                                $visitor->setStatus(VisitorStatusEnum::WANTS_DRINK);
                                echo "Visitor with id `$id` continues to drink. \n";
                            }
                            $visitorRepository->updateVisitorInDB($visitor);
                            break;
                        case VisitorStatusEnum::WANTS_MUSIC:
                        case VisitorStatusEnum::WANTS_DRINK:
                            if ($visitor->getGenre() === $currentGenre) {
                                $visitor->setStatus(VisitorStatusEnum::DANCING);
                                $visitorRepository->updateVisitorInDB($visitor);
                                echo "Visitor with id `$id` get out of line to order something and going to dance. \n";
                            } else {
                                echo "Visitor with id `$id` continues to be in line to order drink or music. \n";
                            }
                            break;
                    }
                }
            }
            $previousGenre = $currentGenre;
        }
    }

    private function payment(
        Visitor $visitor,
        int $servicePrice,
        string $statusIfPaid,
        string $statusIfNotPaid
    ): Visitor
    {
        $id = $visitor->getId();
        if ($this->visitorHasMoney($visitor->getMoney())) {
            if ($this->askVisitorForPayment($visitor, $servicePrice)) {
                $visitor->setStatus($statusIfPaid);
                echo "Visitor with id `$id` get paid for music or drink. \n";
            } else {
                $visitor->setStatus($statusIfNotPaid);
                echo "Visitor with id `$id` gone because he hasn't enough money to order the music or drink. \n";
            }
        } else {
            $visitor->setStatus($statusIfNotPaid);
            echo "Visitor with id `$id` gone because he hasn't money. \n";
        }

        return $visitor;
    }

    private function service(
        Bar $bar,
        Visitor &$visitor,
        VisitorRepository &$visitorRepository,
        int $servicePrice,
        string $statusForChecking,
        string $statusIfPaid,
        string $statusIfNotPaid
    ): Bar
    {
        if ($visitor->getStatus() === $statusForChecking) {

            $visitor = $this->payment(
                $visitor,
                $servicePrice,
                $statusIfPaid,
                $statusIfNotPaid
            );

            $visitorRepository->updateVisitorInDB($visitor);

            if ($visitor->getStatus() === VisitorStatusEnum::GONE) {
                $bar->oneLessVisitor($visitor->getId());
            }

        }

        return $bar;
    }

    public function updateByID(int $id, array $body): Bar
    {
        $bar = $this->find($id);

        if (!$bar) {
            throw new RuntimeException(
                sprintf(
                    'Cannot find bar with id `%d`',
                    $id
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        return $this->update($bar, $body);
    }

    private function update(Bar $bar, array $body): Bar
    {
        foreach ($body as $key => $value) {
            switch ($key) {
                case "title":
                    $bar->setTitle($value);
                    break;
                case "capacity":
                    $bar->setCapacity($value);
                    break;
                case "amountOfBartenders":
                    $bar->setAmountOfBartenders($value);
                    break;
                case "status":
                    $bar->setStatus($value);
                    break;
                case "repertoire":
                    $bar->setRepertoire($value);
                    break;
                case "amountOfVisitors":
                    $bar->setAmountOfVisitors($value);
                    break;
                case "currentGenre":
                    $bar->setCurrentGenre($value);
                    break;
                default:
                    throw new RuntimeException(
                        sprintf(
                            'Unknown property `%s`. Need one of this: `title`, `capacity`, `amountOfBartenders`, 
                            `status`, `repertoire`, `amountOfVisitors`, `currentGenre`',
                            $key
                        )
                    );
            }
        }

        $this->save($bar);

        return $bar;
    }

    public function deleteByID(int $id): void
    {
        $bar = $this->find($id);

        if (!$bar) {
            throw new RuntimeException(
                sprintf(
                    'Cannot find bar with id `%d`',
                    $id
                ),
                ErrorCodeEnum::NOT_FOUND
            );
        }

        $this->delete($bar);
    }

    private function delete(Bar $bar)
    {
        $this->_em->remove($bar);
        $this->_em->flush();
    }


}
