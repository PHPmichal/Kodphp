<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use AppBundle\EventDispatcher\AuctionEvent;
use AppBundle\EventDispatcher\Events;
use AppBundle\Form\AuctionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyAuctionController extends Controller
{
    /**
     * @Route("/my", name="my_auction_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager
            ->getRepository(Auction::class)
            ->findMyOrdered($this->getUser());

        return $this->render("MyAuction/index.html.twig", ["auctions" => $auctions]);
    }

    /**
     * @Route("/my/auction/details/{id}", name="my_auction_details")
     *
     * @param Auction $auction
     *
     * @return Response
     */
    public function detailsAction(Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($auction->getStatus() === Auction::STATUS_FINISHED) {
            return $this->render("MyAuction/finished.html.twig", ["auction" => $auction]);
        }

        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("my_auction_delete", ["id" => $auction->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->add("submit", SubmitType::class, ["label" => "Delete"])
            ->getForm();

        $finishForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("my_auction_finish", ["id" => $auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Finish"])
            ->getForm();

        return $this->render(
            "MyAuction/details.html.twig",
            [
                "auction" => $auction,
                "deleteForm" => $deleteForm->createView(),
                "finishForm" => $finishForm->createView(),
            ]
        );
    }

    /**
     * @Route("/my/auction/add", name="my_auction_add")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction);

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            if ($auction->getStartingPrice() >= $auction->getPrice()) {
                $form->get("startingPrice")->addError(new FormError("the price of the caller must be less than buy now"));
            }

            if ($form->isValid()) {
                $auction
                    ->setStatus(Auction::STATUS_ACTIVE)
                    ->setOwner($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($auction);
                $entityManager->flush();

                $this->get("event_dispatcher")->dispatch(Events::AUCTION_ADD, new AuctionEvent($auction));

                $this->addFlash("success", "Auction {$auction->getTitle()} is added.");

                return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
            }

            $this->addFlash("error", "an error occured!");
        }

        return $this->render("MyAuction/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/my/auction/edit/{id}", name="my_auction_edit")
     *
     * @param Request $request
     * @param Auction $auction
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AuctionType::class, $auction);

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            $this->get("event_dispatcher")->dispatch(Events::AUCTION_EDIT, new AuctionEvent($auction));

            $this->addFlash("success", "Auction {$auction->getTitle()} is realised");

            return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
        }

        return $this->render("MyAuction/edit.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/my/auction/delete/{id}", name="my_auction_delete", methods={"DELETE"})
     *
     * @param Auction $auction
     *
     * @return RedirectResponse
     */
    public function deleteAction(Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush();

        $this->get("event_dispatcher")->dispatch(Events::AUCTION_DELETE, new AuctionEvent($auction));

        $this->addFlash("success", "Auction {$auction->getTitle()} is delete");

        return $this->redirectToRoute("my_auction_index");
    }

    /**
     * @Route("/my/auction/finish/{id}", name="my_auction_finish", methods={"POST"})
     *
     * @param Auction $auction
     *
     * @return RedirectResponse
     */
    public function finishAction(Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $auction
            ->setExpiresAt(new \DateTime())
            ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->get("event_dispatcher")->dispatch(Events::AUCTION_FINISH, new AuctionEvent($auction));

        $this->addFlash("success", "Auction {$auction->getTitle()} is finish");

        return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
    }
}
