<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title>HighTech - IT Solutions Website Template</title>
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
        <link rel="shortcut icon" href="img/favicon.ico">
        <style>
            table {
            border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    td a {
        color: black; /* Set anchor tag text color to black */
    }

    </style>
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
                            <a href="service" class="nav-item nav-link">Services</a>
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
                            <span class="text-black-50">Have any questions?</span>
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
                <h1 class="display-2 text-white mb-4 animated slideInDown">learner Portal</h1>
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
        <div class="container-fluid services py-5 my-5">
    <div class="py-5">
        <div class="mx-auto pb-5 wow fadeIn" data-wow-delay=".3s" style="max-width: 600px;">
            <h5 class="text-primary">Useful University links</h5>
    
            
    <table>
        <thead>
            <tr>
                <th>Name of Institution</th>
                <th>Institution Website</th>
                <th>Application Fee</th>
                <th>Closing Dates</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sefako Makgato Health Science University</td>
                <td><a href="https://www.smu.ac.za" target="_blank">www.smu.ac.za</a></td>
                <td><a href="https://www.smu.ac.za/students/apply/online-application/" target="_blank">SMU Prospectus 2023 (Download PDF)</a></td>
                <td>Fee R200<br>29 July 2023</td>
            </tr>
            <tr>
                <td>University Of Cape Town</td>
                <td><a href="https://www.uct.ac.za" target="_blank">www.uct.ac.za</a></td>
                <td><a href="http://www.students.uct.ac.za/students/applications/key-dates" target="_blank">Undergraduate prospectus | UCT Students</a></td>
                <td>Fee R100<br>31 July 2023</td>
            </tr>
            <tr>
                <td>University Of the Free State</td>
                <td><a href="www.ufs.ac.za" target="_blank">www.ufs.ac.za</a></td>
                <td><a href="https://apply.ufs.ac.za/Application/Start" target="_blank">UFS Prospectus 2023</a></td>
                <td>Fee <br>31 May 2023</td>
            </tr>
            <tr>
                <td>University Of Kwa-Zulu Natal</td>
                <td><a href="www.ukzn.ac.za" target="_blank">www.ukzn.ac.za</a></td>
                <td><a href="Online Application (ukzn.ac.za)" target="_blank">Prospectus (ukzn.ac.za)</a></td>
                <td>Fee R250( SA only)

Fee R750(foreign)<br>30 November 2023</td>
            </tr>
            <tr>
                <td>University Of Limpopo</td>
                <td><a href="www.ul.ac.za" target="_blank">www.ukzn.ac.za</a></td>
                <td><a href="University of Limpopo:(ul.ac.za)" target="_blank">University of Limpopo:(ul.ac.za)</a></td>
                <td>Fee R200<br>29 July 2023</td>
            </tr>
            <tr>
                <td>University Of Pretoria</td>
                <td><a href="www.up.ac.za" target="_blank">www.ukzn.ac.za</a></td>
                <td><a href="https://www.up.ac.za/online-application" target="_blank">University of Pretoria (UP) Prospectus 2022 (Download PDF) - Prospectus (onlineprospectus.co.za)</a></td>
                <td>Fee R300<br>30 June 2023</td>
            </tr>
            <tr>
                <td>Stellenbosch University</td>
                <td><a href="www.sun.ac.za" target="_blank">www.sun.ac.za</a></td>
                <td><a href="http://www.sun.ac.za/english/maties/apply" target="_blank">Stellenbosch University Prospectus PDF Download (2023) | Universityonlineapplication</a></td>
                <td>Fee R100<br>30 September 2023</td>
            </tr>
            <tr>
                <td>University Of Witwatersrand</td>
                <td><a href="www.wits.ac.za" target="_blank">www.wits.ac.za</a></td>
                <td><a href="https://www.wits.ac.za/study-at-wits/" target="_blank">General Prospectus - Wits University</a></td>
                <td>Fee R100(SA only)

Fee R700(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>Nelson Mandela University</td>
                <td><a href="www.nmu.ac.za" target="_blank">www.nmu.ac.za</a></td>
                <td><a href="https://www.mandela.ac.za/Study-at-Mandela" target="_blank">
Nelson Mandela University Prospectus 2022 (Download PDF) - Prospectus (onlineprospectus.co.za)</a></td>
                <td>Application Free(SA only)

Fee R250(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>University of Fort Hare</td>
                <td><a href="" target="_blank">University of Fort Hare | Together in Excellence (ufh.ac.za)</a></td>
                <td><a href="" target="_blank">UFH Application For Admission 2023/2024 - debbieupdate

University of Fort Hare (UFH) Undergraduate Prospectus 2023 PDF - South Africa Portal</a></td>
                <td>Fee R120

Online Free

Fee R500(foreign)
<br>31 October 2023</td>
            </tr>
            <tr>
                <td>North-West University</td>
                <td><a href="" target="_blank">Welcome to North-West University (NWU) | NWU | North-West University</a></td>
                <td><a href="Apply | Studies | NWU | North-West University" target="_blank">Apply | Studies | NWU | North-West University</a></td>
                <td>Application Free

Fee R575(foreign)<br>Selection courses 30 June 2023 &

Non-selection courses 31 August 2023</td>
            </tr>
            <tr>
                <td>Rhodes University</td>
                <td><a href="" target="_blank">Rhodes University – Where leaders learn (ru.ac.za)</a></td>
                <td><a href="" target="_blank">Rhodes University Online Application (2023) | Apply Now | Universityonlineapplication</a></td>
                <td>Fee R100<br>30 September 2023</td>
            </tr>
            <tr>
                <td>University of Western Cape</td>
                <td><a href="" target="_blank">UWC Online Application 2023 | Apply Now | Universityonlineapplication</a></td>
                <td><a href="" target="_blank">UWC Prospectus 2023 (Download PDF) (admalic.co.za)</a></td>
                <td>Application Free<br>30 September 2023</td>
            </tr> <tr>
                <td>University of Johannesburg</td>
                <td><a href="" target="_blank">University of Johannesburg - UJ University in South Africa</a></td>
                <td><a href="" target="_blank">University Of Johannesburg Online Application, UJ (2023) | Apply Now | Universityonlineapplication</a></td>
                <td>Online (Free)

Fee R200<br>30 September 2023</td>
            </tr>
            <tr>
                <td>University of Venda</td>
                <td><a href="" target="_blank">UNIVEN Online Application 2023 | Apply Now | Universityonlineapplication</a></td>
                <td><a href="" target="_blank">UNIVEN Prospectus 2023 (PDF Download) (admalic.co.za)</a></td>
                <td>

                Fee R100<br>31 December 2023</td>
            </tr>
            <tr>
                <td>Walter Sisulu University</td>
                <td><a href="" target="_blank">Walter Sisulu University Online Application Process 2023-2024 - SA Online Portal (applicationportal.co.za)</a></td>
                <td><a href="" target="_blank">WSU Prospectus 2023 (PDF Download) (admalic.co.za)</a></td>
                <td>Fee R120<br>30 November 2023</td>
            </tr>
            <tr>
                <td>University of Zululand</td>
                <td><a href="www.unizulu.ac.za" target="_blank">www.unizulu.ac.za</a></td>
                <td><a href="" target="_blank">UniZulu Online Application 2023 | Apply Now | Universityonlineapplication</a></td>
                <td>Fee R220

Fee R275(foreign)<br>31 October 2023</td>
            </tr>
            <tr>
                <td>Cape Peninsula University of Technology</td>
                <td><a href="" target="_blank">CPUT - Cape Peninsula University of Technology</a></td>
                <td><a href="" target="_blank">CPUT Prospectus PDF Download 2023 | Universityonlineapplication</a></td>
                <td>Application Free

Fee R100(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>Central University of Technology</td>
                <td><a href="" target="_blank">CUT | CUT, South Africa - Thinking Beyond


CUT | Application Process</a></td>
                <td><a href="" target="_blank">CUT Prospectus 2023 for Undergraduate (Download PDF) (sanotify.com)</a></td>
                <td>Application Free<br>30 September 2023</td>
            </tr>
            <tr>
                <td>Durban University of Technology</td>
                <td><a href="" target="_blank">Durban University of Technology (dut.ac.za)</a></td>
                <td><a href="" target="_blank">DUT Undergraduate Prospectus 2023 PDF Download - South Africa Portal</a></td>
                <td>Fee R250

Fee R300(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>Mangosuthu University of Technology</td>
                <td><a href="" target="_blank">Mangosuthu University of Technology - MUT 2022 applications. (universityinfo.co.za)</a></td>
                <td><a href="" target="_blank">MUT Prospectus 2023 (PDF Download) (admalic.co.za)</a></td>
                <td>Fee R250

Fee R300(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>University of Mpumalanga</td>
                <td><a href="" target="_blank">University of Mpumalanga</a></td>
                <td><a href="" target="_blank">University of Mpumalanga (UMP) Prospectus 2023 (Download PDF) (sanotify.com)</a></td>
                <td>Fee R150

Fee R350(foreign)<br>30 September 2023</td>
            </tr>
            <tr>
                <td>Tshwane university of Technology</td>
                <td><a href="" target="_blank">Tshwane University of Technology (tut.ac.za)</a></td>
                <td><a href="" target="_blank">Tut Application Form for Employment - Fill Out and Sign Printable PDF Template | signNow</a></td>
                <td>Fee R240<br>23 September 2023</td>
            </tr>
            <tr>
                <td>Vaal University of Technology</td>
                <td><a href="" target="_blank">Vaal University of Technology (vut.ac.za)</a></td>
                <td><a href="" target="_blank">Apply to VUT – Vaal University of Technology

VUT Prospectus 2023 (PDF Download) (admalic.co.za)</a></td>
                <td>Fee R100<br>30 November 2023</td>
            </tr>
            <!-- Repeat similar rows for other institutions -->
            <!-- ... -->
        </tbody>
    </table>
    <?php include 'pwa-footer.php'; ?>
</body>
        </div>
    </div>
</div>



       
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
    </body>

</html>
