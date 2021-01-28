<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\AddWishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    /**
     * @Route("/wishes", name="wish_list")
     */
    public function list(WishRepository $repo): Response
    {
        $wishes = $repo->findBy(
            ["isPublished" => true], //clauses where
            ["dateCreated" => "DESC"] //order by
        );
        return $this->render('wish/list.html.twig', ["wishes" => $wishes]);
    }

    /**
     * @Route("/wishes/detail/{id}", name="wish_detail")
     */
    public function detail($id, WishRepository $wishRepository): Response
    {
        $wish=$wishRepository->find($id);

        if (!$wish){
            //alors on déclenche une 404
            throw $this->createNotFoundException('This wish is gone.');
        }



        return $this->render('wish/detail.html.twig', [
            //passe l'id présent dans l'URL à twig
            "wish_id" => $id,
            "wish" => $wish
        ]);
    }

    /**
     * @Route("/wishes/create", name="wish_add")
     */
    public function create(Request $request, EntityManagerInterface $entityManager) : Response
    {
        $wish = new Wish();

        $form =$this->createForm(AddWishType::class, $wish);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $wish->setDateCreated(new \DateTime());
            $wish->setIsPublished(true);

            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Votre voeu a bien a bien été jeté.');

            return $this->redirectToRoute('wish_detail' , ["id" => $wish->getId()]);
        }

        return $this->render('wish/add_wish.html.twig', ["wish_form" => $form->createView()]);
    }


}
