<?php
    require "vendor/autoload.php";
    require_once __DIR__."/html_tag_helpers.php";

    \EasyRdf\RdfNamespace::setDefault('og');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Louvre Museum Paris</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- CSS maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <style>
        html,
        body {
        height: 100%;
        margin: 0;
        }

        .leaflet-container {
        height: 400px;
        width: 600px;
        max-width: 100%;
        max-height: 100%;
        }
    </style>

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Teko:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Meng-embed Google API -->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <!-- Mengembed Jquery -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript">
    // Meload paket API dari Google Chart
    google.load('visualization', '1', {'packages':['corechart']});
    // Membuat Callback yang meload API visualisasi Google Chart
    google.setOnLoadCallback(drawChart);
        function drawChart() {
            var json = $.ajax({
                url: 'json.php', // file json hasil query database
                dataType: 'json',
                async: false
            }).responseText;
            
            // Mengambil nilai JSON
            var data = new google.visualization.DataTable(json);
            var options = {
                colors: ['#B78D65'],
                width: 1340,
                vAxis: {
                    title: 'Year',
                },
                height: 900,
                hAxis: {
                    title: 'Number of Visitors in Millions',
                },
                class:['progress-bar-striped progress-bar-animated'],
                role:['progressbar'],
            };
            // API Chart yang akan menampilkan ke dalam div id
            var chart = new google.visualization.BarChart(document.getElementById('tampil_chart'));
            chart.draw(data, options);
        }
    </script>
</head>
<?php
  //LOAD to RDF
  $museum_rdf = 'http://localhost/tubesws/louvre.rdf';
  $data = \EasyRdf\Graph::newAndLoad($museum_rdf);
  $doc = $data->primaryTopic();

  \EasyRdf\RdfNamespace::set('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
  \EasyRdf\RdfNamespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
  \EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');
  \EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
  \EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
  \EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');

  //BACA RDF
  $museum_uri = '';
  foreach ($doc->all('owl:sameAs') as $akun) {
  $museum_uri = $akun->get('foaf:homepage');
  $director_uri = $akun->get('foaf:director');
  $art_uri = $akun->get('foaf:type_art');
  $historic_uri = $akun->get('foaf:type_historic');
  break;
  }

  // set sparql endpoint
  $sparql_endpoint = 'https://dbpedia.org/sparql';
  $sparql = new \EasyRdf\Sparql\Client($sparql_endpoint);

  $sparql_query = ' 
  SELECT distinct * WHERE {
  <' . $museum_uri . '>   rdfs:comment ?info ;
  dbp:location ?lokasi ;
  rdfs:label ?label .

  ?lukisan dbo:museum <http://dbpedia.org/resource/Louvre> ;
  rdfs:label ?lukisan_label .

  <' . $director_uri . '> rdfs:comment ?info2 ;
  rdfs:label ?label2 .

  <' . $art_uri . '> rdfs:comment ?info3 ;
  rdfs:label ?label3 .

  <' . $historic_uri . '> rdfs:comment ?info4 ;
  rdfs:label ?label4 .

  FILTER (lang(?info) = "en" && lang(?info2) = "en" && lang(?info3) = "en" && lang(?info4) = "en" && 
      lang(?label) = "en" && lang(?label2) = "en" && lang(?label3) = "en" && lang(?label4) = "en" && 
  lang(?lukisan_label) = "en")
          
  }LIMIT 25
  ';

  $result = $sparql->query($sparql_query);

  // ambil detail louvre dari $result sparql
  $detail = [];
  foreach ($result as $row) {
      $detail = [
          'info' => $row->info,
          'lokasi' => $row->lokasi,
          'label' => $row->label,
          'info2' => $row->info2,
          'label2' => $row->label2,
          'info3' => $row->info3,
          'label3' => $row->label3,
          'info4' => $row->info4,
          'label4' => $row->label4,
      ];
      break;
  }
  ?>
<body>
    <!-- <audio autoplay loop>
        <source src="http://localhost/tubesws/backsound.mp3" type="audio/mp3"/>
    </audio> -->

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border position-relative text-primary" style="width: 6rem; height: 6rem;" role="status"></div>
        <img class="position-absolute top-50 start-50 translate-middle" style="width: 60px; height: 60px;" src="img/icons/louvre-museum.png" alt="Icon">
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top py-lg-0 px-lg-5 wow fadeIn justify-content-center" data-wow-delay="0.1s">
        <a href="index.html" class="navbar-brand ms-4 ms-lg-0">
            <h1 class="text-primary-center mx-auto my-2  "><img class="me-3" style="width: 60px; height: 60px;" src="img/icons/louvre-museum.png" alt="Icon">Louvre Museum Paris</h1>
        </a>
    </nav>
    <!-- Navbar End -->

    <!-- Carousel Start -->
    <div class="container-fluid p-0 pb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="owl-carousel header-carousel position-relative">
            <div class="owl-carousel-item position-relative" data-dot="<img src='img/carousel.jpg'>">
                <img class="img-fluid" src="img/carousel.jpg" alt="" width="1920px" height="1080px">
                <div class="owl-carousel-inner">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-10 col-lg-8">
                                <h1 class="display-1 text-white animated slideInDown">Bonjour !!!</h1>
                                <p class="fs-5 fw-medium text-white mb-4 pb-3 text-primary" style="text-align: justify;"><?=$doc->get('foaf:carousel1')?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="owl-carousel-item position-relative" data-dot="<img src='img/carousell.jpg'>">
                <img class="img-fluid" src="img/carousell.jpg" alt="">
                <div class="owl-carousel-inner">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-10 col-lg-8">
                                <h1 class="display-1 text-white animated slideInDown">Mon nom de lieu est</h1>
                                <p class="fs-5 fw-medium text-white text-justify mb-4 pb-3" style="text-align: justify;"><?=$doc->get('foaf:carousel2')?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="owl-carousel-item position-relative" data-dot="<img src='img/carouselll.jpg'>">
                <img class="img-fluid" src="img/carouselll.jpg" alt="">
                <div class="owl-carousel-inner">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-10 col-lg-8">
                                <h1 class="display-1 text-white animated slideInDown">Le musée du Louvre</h1>
                                <p class="fs-5 fw-medium text-white text-justify mb-4 pb-3" style="text-align: justify;"><?=$doc->get('foaf:carousel3')?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                <div class="about-img img-fluid">
                <div class="about-img img-fluid">
                    <?php
                    $uri1 = 'https://www.louvre.fr/en';
                        $doc1 = \EasyRdf\Graph::newAndLoad($uri1);
                        if ($doc1->image) {
                            echo content_tag('img', null, array('src'=>$doc1->image, 'class'=>'image'));
                        }
                    ?>
                    </div>
        
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                    <h4 class="section-title">DEFINITION</h4>
                    <h1 class="display-5 mb-4">What is Louvre Museum?</h1>
                    <p style="text-align: justify;"><?= $detail['info']; ?></p>
                    <div class="d-flex align-items-center mb-5">
                        <div class="d-flex flex-shrink-0 align-items-center justify-content-center border border-5 border-primary" style="width: 120px; height: 120px;">
                            <h1 class="display-1 mb-n2" data-toggle="counter-up"><?= $doc->get('foaf:year') ?></h1>
                        </div>
                        <div class="ps-4">
                            <h3>Years</h3>
                            <h3>Age of</h3>
                            <h3 class="mb-0">The Louvre Museum</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Galery -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h4 class="section-title">Our Galery</h4>
                <h1 class="display-5 mb-4">There are many works on display in the Louvre Museum</h1>
            </div>
            <div class="row g-0 team-items">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px;">
                            <?php
                                $uri2 = 'https://dbpedia.org/page/The_Wedding_at_Cana';
                                $doc2 = \EasyRdf\Graph::newAndLoad($uri2);
                                    if ($doc2->image) {
                                        echo content_tag('img', null, array('src'=>$doc2->image, 'class'=>'image'));
                                    }
                            ?>                          
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc2->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px;">
                            <?php
                                $uri3 = 'https://dbpedia.org/page/The_Raft_of_the_Medusa';
                                $doc3 = \EasyRdf\Graph::newAndLoad($uri3);
                                    if ($doc3->image) {
                                        echo content_tag('img', null, array('src'=>$doc3->image, 'class'=>'image'));
                                    }
                            ?>                    
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc3->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-wow-delay="0.5s">
                    <div class="team-item position-relative">
                        <div class="card" style="width:300px">
                            <?php
                                $uri4 = 'https://dbpedia.org/page/The_Coronation_of_Napoleon';
                                $doc4 = \EasyRdf\Graph::newAndLoad($uri4);
                                    if ($doc4->image) {
                                        echo content_tag('img', null, array('src'=>$doc4->image, 'class'=>'image'));
                                    }
                            ?>                   
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc4->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <?php
                                $uri5 = 'https://dbpedia.org/page/Liberty_Leading_the_People';
                                $doc5 = \EasyRdf\Graph::newAndLoad($uri5);
                                    if ($doc5->image) {
                                        echo content_tag('img', null, array('src'=>$doc5->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc5->title?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px;">
                            <?php
                                $uri6 = 'https://dbpedia.org/page/Francis_I_of_France';
                                $doc6 = \EasyRdf\Graph::newAndLoad($uri6);
                                    if ($doc6->image) {
                                        echo content_tag('img', null, array('src'=>$doc6->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc6->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px;">
                            <?php
                                $uri7 = 'https://en.wikipedia.org/wiki/Mona_Lisa';
                                $doc7 = \EasyRdf\Graph::newAndLoad($uri7);
                                    if ($doc7->image) {
                                        echo content_tag('img', null, array('src'=>$doc7->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc7->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-wow-delay="0.5s">
                    <div class="team-item position-relative">
                        <div class="card" style="width:300px">
                            <?php
                                $uri8 = 'https://dbpedia.org/page/Fountain_of_Diana';
                                $doc8 = \EasyRdf\Graph::newAndLoad($uri8);
                                    if ($doc8->image) {
                                        echo content_tag('img', null, array('src'=>$doc8->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc8->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri9 = 'https://dbpedia.org/page/Winged_Victory_of_Samothrace';
                                $doc9 = \EasyRdf\Graph::newAndLoad($uri9);
                                    if ($doc9->image) {
                                        echo content_tag('img', null, array('src'=>$doc9->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc9->title?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Galerry End -->

    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <h4 class="section-title">Anything about our museum</h4>
                    <h1 class="display-5 mb-4">Why You Should Visit Louvre Museum?</h1>
                    <p class="mb-4" style="text-align: justify;">You should know about the first woman head of the Louvre! And You must know about what kind of museum The Louvre is.</p>
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <img class="flex-shrink-0" src="img/icons/icon-1.png" alt="Icon">
                                <div class="ms-4">
                                    <h3><?= $detail['label2']; ?></h3>
                                    <p class="mb-0" style="text-align: justify;"><?= $detail['info2']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <img class="flex-shrink-0" src="img/icons/icon-6.png" alt="Icon">
                                <div class="ms-4">
                                    <h3><?= $detail['label3']; ?></h3>
                                    <p class="mb-0" style="text-align: justify;"><?= $detail['info3']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <img class="flex-shrink-0" src="img/icons/icon-4.png" alt="Icon">
                                <div class="ms-4">
                                    <h3><?= $detail['label4']; ?></h3>
                                    <p class="mb-0" style="text-align: justify;"><?= $detail['info4']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="feature-img">
                        <?php
                            $uri10 ='https://dbpedia.org/page/Laurence_des_Cars';
                            $doc10 = \EasyRdf\Graph::newAndLoad($uri10);
                                if ($doc10->image) {
                                    echo content_tag('img', null, array('src'=>$doc10->image, 'class'=>'image'));
                                }
                        ?>
                        <?php
                            $uri11 = 'https://dbpedia.org/page/Art_museum' ;
                            $doc11 = \EasyRdf\Graph::newAndLoad('https://dbpedia.org/page/Art_museum');
                                if ($doc11->image) {
                                    echo content_tag('img', null, array('src'=>$doc11->image, 'class'=>'image'));
                                }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Artist Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h4 class="section-title">Our Artist</h4>
                <h1 class="display-5 mb-4">We have many legendary artist in our museum</h1>
            </div>
            <div class="row g-0 team-items">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px">
                            <?php
                                $uri12 = 'https://dbpedia.org/page/Eug%C3%A8ne_Delacroix';
                                $doc12 = \EasyRdf\Graph::newAndLoad($uri12);
                                    if ($doc12->image) {
                                        echo content_tag('img', null, array('src'=>$doc12->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc12->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item position-relative">
                    <div class="card" style="width:300px">
                            <?php
                                $uri13 = 'https://dbpedia.org/page/Jacques-Louis_David';
                                $doc13 = \EasyRdf\Graph::newAndLoad($uri13);
                                    if ($doc13->image) {
                                        echo content_tag('img', null, array('src'=>$doc13->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc13->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item position-relative">
                        <div class="card" style="width:300px">
                            <?php
                                $uri14 = 'https://dbpedia.org/page/Paolo_Veronese';
                                $doc14 = \EasyRdf\Graph::newAndLoad($uri14);
                                    if ($doc14->image) {
                                        echo content_tag('img', null, array('src'=>$doc14->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4" style="height: 100px;">
                            <h3 class="mt-2"><?= $doc14->title ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri15 = 'https://dbpedia.org/page/Leonardo_da_Vinci';
                                $doc15 = \EasyRdf\Graph::newAndLoad($uri15);
                                    if ($doc15->image) {
                                        echo content_tag('img', null, array('src'=>$doc15->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc15->title?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri16 = 'https://dbpedia.org/page/Pietro_Perugino';
                                $uri16 = \EasyRdf\Graph::newAndLoad($uri16);
                                    if ($uri16->image) {
                                        echo content_tag('img', null, array('src'=>$uri16->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $uri16->title?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri17 = 'https://dbpedia.org/page/Jean-Baptiste-Sim%C3%A9on_Chardin';
                                $doc17 = \EasyRdf\Graph::newAndLoad($uri17);
                                    if ($doc17->image) {
                                        echo content_tag('img', null, array('src'=>$doc17->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc17->title?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri18 = 'https://dbpedia.org/page/Petrus_Christus';
                                $doc18 = \EasyRdf\Graph::newAndLoad($uri18);
                                    if ($doc18->image) {
                                        echo content_tag('img', null, array('src'=>$doc18->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc18->title?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item position-relative">
                        <div class="card" style="width: 300px;">
                            <!-- <img class="img-fluid" src="" alt=""> -->
                            <?php
                                $uri19 = 'https://dbpedia.org/page/Antoine_Watteau';
                                $doc19 = \EasyRdf\Graph::newAndLoad($uri19);
                                    if ($doc19->image) {
                                        echo content_tag('img', null, array('src'=>$doc19->image, 'class'=>'image'));
                                    }
                            ?>
                        </div>
                        <div class="bg-light text-center p-4">
                            <h3 class="mt-2"><?= $doc19->title?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Artist End -->

    <!-- Chart -->
    <div class="container-xxl project pt-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h4 class="section-title">Chart of Visitors</h4>
                <h1 class="display-5">The following are data on the number of visitors to the Louvre Museum from 2007 until 2021</h1>
            </div>
        </div>
    </div>
    <div id="tampil_chart" style="width:max-content;" class="progress-bar progress-bar-striped progress-bar-animated" ></div>
    <!-- Resource : https://www.statista.com/statistics/247419/yearly-visitors-to-the-louvre-in-paris/ -->
        
    <!-- Maps Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <h4 class="section-title">Our Location</h4>
                    <h1 class="display-5 mb-4">Location of Louvre</h1>
                    <p class="mb-4" style="text-align: justify;"><?=$doc->get('foaf:mapsInfo1')?></p>
                    <p class="mb-4" style="text-align: justify;"><?=$doc->get('foaf:mapsInfo2')?></p>
                </div>
                <!-- maps -->
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="row g-3">
                        <div class="map_main">
                             <!-- ukuran dari mapsnya ditampilan web -->
                            <div id="map" style="width: 700px; height: 300px"></div>
                            <script>
                                //lokasi Museum Louvre. longitude dan longitude
                                const map = L.map("map").setView(['<?= $doc->get('foaf:latitude') ?>', '<?= $doc->get('foaf:longitude') ?>'], 15);

                                const tiles = L.tileLayer(
                                "https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                                    maxZoom: 19,
                                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                                }
                                ).addTo(map);

                                const marker = L.marker(['<?= $doc->get('foaf:latitude') ?>', '<?= $doc->get('foaf:longitude') ?>'])
                                .addTo(map)
                                .bindPopup("<b>lokasi</b><br />Museum Louvre")
                                .openPopup();

                                const popup = L.popup()
                                .setLatLng(['<?= $doc->get('foaf:latitude') ?>', '<?= $doc->get('foaf:longitude') ?>'])
                                .setContent("pop up lokasi Museum Louvre.")
                                .openOn(map);

                                function onMapClick(e) {
                                popup
                                    .setLatLng(e.latlng)
                                    .setContent(`You clicked the map at ${e.latlng.toString()}`)
                                    .openOn(map);
                                }

                                map.on("click", onMapClick);
                            </script>
                            <h4 style="color: black">Musée du Louvre, Paris, France Geographic Information</h4>
                            <table class="margina">
                                <tbody>
                                <tr>
                                    <th>Country</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:country') ?></td>
                                </tr>
                                <tr>
                                    <th>Latitude</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:latitude') ?></td>
                                </tr>
                                <tr>
                                    <th>Longitude</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:longitude') ?></td>
                                </tr>
                                <tr>
                                    <th>DMS Lat</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:dms_lat') ?></td>
                                </tr>
                                <tr>
                                    <th>DMS Long</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:dms_long') ?></td>
                                </tr>
                                <tr>
                                    <th>UTM Easting</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:utm_easting') ?></td>
                                </tr>
                                <tr>
                                    <th>UTM Northing</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:utm_northing') ?></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:category') ?></td>
                                </tr>
                                <tr>
                                    <th>Country Code</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:country_code') ?></td>
                                </tr>
                                <tr>
                                    <th>Zoom Level</th>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $doc->get('foaf:zoom_level') ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Maps End -->

    <!-- Loop Galery Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h4 class="section-title">Paintings</h4>
                <h1 class="display-5 mb-4">Name of Paintings You Can Visit at the Louvre</h1>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
                <?php 
                foreach ($result as $row) { ?>
                    <div class="testimonial-item text-center" data-dot="<img class='img-fluid' src='img/icons/dot.png'>">
                        <h3><?= $row->lukisan_label ?></h3>
                    </div>
                <?php 
                } 
                ?>
            </div>      
        </div>
    </div>
    <!-- Loop Galey End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>
</html>