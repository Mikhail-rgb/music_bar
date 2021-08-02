<?php
declare(strict_types=1);

namespace App\Entity;

use App\Enum\ErrorCodeEnum;
use App\Repository\BarRepository;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

/**
 * @ORM\Entity(repositoryClass=BarRepository::class)
 */
class Bar
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="integer")
     */
    private int $capacity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $amountOfVisitors;

    /**
     * @ORM\Column(type="integer")
     */
    private int $amountOfBartenders;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $currentGenre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $status;

    /**
     * @ORM\Column(type="array")
     */
    private array $repertoire = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $visitors = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getAmountOfVisitors(): int
    {
        if($this->amountOfVisitors === null)
            return 0;

        return $this->amountOfVisitors;
    }

    public function setAmountOfVisitors(int $amountOfVisitors): void
    {
        $this->amountOfVisitors = $amountOfVisitors;
    }

    public function getAmountOfBartenders(): int
    {
        return $this->amountOfBartenders;
    }

    public function setAmountOfBartenders(int $amountOfBartenders): void
    {
        $this->amountOfBartenders = $amountOfBartenders;

    }

    public function getCurrentGenre(): string
    {
        return $this->currentGenre;
    }

    public function setCurrentGenre(string $currentGenre): void
    {
        $this->currentGenre = $currentGenre;

    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function oneMoreVisitor(Visitor $visitor): void
    {
        $visitorsCounter = $this->getAmountOfVisitors();

        if ($visitorsCounter === NULL)
            $visitorsCounter = 0;

        $visitorsCounter += 1;
        $this->setAmountOfVisitors($visitorsCounter);

        $this->addVisitor($visitor);
    }

    /**
     * @return array
     */
    public function getRepertoire(): array
    {
        return $this->repertoire;
    }

    public function setRepertoire(array $repertoire): void
    {
        $this->repertoire = $repertoire;
    }

    /**
     * @return array|null
     */
    public function getVisitors(): ?array
    {
        return $this->visitors;
    }

    public function setVisitors(?array $visitors): void
    {
        $this->visitors = $visitors;
    }

    private function addVisitor(Visitor $visitor): void
    {
        $this->visitors[$visitor->getId()] = $visitor;
    }

    public function oneLessVisitor(int $visitorId):void
    {
        unset($this->visitors[$visitorId]);

        $visitorsCounter = $this->getAmountOfVisitors();

        $visitorsCounter -= 1;

        if ($visitorsCounter < 0)
            $visitorsCounter = 0;

        $this->setAmountOfVisitors($visitorsCounter);
    }

    public function askVisitorsToLeave():void
    {
        unset($this->visitors);
        $this->visitors = array();
        $this->setAmountOfVisitors(0);
    }
}
