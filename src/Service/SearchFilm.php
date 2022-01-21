<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class SearchFilm{
    public static function search ($nomFilm) 
    {
        $descriptionFilm = NULL;
        //$apiKey = '7173ced7';//a mettre 
        $apiKey = 'b29d2fa';
        $nouveauNomFilm = str_replace(" ","+",$nomFilm);
        $url = "http://www.omdbapi.com/?apikey=" . $apiKey . "&t=" . $nouveauNomFilm;
         
        $response = file_get_contents($url);

        try
        {
            $descriptionFilm = json_decode($response,true)["Plot"];
        }catch (\Exception $e)
        {
            error_log($e->getMessage());
        }
        return $descriptionFilm;
    }
}