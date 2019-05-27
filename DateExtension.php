<?php

namespace AppBundle\Twig;

class DateExtension extends \Twig_Extension //Filtr dla naszego twiga
{
    /**
     * @return array
     */
    public function getFilters() // //zwracamy tablice z funkcjami
    {
        return [
            new \Twig_SimpleFilter("expireDate", [$this, "expireDate"])
        ];
    }

    /**
     * @return array
     */
    public function getFunctions() // zwracamy tablice z funkcjami
    {
        return [
            new \Twig_SimpleFunction("auctionStyle", [$this, "auctionStyle"])
        ];
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return string
     */
    public function expireDate(\DateTime $expiresAt)//dostaje date expipesAat z twiga , własny fltr na wyświetlanie czasu zakończenia aukcji
    {
        if ($expiresAt < new \DateTime("-7 days")) { // jeśli data zakonczenia aukcji jest mniejsz niż 7 dni
            return $expiresAt->format("Y-m-d H:i"); // pokaż date w takim formacie rok-miesiąc-dzień-godzina-minuta
        }

        if ($expiresAt > new \DateTime("-1 day")) {//jeśli jeśli jest więkdza od jedno dnia
            return "za " . $expiresAt->diff(new \DateTime())->days . " dni"; // pokaż dzień plus napis dni
        }

        return "za " . $expiresAt->format("H") . " godz. " . $expiresAt->format("i") . " min.";//jeśli za mniej niż jeden dzień 
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return string
     */
    public function auctionStyle(\DateTime $expiresAt) // zmiana wyklądu aukcji jeśli zbliża się jej koniec 
    {
        if ($expiresAt < new \DateTime("+1 day")) { // poniżej jednego dnia zmiana koloru panelu
            return "panel-danger";
        }

        return "panel-default";
    }
}
