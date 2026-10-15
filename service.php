<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <?php include 'pwa-head.php'; ?>
        <meta content="" name="keywords">
        <meta content="" name="description">

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Saira:wght@500;600;700&display=swap" rel="stylesheet"> 

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link href="lib/animate/animate.min.css" rel="stylesheet">
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

        <!-- Customized Bootstrap Stylesheet -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
        <!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh+WyHcMpBcy5X2I/KELC7M/L0nYd5F0crViu" crossorigin="anonymous">
<link rel="shortcut icon" href="img/favicon.ico">  
</head>

    <body>
        <!-- Spinner Start -->
        <div id="spinner" class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" role="status"></div>
        </div>
        <!-- Spinner End -->

        <!-- Topbar Start -->
        <div class="container-fluid bg-dark py-2 d-none d-md-flex">
            <div class="container">
                <div class="d-flex justify-content-between topbar">
                    <div class="top-info">
                        <small class="me-3 text-white-50"><a href="#"><i class="fas fa-map-marker-alt me-2 text-secondary"></i></a>Matatiele Alfred Nzo District Eastern Cape</small>
                        <small class="me-3 text-white-50"><a href="#"><i class="fas fa-envelope me-2 text-secondary"></i></a>info@mdi-za.org</small>
                    </div>
                    <div class="top-link">
                    <a href="https://www.facebook.com/profile.php?id=100090768176161&mibextid=LQQJ4d" class="bg-light nav-fill btn btn-sm-square rounded-circle"><i class="fab fa-facebook-f text-secondary"></i></a>
                        <a href="https://www.linkedin.com/company/mgobozi-developmental-initiatives-mdi/" class="bg-light nav-fill btn btn-sm-square rounded-circle me-0"><i class="fab fa-linkedin-in text-secondary"></i></a>
                   </div>
                </div>
            </div>
        </div>
        <!-- Topbar End -->

        <!-- Navbar Start -->
        <div class="container-fluid bg-primary">
            <div class="container">
                <nav class="navbar navbar-dark navbar-expand-lg py-0">
                    <a href="index.html" class="navbar-brand">
                    <img src="img/logo.png" alt="" width="110" height="100" class="d-inline-block align-text-top">
                    </a>
                    <button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse bg-transparent" id="navbarCollapse">
                        <div class="navbar-nav ms-auto mx-xl-auto p-0">
                            <a href="index" class="nav-item nav-link">Home</a>
                            <a href="about" class="nav-item nav-link">About</a>
                            <a href="service" class="nav-item nav-link active">Services</a>
                            <div class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" id="learnerPortalDropdown" role="button" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false" onclick="toggleSubheadings()">Learner Portal</a>
    <div class="dropdown-menu" aria-labelledby="learnerPortalDropdown">
        <a class="dropdown-item" href="learnerPortal">University Links</a>
        <a class="dropdown-item" href="resource">Learner Resource Directory</a>
        <a class="dropdown-item" href="funding">Bursary Funding</a>
        <a class="dropdown-item" href="nbt">NBT Test Dates</a>
        <!-- Add more subheadings as needed -->
    </div>
</div>

<script>
    function toggleSubheadings() {
        // Toggle the visibility of the subheadings
        $('.dropdown-menu').toggle();
    }
</script>


                         <a href="careers" class="nav-item nav-link">Careers</a>
                            <a href="https://form.jotform.com/233170593368057" class="nav-item nav-link">School Registration</a>
                            <a href="portfolio" class="nav-item nav-link">Gallery</a>
                        </div>
                    </div>
                    <div class="d-none d-xl-flex flex-shirink-0">
                        <div id="phone-tada" class="d-flex align-items-center justify-content-center me-4">
                            <a href="" class="position-relative animated tada infinite">
                                <i class="fa fa-phone-alt text-white fa-2x"></i>
                                <div class="position-absolute" style="top: -7px; left: 20px;">
                                    <span><i class="fa fa-comment-dots text-secondary"></i></span>
                                </div>
                            </a>
                        </div>
                        <div class="d-flex flex-column pe-4 border-end">
                            <span class="text-white-50">Have any questions?</span>
                            <span class="text-secondary">Call: 0846725409</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center ms-4 ">
                            <a href="#"><i class="bi bi-search text-white fa-2x"></i> </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!-- Navbar End -->

        
        <!-- Page Header Start -->
        <div class="container-fluid page-header py-5">
            <div class="container text-center py-5">
                <h1 class="display-2 text-white mb-4 animated slideInDown">what we do</h1>
                <nav aria-label="breadcrumb animated slideInDown">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Pages</a></li>
                        <li class="breadcrumb-item" aria-current="page">Services</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header End -->


        <!-- Services Start -->
        <div class="container-fluid py-5 my-5">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-lg-5 col-md-6 col-sm-12 wow fadeIn" data-wow-delay=".3s">
                        <div class="h-100 position-relative">
                            <img src="img/apply.jpg" class="img-fluid w-75 rounded" alt="" style="margin-bottom: 25%;">
                            <div class="position-absolute w-75" style="top: 25%; left: 25%;">
                                <img src="img/p.jpg" class="img-fluid w-100 rounded" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 col-sm-12 wow fadeIn" data-wow-delay=".5s">
                        <h5 class="text-primary">what-we-do</h5>
                        <h1 class="mb-4">Current Programs</h1>
                        <h3>Enable online mathematics tutoring</h3>
                        <p>Investing in connectivity infrastructure and devices for Luphindo High School in Matatiele as our E-Learning support pilot project,
                             which   links learners to mathematics tutors and exisiting online programs.</p><br>
                             <h3>Online university applications</h3>
                        <p>Hosting our first online university application support initiatives. Trained agents support learners create email accounts, organise and digitize mandatory documents. Agents assist learners complete online applications to South African universities
                            . Qualifying learners applying for Medical and/or Pharmaceutical studies are assisted with application fee.</p><br/>
                            <h3>Financial Aid Program</h3>
                        <p>Financial support for qualifying learners studying towards MBChB degree and/or BPharm.</p>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- About End -->




       
        <!-- Back to Top -->
        <a href="#" class="btn btn-secondary btn-square rounded-circle back-to-top"><i class="fa fa-arrow-up text-white"></i></a>

        
        <!-- JavaScript Libraries -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="lib/wow/wow.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/waypoints/waypoints.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>

        <!-- Template Javascript -->
        <script src="js/main.js"></script>
    <?php include 'pwa-footer.php'; ?>
    </body>

</html>
