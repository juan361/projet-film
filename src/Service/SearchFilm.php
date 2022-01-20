<?php
namespace App\Service;
class SearchFilm{
    public static function search ($nomFilm) 
    {
        $descriptionFilm = NULL;
        $apiKey = '7173ced7';//a mettre 
         
        $url = "http://www.omdbapi.com/?i=tt3896198&apikey=7173ced7" . $apiKey . "&t=" . $nomFilm;
         
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