<?php

use function GuzzleHttp\json_decode;

require "../classes/Cache/cache_class.php";

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class Info
{

    private $type;
    private $ogrn;
    private $debug;
    private $cache;
    private $daysactual;
    private $name;
    function __construct()
    {
        $this->daysactual=isset($_GET['update'])?0:30;
       
        $this->cache = new Cache("zod1", "zchb", false, $this->daysactual);


        $this->debug = new Debug("отладка модуля Info", "deb2");

        if (!isset($_GET["ogrn"])) {
            $this->debug->addLog("Данные по ОГРН пустой");
            exit;
        }

        $this->type = isset($_GET["type"]) ? $_GET["type"] : 1;
        

        $this->ogrn = explode("_",$_GET["ogrn"])[0];

        $ogrnarray=explode("_",$_GET["ogrn"]);
        unset($ogrnarray[0]);
        $this->name = implode("&nbsp;", $ogrnarray);
        


        switch ($this->type) {
            case 1:
                echo  $this->getJSON();
                break;
         
            case 2:
                $this->buildHTML();
                break;
        }
    }

    function getJSON()
    {
        if ($this->cache->checkrecord($this->ogrn,isset($_GET['update'])?0:30) == 0) {


            $this->debug->addLog("Данные по ОГРН " . $this->ogrn . " получены из кеша");
            return $this->cache->get($this->ogrn);
        } else {
            $data = file_get_contents("https://zachestnyibiznesapi.ru/paid/data/important-facts?api_key=L2GrRUY7AbeAd3PBsSQMIwQs8aORvd1I&id=" . $this->ogrn . "&_format=json");
            $this->debug->addLog("Данные по ОГРН " . $this->ogrn . " получены из внешнего источника и кешированы");
            $this->cache->add($this->ogrn, $data);
            return $data;
        }
    }





    function buildHTML()
    {
       $obj= \json_decode($this->getJSON());
   
        echo '        
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Document</title>
    <script
  src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
  <link rel="stylesheet" href="css/info.css">
  <script src="js\info.js"></script>

</head>
<body>';
echo '<div class="topbar">
<div class="orginfo">

<div class="orginfo__data">'.$this->name.'</div>
</div>
<div class="datainfo">

<div class="datainfo__data">Данные из кеша '.(date("d.m.Y",strtotime($this->cache->getActuality($this->ogrn)))).'</div>

</div>
<div class="dataaction">
    <div class="button" onclick="window.location=\'info.php?type='. $this->type .'&name='.(isset($_GET['name'])?$_GET['name']:"").'&ogrn='.$this->ogrn.'&update\'">Обновить</div>
</div>
</div>';

echo '</div><div class="danger">';

foreach($obj->body->danger as $item)
{
echo '<div class="inforow">
        <div class="name">'.$item->name.'</div>
        <div class="value">'.$item->value.'</div>
     '//   <div class="desc">'.$item->desc.'</div>
      .'<div></div>
       
      
        
    </div>';

}
echo '</div><div class="warning">';


foreach($obj->body->warning as $item)
{
echo '<div class="inforow">
        <div class="name">'.$item->name.'</div>
        <div class="value">'.$item->value.'</div>
      '//    <div class="desc">'.$item->desc.'</div>
      .'<div></div>
        
        
        
    </div>';


}



echo '</div><div class="success">';

foreach($obj->body->success as $item)
{
echo '<div class="inforow">
        <div class="name">'.$item->name.'</div>
        <div class="value">'.$item->value.'</div>
        '//   <div class="desc">'.$item->desc.'</div>
        .'<div></div>

        
        
    </div>';


}
  



  
  


echo '
</div>
</body>
</html>';

    }
}
