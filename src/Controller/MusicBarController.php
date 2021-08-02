<?php
declare(strict_types=1);

namespace App\Controller;

use App\Enum\ErrorCodeEnum;
use App\Enum\VisitorStatusEnum;
use App\Repository\BarRepository;
use App\Repository\VisitorRepository;
use App\Validator\BarValidator;
use App\Validator\GeneralValidator;
use App\Validator\VisitorValidator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MusicBarController extends AbstractController
{
    private BarRepository $barRepository;
    private VisitorRepository $visitorRepository;
    private SerializerInterface $serializer;
    private VisitorValidator $visitorValidator;
    private BarValidator $barValidator;

    public function __construct(
        BarRepository $barRepository,
        VisitorRepository $visitorRepository,
        SerializerInterface $serializer,
        VisitorValidator $visitorValidator,
        BarValidator $barValidator
    )
    {
        $this->barRepository = $barRepository;
        $this->visitorRepository = $visitorRepository;
        $this->serializer = $serializer;
        $this->visitorValidator = $visitorValidator;
        $this->barValidator = $barValidator;
    }

    /**
     * @Route("/music/bar/open", name="open_music_bar", methods={"POST"})
     */
    public function openMusicBar(Request $request): Response
    {
        $body = json_decode((string)$request->getContent(), true);
        $this->barValidator->checkBeforeCreation($body);
        if ($this->barRepository->barExists($body)) {
            $existedBar = $this->barRepository->returnBarByTitle($body['title']);
            $bar = $this->barRepository->openBar($existedBar);
        } else {
            $bar = $this->barRepository->create($body);
        }

        return new JsonResponse(
            [
                'bar_id' => $bar->getId(),
                'bar_title' => $bar->getTitle(),
                'status' => $bar->getStatus()
            ]
        );
    }

    /**
     * @Route("/music/bars", name="show_all_bars", methods={"GET"})
     */
    public function getAllBars(): Response
    {
        $bars = $this->barRepository->getBarsArray();

        $barsJson = $this->serializer->serialize($bars, 'json');

        return new JsonResponse(json_decode($barsJson, true));
    }

    /**
     * @Route("/music/bar/{title}", name="find_bar_by_title", methods={"GET"})
     */
    public function getBarByName(string $title): Response
    {
        $bar = $this->barRepository->returnBarByTitle($title);

        $barJson = $this->serializer->serialize($bar, 'json');

        return new JsonResponse(json_decode($barJson, true));
    }

    /**
     * @Route("/music/bar/{id}", name="update_bar_by_id", methods={"PUT"})
     */
    public function updateBarByID(Request $request, int $id): Response
    {
        $body = json_decode((string)$request->getContent(), true);

        $updatedBar = $this->barRepository->updateByID($id, $body);

        $barJson = $this->serializer->serialize($updatedBar, 'json');

        return new JsonResponse(json_decode($barJson, true));
    }

    /**
     * @Route("/music/bar/id/{id}", name="delete_bar_by_id", methods={"DELETE"})
     */
    public function deleteBarByID(Request $request, int $id): Response
    {
        $this->barRepository->deleteByID($id);

        return new JsonResponse(
            [
                'message' => sprintf(
                    'Bar with id `%d` deleted',
                    $id
                )
            ]
        );
    }

    /**
     * @Route("/music/bar/{title}/WhatIshappening", name="do_bar_stuff", methods={"POST"})
     */
    public function doBarStuff(string $title): Response
    {
        $this->barRepository->visitorsProcessing($title, $this->visitorRepository);
        return new JsonResponse(
            [
                'message' => "Visitors processing has just finished"
            ]
        );
    }

    /**
     * @Route("/music/bar/close/{title}", name="close_music_bar", methods={"POST"})
     */
    public function closeMusicBar(string $title): Response
    {
        $bar = $this->barRepository->returnBarByTitle($title);
        $closedBar = $this->barRepository->closeBar($bar);

        return new JsonResponse(
            [
                'bar_id' => $closedBar->getId(),
                'bar_title' => $closedBar->getTitle(),
                'status' => $closedBar->getStatus(),
                'amountOfVisitors' => $closedBar->getAmountOfVisitors()
            ]
        );
    }

    /**
     * @Route("/music/bar/{barTitle}/visitors", name="create_new_visitors", methods={"POST"})
     */
    public function newVisitors(Request $request, string $barTitle): Response
    {
        $bar = $this->barRepository->returnBarByTitle($barTitle);

        if (!$this->barRepository->barOpened($bar)) {
            throw new RuntimeException(
                sprintf(
                    'Bar `%s` is closed',
                    $bar->getTitle()
                ),
                ErrorCodeEnum::CREATION_FAILED
            );
        }

        $body = json_decode((string)$request->getContent(), true);

        $this->visitorValidator->checkBeforeCreation($body);

        $visitors = $body['visitors'];
        $visitorsId = [];
        foreach ($visitors as $visitor) {
            $newVisitor = $this->visitorRepository->create($visitor);
            $visitorsId[] = $newVisitor->getId();
            $this->barRepository->faceControl($bar, $newVisitor, $this->visitorRepository);
        }

        return new JsonResponse(
            [
                'visitor(s)_id' => $visitorsId,
                'status' => sprintf(
                    'came to the `%s`',
                    $barTitle
                )
            ]
        );
    }

    /**
     * @Route("/music/bar/visitors/all", name="return_all_visitors", methods={"GET"})
     */
    public function getAllVisitors(): Response
    {
        $visitors = $this->visitorRepository->getVisitorsArray();

        $visitorsJson = $this->serializer->serialize($visitors, 'json');

        return new JsonResponse(json_decode($visitorsJson, true));
    }

    /**
     * @Route("/music/bar/allVisitors", name="delete_all_visitors", methods={"DELETE"})
     */
    public function deleteAllVisitors(): Response
    {
        $this->visitorRepository->deleteAll();

        return new JsonResponse(
            [
                'message' => "all visitors deleted"
            ]
        );
    }
}
