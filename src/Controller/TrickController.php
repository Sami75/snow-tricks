<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentsRepository;
use App\Entity\User;
use App\Repository\UserRepository;

use App\Service\UploadedFileManager;
use App\Service\TemporaryFileManager;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * handle all requests related to trick diplay and management
 */
class TrickController extends AbstractController
{
    private $uploadedFile;

    public function __construct(UploadedFileManager $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }
    /**
     * Action : get home page
     * @Route("/", name="trick_index", methods={"GET"})
     */
    public function index(TrickRepository $trickRepository): Response
    {
        return $this->render('trick/index.html.twig', [
            'fixed_menu'=> 'enabled'
        ]);
    }

    /**
     * Action : Create new trick
     * @Route("member/new", name="trick_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setVideoDocs($this->uploadedFile->docsInputManager($form->get('videoDocs')->getData()));
            $trick->setImgDocs($this->uploadedFile->docsInputManager($form->get('imgDocs')->getData()));


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('trick_index');
        }

        return $this->render('member/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Action : display a trick details page
     * @Route("/{slug}-{id}", name="trick_show", methods={"GET","POST"}, requirements= {"slug": "[a-z0-9\-]*"})
     */
    public function show(Trick $trick, Request $request, string $slug): Response
    {
        if ($trick->getSlugName() !== $slug) {
            return $this->redirectToRoute("trick_show", [
            'id' => $trick->getId(),
            'slug' => $trick->getSlugName()
          ], 301);
        }
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setTrick($trick);
            $comment->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $comment = new Comment();
            $form = $this->createForm(CommentType::class, $comment);
        }

        $niveau = Trick::NIVEAU[$trick->getNiveau()];
        $trick_group = Trick::TRICK_GROUP[$trick->getTrickGroup()];

        return $this->render('trick/show.html.twig', [
          'comment'=> $comment,
          'form' => $form->createView(),
          'trick' => $trick,
          'niveau' => $niveau,
          'trick_group' => $trick_group
        ]);
    }


    /**
     * Action : Edit a trick
     * @Route("member/{id}/edit", name="trick_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Trick $trick, TemporaryFileManager $temporaryStorage): Response
    {
        $temporaryStorage->setTempImg($trick->getImgDocs());
        $temporaryStorage->setTempVideo($trick->getVideoDocs());

        $trick->setImgDocs($temporaryStorage->getTempImg());
        $trick->setVideoDocs($temporaryStorage->getTempVideo());

    
        $form = $this->createForm(TrickType::class, $trick);

        if ($request->isMethod('post')) {
            $storedImages = $temporaryStorage->getTempImg();
            $storedVideos = $temporaryStorage->getTempVideo();
        }

        $form->handleRequest($request);

       
        if ($form->isSubmitted() && $form->isValid()) {
            $trick =  $this->validateEdition($form, $trick, 'imgDocs', $storedImages);
            $trick = $this->validateEdition($form, $trick, 'videoDocs', $storedVideos);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('trick_index');
        }


        return $this->render('member/edit.html.twig', [

            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Action : delete a trick
     * @Route("member/{id}/delete", name="trick_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Trick $trick): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trick_index');
    }

    /**
     * Action : ajax request to get or count tricks from the db
     * @Route("/ajax/", name="trick_ajax", methods={"POST"})
     */
    public function ajax(TrickRepository $trickRepository, Request $request)
    {
        if (!$request->request->has('first')) {
            return $this->render('trick/ajax.html.twig', [
          'count' => $trickRepository->countTricks()
        ]);
        } else {
            return $this->render('trick/ajax.html.twig', [

            'tricks' => $trickRepository->loadXtricks($request->request->get('first'), 4),
        ]);
        }
    }


    /**
     * Action : Ajax request: Add comments to trick details page
     * @Route("/new_comments/{id}", name="new_comments", methods={"POST"})
     */
    public function newComments(CommentsRepository $commentRepo, Request $request, Trick $trick)
    {
        if (null !== $request->request->get('first')) {
            return $this->render('trick/comments.html.twig', [

        'comments' => $commentRepo->findComments($trick->getId(), $request->request->get('first'))
            
        ]);
        }
 
        return $this->render('trick/comments.html.twig', [

        'comments' => $commentRepo->findComments($trick->getId()),
        'initial_load' => true
            
        ]);
    }
    /**
     * Action : check if  old files are associated to a trick
     *          before adding new files
     */
    private function validateEdition($form, $trick, $identifier, $array)
    {
        if ($form->get($identifier)->getData() == null && isset($array)) {
            $files2Save = $this->uploadedFile->docsInputManager($array);
            ($identifier == 'imgDocs')? $trick->setImgDocs($files2Save): $trick->setVideoDocs($files2Save);
        } else {
            $docs = $form->get($identifier)->getData();
            $files2Save = $this->uploadedFile->docsInputManager($docs);

            ($identifier == 'imgDocs')? $trick->setImgDocs($files2Save): $trick->setVideoDocs($files2Save);
        }

        return $trick;
    }
}
