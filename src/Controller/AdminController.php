<?php

namespace App\Controller;

use App\Entity\Gif;
use App\Entity\GifState;
use App\Entity\ReportState;
use App\Repository\GifRepository;
use App\Service\GifDownloaderService;
use App\Service\GifService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GifService $gifService,
        private readonly GifDownloaderService $gifDownloader,
        private readonly MailService $mailService,
    )
    {
    }

    private const GIFS_PER_PAGE = 9;

    private function getQueryBuilderByType(string $type)
    {
        /** @var GifRepository $gifRepo */
        $gifRepo = $this->em->getRepository(Gif::class);

        switch ($type) {
            case 'submitted':
            case 'accepted':
            case 'refused':
            case 'published':
                $gifState = GifState::fromName($type);
                return $gifRepo->findByGifState_queryBuilder($gifState);
            case 'reported':
                return $gifRepo->getReportedGifs_queryBuilder();
            default:
                throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/admin/api", name="adminApi", methods={"POST"})
     */
    public function adminApiAction(Request $request)
    {
        $post = $request->request;

        // Request integrity check
        if (!$post->has('api_key')) {
            return self::apiError('missing_api_key');
        }

        if (!$this->checkApiKey($post->get('api_key'))) {
            return self::apiError('wrong_api_key');
        }

        if (!$post->has('action')) {
            return self::apiError('missing_action');
        }

        /** @var GifRepository $gifRepo */
        $gifRepo = $this->em->getRepository(Gif::class);

        // Result array returned to client
        $result = [];

        switch ($post->get('action')) {
            case 'change_gif_status':
                $check = self::checkParameters($post, ['gif_id', 'new_gif_state', 'caption']);
                if ($check !== true) {
                    self::apiError($check);
                }

                /** @var Gif $gif */
                $gif = $gifRepo->find($post->get('gif_id'));

                if (!$gif) {
                    self::apiError('unknown_gif');
                }

                $caption = $post->get('caption');
                $gifState = GifState::fromName($post->get('new_gif_state'));

                if ($gifState == -1) {
                    return new JsonResponse(['success' => false]);
                }

                $gif->setCaption($caption);
                $gif->setGifStatus($gifState);
                // Regenerate permalink in case of caption changed
                $gif->generateUrlReadyPermalink();

                $this->em->flush();

                // Post-update actions
                switch ($gifState) {
                    case GifState::ACCEPTED:
                        if ($gif->getEmail() != null) {
                            $this->mailService->sendGifApprovedMail($gif);
                        }
                        break;
                    case GifState::PUBLISHED:
                        if ($gifState == GifState::PUBLISHED) {
                            $this->gifService->publish($gif);
                        }
                        break;
                }

                break;
            case 'change_report_status':
                $check = self::checkParameters($post, ['gif_id']);
                if ($check !== true) {
                    self::apiError($check);
                }

                /** @var Gif $gif */
                $gif = $gifRepo->find($post->get('gif_id'));

                if (!$gif) {
                    self::apiError('unknown_gif');
                }

                $gif->setReportStatus(ReportState::IGNORED);
                $this->em->flush();
                break;
            case 'delete_gif':
                $check = self::checkParameters($post, ['gif_id']);
                if ($check !== true) {
                    self::apiError($check);
                }

                /** @var Gif $gif */
                $gif = $gifRepo->find($post->get('gif_id'));

                if (!$gif) {
                    self::apiError('unknown_gif');
                }

                // Delete downloaded gif if there is one
                if ($gif->getOriginalGifUrl() != null) {
                    $this->gifDownloader->delete($gif);
                }

                $this->em->remove($gif);
                $this->em->flush();
                break;
            case 'download_gif':
                $check = self::checkParameters($post, ['gif_id']);
                if ($check !== true) {
                    self::apiError($check);
                }

                /** @var Gif $gif */
                $gif = $gifRepo->find($post->get('gif_id'));

                if (!$gif) {
                    self::apiError('unknown_gif');
                }

                $res = $this->gifDownloader->download($gif);

                if ($res !== false) {
                    $this->em->flush();

                    $result['gifUrl'] = $res;
                } else {
                    self::apiError('download_failed');
                }
                break;
            default:
                return self::apiError('unknown_action');
        }

        return new JsonResponse(array_merge($result, ['success' => true]));
    }

    private static function checkParameters(ParameterBag $post, $params)
    {
        foreach ($params as $param) {
            if (!array_key_exists($param, $post->all())) {
                return 'missing_parameter('.$param.')';
            }
        }
        return true;
    }

    private function checkApiKey(?string $apiKey): bool
    {
        return $apiKey === $this->getParameter('admin_api_key');
    }

    private static function apiError($error): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $error
        ], 500);
    }

    /**
     * @Route("/admin/{type}/{page}", name="admin")
     * @Route("/admin/")
     */
    public function adminAction(PaginatorInterface $paginator, $type = 'submitted', $page = 1)
    {
        $queryBuilder = $this->getQueryBuilderByType($type);

        // Prepare counts
        $counts = [];
        foreach (GifState::getAll() as $gifType) {
            $query = $this->getQueryBuilderByType($gifType)->getQuery();
            $query->execute();

            $counts[$gifType] = count($query->getResult());
        }

        // Prepare pagination
        $page = (int) $page;
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $page,
            self::GIFS_PER_PAGE
        );
        $pagination->setUsedRoute('admin');

        $params = [
            'gifs' => $pagination,
            'page' => $page,
            'type' => $type,
            'typeLabel' => GifState::getLabel($type),
            'counts' => $counts,
            'admin_api_key' => $this->getParameter('admin_api_key')
        ];

        return $this->render('/Admin/index.html.twig', $params);
    }
}
