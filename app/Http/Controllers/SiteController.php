<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp;
use RuntimeException;
use App\File;

require_once 'simple_html_dom.php';

class SiteController extends Controller {

    public function __construct() {
//          site web to crawle 
//          
//        $this->url = array('SEBRAE ' => 'http://www.sebrae.com.br/canaldofornecedor',
//            'GDF' => 'https://www.compras.df.gov.br/publico/em_andamento.asp',
//            'CNPQ' => 'http://www.cnpq.br/web/guest/licitacoes',
//            'SSP-DF' => 'http://licitacoes.ssp.df.gov.br./index.php/licitacoes',
//            'CÂMARA LEGISLATIVA DO DISTRITO FEDERAL' => 'http://www.cl.df.gov.br/pt_PT/pregoes',
//            'CÂMARA DOS DEPUTADOS' => 'http://www2.camara.leg.br/transparencia/licitacoes/editais');
    }

    public function index() {

        $this->get_links_cnpq('http://www.cnpq.br/web/guest/licitacoes');
        // $this->get_links_camara('http://www.cl.df.gov.br/pt_PT/pregoes');
    }

    /* crawl CÂMARA LEGISLATIVA  site
     */

    public function get_links_camara($url) {

        echo 'Crawling CÂMARA LEGISLATIVA Site' . ' http://www.cl.df.gov.br/pt_PT/pregoes';

        $client = new GuzzleHttp\Client();
        $body = $client->get($url)->getBody()->getContents();

        $crawler0 = new Crawler($body);
        $count = $crawler0->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr')->count();
        $ar = array();
        //echo "<pre>";
        for ($i = 1; $i <= $count; $i++) {

            if ($i > 2) {

                $url1 = $crawler0->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr[' . $i . ']/td[1]')->filter('a')->attr('href');
                //var_dump($url1);
                $body1 = $client->get($url1)->getBody()->getContents();

                $crawler1 = new Crawler($body1);
                //if(!strpos($crawler1->filter('.header-title')->filter('span')->text(), "Anulados")){
                //$ar[$i]['name']=$crawler1->filter('.header-title')->filter('span')->text();
                $count1 = $crawler1->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr')->count();

                for ($i1 = 1; $i1 <= $count1; $i1++) {

                    //$ar[$i]['attachments'][$i1]=$crawler1->filter('#xqdw')->filter('.results-grid')->first()->filterXPath('//table/tr['.$i.']/td[1]')->filter('a')->attr('href'); 
                    if ($i1 > 2) {
                        $url2 = $crawler1->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr[' . $i1 . ']/td[1]')->filter('a')->attr('href');

                        $body2 = $client->get($url2)->getBody()->getContents();

                        $crawler2 = new Crawler($body2);

                        $count2 = $crawler2->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr')->count();
                        //Set Name
                        $ar[$i1]['name'] = $crawler2->filter('.header-title')->filter('span')->text();
                        $ar[$i1]['object'] = $ar[$i1]['name'];
                        //var_dump($count2);
                        for ($i2 = 1; $i2 <= $count2; $i2++) {
                            if ($i2 > 2) {

                                echo "<br>";
                                if ($crawler2->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr[' . $i2 . ']/td[1]')->filter('a')->count()) {
                                    $url3 = $crawler2->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr[' . $i2 . ']/td[1]')->filter('a')->attr('href');

                                    $body3 = $client->get($url3)->getBody()->getContents();
                                    $crawler3 = new Crawler($body3);

                                    if (strpos($crawler3->filter('#portlet_110_INSTANCE_ou5V')->text(), "Downloads")) {

                                        $url4 = $crawler3->filter('#portlet_110_INSTANCE_ou5V')->filter('.results-grid')->first()->filterXPath('//table/tr[3]/td[1]')->filter('a')->attr('href');
                                        $ar[$i1]['attachments'][$i2]['name'] = $crawler3->filter('.header-title')->filter('span')->text();
                                        try {
                                            $client2 = new GuzzleHttp\Client();
                                            $ar[$i1]['attachments'][$i2]['file'] = $client2->get($url4)->getBody()->getContents();
                                            if (file_put_contents(base_path() . "/public/files/" . $ar[$i1]['attachments'][$i2]['name'] . ".pdf", $ar[$i1]['attachments'][$i2]['file']) != false) {
                                                echo "File created ";
                                            } else {
                                                echo "Cannot create file ";
                                            }
                                        } catch (\Exception $e) {
                                            $ar[$i1]['attachments'][$i2]['file'] = "File not found";
                                        }
                                    } else {

                                        $ar[$i1]['object'] = $crawler3->filter('.header-title')->filter('span')->text();
                                    }
                                }
                            }
                        }
                      
                    }
                }
                
            }
             $this->save_files($ar);
        }
    }

    /* crawl CNPQ site 
     */

    public function get_links_cnpq($url) {
          echo '<h1> Crawling CNPQ Site</h1>'  . ' http://www.cnpq.br/web/guest/licitacoes' . "<br />";
          
        //get all body contents
        $client = new GuzzleHttp\Client();
        $body = $client->get($url)->getBody()->getContents();

        $crawler = new Crawler($body);
        //filter  resultado-licitacao  contents
        $count = $crawler->filter('.resultado-licitacao')->filterXPath('//table/tbody/tr')->count();

        for ($i = 1; $i <= $count; $i++) {
            // set data 
            $ar[$i]['name'] = $crawler->filter('.resultado-licitacao')->filterXPath('//table/tbody/tr[' . $i . ']/td[1]')->filter('h4')->text();
            $ar[$i]['origin'] = 'CNPQ';
            $ar[$i]['object'] = $crawler->filter('.resultado-licitacao')->filterXPath('//table/tbody/tr[' . $i . ']/td[1]')->filter('.cont_licitacoes')->text();
            $ar[$i]['attachments'][0]['name'] = $ar[$i]['name'];

            //get file contents
            $url = 'http://www.cnpq.br' . $crawler->filter('.resultado-licitacao')->filterXPath('//table/tbody/tr[' . $i . ']/td[1]')->filter('ul')->filter('li')->filter('a')->attr('href');
            $file = $client->get($url)->getBody()->getContents();

            //create file 
            if (file_put_contents(base_path() . "/public/files/cnpq/" . urlencode($ar[$i]['name']) . ".pdf", $file) != false) {
                echo $ar[$i]['name'] ." successfully  created " . "<br />";
                
            } else {
                echo "Cannot create file ";
            }
            $ar[$i]['attachments'][0]['file'] = "/public/files/" . urlencode($ar[$i]['name']) . ".pdf";
        }

        $this->save_files($ar);
    }

    /* Save Files
      @param $files : array
      @return bool
     */

    public function save_files($fileData) {

        foreach ($fileData as $files) {
            //save  FILES       
            if (self::save_file($files)) {
                echo $files['name'] . ' successfully saved' . "<br />";
            }
        }
    }

    /* Save File
      @param $files : array
      @return bool
     */

    private function save_file($files) {

        $data = new File();
        $data->name = $files['name'];
        $data->origin = $files['origin'];
        $data->attachments = $files['attachments'];
        $data->object = $files['object'];
        //$data->starting_date = $files['starting_date'];
        if ($data->save()) {
            return true;
        }
    }

}
