<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Temp;
use App\Form\GetWeatherType;
use App\Repository\LocationRepository;
use App\Repository\TempRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param LocationRepository $locationRepository
     * @param TempRepository $tempRepository
     * @return Response
     */
    public function getWeather(Request $request,
                               LocationRepository $locationRepository,
                               TempRepository $tempRepository)
    : Response
    {
        $form = $this->createForm(GetWeatherType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $name = $form->getData()->getName();
            $LOCATION = $this->getCity($name);
            if (!isset($LOCATION)){
                return $this->render('main/statsweather.html.twig', [
                    'city'=>$name,
                    'alert'=>'brak informacji o mieście'
                ]);
            }
            $weather = $this->fetchData("https://www.metaweather.com/api/location/".$LOCATION->getUniqueName());
            $consolidated_weather = $weather->consolidated_weather;
            $locationRepository->insertLocation($LOCATION, );
            $city_id = $locationRepository->findOneBy(['uniqueName' => $LOCATION->getUniqueName()])->getId();
            $tempRepository->insertWeatherStats($consolidated_weather, $city_id);
            return $this->render('main/statsweather.html.twig', [
                'city'=>$name,
                'stats'=>$consolidated_weather
            ]);
        }
        return $this->render('main/weather.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    private function fetchData(string $url){
        try {
            if (file_get_contents($url)){
                return json_decode(file_get_contents($url));
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }

    public function getCity(string $name): Location | null{
        $loc = new Location();
        $cityData = $this->fetchData("https://www.metaweather.com/api/location/search/?query=".$name);
        $loc->setName($name);
        $loc->setUniqueName($cityData[0]->woeid);
        return $loc;
    }

}
