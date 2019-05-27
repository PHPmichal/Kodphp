<?php

//Tylko dla zalogowanych userów

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
    public function indexAction() // wyświetlenie wszystkich aukcji zalogowanego usera
    {
        $this->denyAccessUnlessGranted("ROLE_USER"); //sprawdzamy jaki user jest zalogowany i czy jest zalogowany

        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager
            ->getRepository(Auction::class)
            ->findMyOrdered($this->getUser());

        return $this->render("MyAuction/index.html.twig", ["auctions" => $auctions]); // przesłanie wszystkich aukcji utworzonych przez
                                                                                        //przez zalogowanego usera
    }

    /**
     * @Route("/my/auction/details/{id}", name="my_auction_details")
     *
     * @param Auction $auction
     *
     * @return Response
     */
    public function detailsAction(Auction $auction) // wykorzystanie parakonwentera do pobrania w tle zawartości aukcji wynikającej z routingu
        // Szczegóły danej aukcji tylko usera , który je stworzył , tu wyświetlamy tylko szczegóły naszej aukcji
    {
        $this->denyAccessUnlessGranted("ROLE_USER");//sprawdzamy czy jest zalogowany uesr

        if ($auction->getStatus() === Auction::STATUS_FINISHED) {  //sprawdzamy jaki status posiada nasza aukcja
            return $this->render("MyAuction/finished.html.twig", ["auction" => $auction]);
        }

        $deleteForm = $this->createFormBuilder() // utworzenie formularza (przycisk) do usuwania naszej aukcji
            ->setAction($this->generateUrl("my_auction_delete", ["id" => $auction->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->add("submit", SubmitType::class, ["label" => "Delete"])
            ->getForm();

        $finishForm = $this->createFormBuilder() // dodanie formularza(przycisk) do zmiany statusu aukcji z active na finish
            ->setAction($this->generateUrl("my_auction_finish", ["id" => $auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Finish"])
            ->getForm();

        return $this->render( // przesłanie szczegółów aukcji oraz formularzy do twiga
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
    public function addAction(Request $request)// dodawanie naszej nowej aukcji
    {
        $this->denyAccessUnlessGranted("ROLE_USER");//sprawdzamy jaki user jest zalogowany 

        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction); //odwołanie się do formularza 

        if ($request->isMethod("post")) { // jeśli jestemy w danej metodzie to
            $form->handleRequest($request); // pobieramy zmienne i wstawiamy do formularza

            if ($auction->getStartingPrice() >= $auction->getPrice()) { //sprawdzamy czy cena wywoławcza nie była wyższa od ceny kup teraz
                $form->get("startingPrice")->addError(new FormError("the price of the caller must be less than buy now"))
            }

            if ($form->isValid()) { //walidacja formularza zabespiecza aby nie był pusty i miał określoną długość znaków
                $auction
                    ->setStatus(Auction::STATUS_ACTIVE)
                    ->setOwner($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($auction);
                $entityManager->flush(); // jeśli jest ok to zapisujemy do MySql

                $this->get("event_dispatcher")->dispatch(Events::AUCTION_ADD, new AuctionEvent($auction));

                $this->addFlash("success", "Auction {$auction->getTitle()} is added."); // info dla user , że wszystko jest dobrze

                return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
            }

            $this->addFlash("error", "an error occured!"); // jeśli wystąpi jakiś błąd to przesyłamy go do twiga
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
    public function editAction(Request $request, Auction $auction)//tutaj edytujemy naszą aukcje 
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) { // sprawdzamy czy dany user jest właścicielem aukcji
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AuctionType::class, $auction);//za pomocą parakonwentera po route pobieramy dane naszej aukcji
                                                                //oraz jest wstawiamy do formularza

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush(); // update danych

            $this->get("event_dispatcher")->dispatch(Events::AUCTION_EDIT, new AuctionEvent($auction));

            $this->addFlash("success", "Auction {$auction->getTitle()} is realised");//info o tym ,ze jest wszystko dobrze

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
    public function deleteAction(Auction $auction) // usuwanie aukcji wybranej aukcji po id z route za pomoca parakonwentera
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush(); // usuwamy aukcje

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
    public function finishAction(Auction $auction) // zmiana statusu naszej aukcji z active na finish
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $auction
            ->setExpiresAt(new \DateTime()) // aktualizacja czasu nasze aukcji 
            ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush(); // update tylko wartości status aukcji z active na finish

        $this->get("event_dispatcher")->dispatch(Events::AUCTION_FINISH, new AuctionEvent($auction));

        $this->addFlash("success", "Auction {$auction->getTitle()} is finish");

        return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
    }
}
