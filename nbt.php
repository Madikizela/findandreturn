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
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
        #content {
            text-align: center;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            border: 0px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
            display: inline-block;
            text-align: left;
        }

        ul li {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        ul li:last-child {
            border-bottom: none;
        }

        a {
            color: #21610c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h2 {
            color: #21610c;
        }
        .container {
  display: flex;
  align-items: center; /* Align items vertically */
}

.squares,
.labels {
  list-style: none;
  padding: 0;
  margin: 0;
}

.square {
  width: 50px; /* Adjust the width and height as needed */
  height: 50px;
  margin-right: 20px; /* Adjust the spacing between squares as needed */
}

.square.yellow {
  background-color: blue;
}

.square.blue {
  background-color: #FFBF00;
}

.square.green {
  background-color: white;
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
                <div id="note" class="text-secondary d-none d-xl-flex"><small></small></div>
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
                        <a href="index" class="nav-item nav-link  text-secondary">Home</a>
                        <a href="about" class="nav-item nav-link">About</a>
                        <a href="service" class="nav-item nav-link">Services</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="learnerPortalDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" active onclick="toggleSubheadings()">Learner Portal</a>
                            <div class="dropdown-menu" aria-labelledby="learnerPortalDropdown">
                                <a class="dropdown-item" href="learnerPortal">University Links</a>
                                <a class="dropdown-item" href="resource">Learner Resource Directory</a>
                                <a class="dropdown-item" href="funding">Bursary Funding</a>
                                <a class="dropdown-item" href="">NBT Test Dates</a>
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

    <div class="container"> <!-- Added container here -->
        <div id="content">
            <h2>UNDERSTANDING NATIONAL BENCHMARK TEST (NBT)</h2>

            <style>
                /* CSS for layout */
                .row {
                    display: flex; /* Use flexbox for layout */
                }

                .section {
                    flex: 1; /* Each section takes up equal space */
                    padding: 20px;
                    border: 1px solid #ccc;
                    box-sizing: border-box; /* Ensure padding is included in width */
                }

                ul {
                    list-style-type: disc; /* Use dots for list items */
                    margin-bottom: 10px; /* Add some space between the list and other content */
                    padding-left: 20px; /* Add some left padding for better alignment */
                }

                li {
                    text-align: left; /* Align text to the left */
                    margin-bottom: 10px; /* Add some space between list items */
                }

                table {
                    width: 100%; /* Make the table fill the width of the container */
                    border-collapse: collapse; /* Collapse borders */
                }

                th,
                td {
                    border: 1px solid #ddd; /* Add borders to cells */
                    padding: 8px; /* Add padding to cells */
                    text-align: left; /* Align text to the left */
                }
  /* Style to create a horizontal list */ 
  ul.horizontal-list { 
      list-style-type: none; 
      margin: 0; 
      padding: 0; 
      display: flex; 
    } 
  
    ul.horizontal-list li { 
      margin-right: 50px; 
      display: flex; 
      flex-direction: column; 
      align-items: center; 
    } 
    
    .cycle { 
      width: 90px; 
      height: 90px; 
      border-radius: 50%; 
      background-color: lightblue; 
      display: flex; 
      justify-content: center; 
      align-items: center; 
    } 


            </style>

            <div class="row">
                <div class="section">
                <h2>HOW TO REGISTER FOR THE NBT</h2>
                    <p>Register on the internet:<br>
                    <ol>
  <li>Access the NBT website:<a href="http://www.nbt.ac.za">www.nbt.ac.za</a></li>
  <li>Click on “<b>BOOK A TEST</b>” and 
follow the prompts. </li>
<li>You can also register via the 
internet on your <b>cell phone</b></li>
<li>A letter confi rming your registration and 
containing a barcode/NBT reference 
number will be available for you to download 
and print after you click on “SUBMIT”.</li>
</ol>
<b>NOTE</b>: Registration is date and site specifi c. If you 
miss your scheduled writing session, or wish to write 
a second time, you must <b>register and pay again.</b>
<h5>NBT COST</h5> 
<ul class="horizontal-list"> 
    <li> 
      <div class="cycle">AQL only</div> 
      <p>R175</p> 
    </li> 
    <li> 
      <div class="cycle">AQL & MAT</div> 
      <p>R350</p> 
    </li> 
    <li> 
      <div class="cycle">Re-mark</div> 
      <p>R250</p> 
    </li> 
  </ul> 

<h2>PAYMENT 
FOR THE TEST</h2>
<ul>
  <li>You must take your registration letter to an <b>EasyPay Paypoint</b> to pay the testing fees.</li>
  <li>You may pay on the <b>EasyPay website</b> at <a href="http://www.easypay.co.za">www.easypay.co.za</a></li>
  <li>You may pay by making a <b>direct deposit or EFT</b> payment to NBT Project.</li>
</ul>
</p>
<h2>WHY DO I HAVE TO 
WRITE THE TESTS?</h2>
               <p>
               The NBTs are used by many universities 
in the country. They help higher 
education institutions to <b>interpret school 
leaving results</b> such as those of the National 
Senior Certifi cate (NSC). The test results <b>support</b>, but 
don’t replace or duplicate,<b> NSC results</b>.
Some South African higher education institutions use the 
NBTs for <b>admission</b> and others use them to determine 
whether you will need extra academic support during 
your studies. They also assist with the development of 
curricula. Find out from the institution you are applying to 
whether you are required to write the tests.
               </p> 
               <hr>
               <h2>WHAT CAN I EXPECT
FROM THE TESTS?</h2> 
<p>
There are <b>two tests</b>: Academic 
Literacy, Quantitative Literacy (which 
are combined: <b>AQL</b>), and Mathematics 
(<b>MAT</b>). For more information about what the 
tests focus on, go to our web site: <b>www.nbt.ac.za</b>
The QL test is closely related to the intended outcomes 
of the NSC subject, Mathematical Literacy.<br>
Students with disabilities are accommodated. The AL 
test has also been translated into <b>Braille</b>.<br>
Each test requires you to apply prior learning - what 
you know and are able to do - to materials that refl ect 
<b>expectations for fi rst year students</b> in university 
programmes.<br>
<b>The tests are confi dential, so there are no past papers 
and no special study materials to prepare for the tests.</b>
</p>
<hr>
<h2>
<b>CHOOSE</b> ONLINE OR THE 
PENCIL & PAPER TEST
</h2> 
<ol>
  <li>The tests are <b>equilibrated</b> so there 
is no dierence in writing the online 
or pencil and paper form of the NBT.</li>
  <li>You should choose the test form that <b>suits 
you the best</b>.</a></li>
  <li>The <b>online test</b> is convenient as you would normally 
write the test at home, but you will <b>need a computer</b> that meets the minimum specifi cation and have 
<b>stable internet access</b> and <b>sufficient bandwidth or 
data</b>.</li>
<li>You may prefer to write in a <b>test venue</b> using the 
<b>pencil and paper</b> form of the NBT, in which case you 
should <b>book your test</b> in a venue closest to you on a 
date that suits you.</li>
</ol> 
                </div>
                <div class="section">
                <h2>2024 CALENDAR</h2>
                    <p>AT THE TIME OF COMPILING THIS BROUCHER THESE WERE THE DATES NOT IN THE PAST:</p>
                    <table>
                        <tr>
                            <th>NBT Test Dates </th>
                            <th>Last Day to Register Online </th>
                            <th>Last Day to Pay Fees </th>
                            <th>Results Available to Institutions </th>
                            <th>Results Available to Writers</th>
                        </tr>
                        <tr>
                            <td style="background-color: #FFBF00;">11-May</td>
                            <td>21-Apr</td>
                            <td>29-Apr </td>
                            <td>3-Jun</td>
                            <td>10-Jun</td>
                        </tr>
                        <tr>
                            <td>18-May</td>
                            <td>28-Apr</td>
                            <td>6-May</td>
                            <td>10-Jun</td>
                            <td>17-Jun</td>
                        </tr>
                        <tr>
                            <td>1-Jun</td>
                            <td>12-May</td>
                            <td>20-May</td>
                            <td>24-Jun</td>
                            <td>1-Jul</td>
                        </tr>
                        <tr>
                            <td>8-Jun</td>
                            <td>19-May</td>
                            <td>27-May</td>
                            <td>1-Jul</td>
                            <td>8-Jul</td>
                        </tr>
                        <tr>
                            <td style="background-color: blue;">9-Jun</td>
                            <td >19-May</td>
                            <td>27-May</td>
                            <td>1-Jul</td>
                            <td>8-Ju</td>
                        </tr>
                        <tr>
                            <td style="background-color: #FFBF00;">22-Jun</td>
                            <td>2-Jun</td>
                            <td>10-Jun</td>
                            <td>15-Jul</td>
                            <td>22-Jul</td>
                        </tr>
                        <tr>
                            <td>29-Jun</td>
                            <td>9-Jun</td>
                            <td>17-Jun</td>
                            <td>22-Jul</td>
                            <td>29-Jul</td>
                        </tr>
                        <tr>
                            <td>6-Jul</td>
                            <td>16-Jun</td>
                            <td>24-Jun</td>
                            <td>29-Jul</td>
                            <td>5-Aug</td>
                        </tr>
                        <tr>
                            <td style="background-color: blue;">7-Jul</td>
                            <td>16-Jun</td>
                            <td>24-Jun</td>
                            <td>29-Jul</td>
                            <td>5-Aug</td>
                        </tr>
                        <tr>
                            <td>13-Jul</td>
                            <td>23-Jun</td>
                            <td>1-Jul</td>
                            <td>5-Aug</td>
                            <td>12-Aug</td>
                        </tr>
                        <tr>
                            <td style="background-color: #FFBF00;">20-Jul</td>
                            <td>30-Jun</td>
                            <td>8-Jul</td>
                            <td>12-Aug</td>
                            <td>19-Aug</td>
                        </tr>
                        <tr>
                            <td>27-Jul</td>
                            <td>7-Jul</td>
                            <td>15-Jul</td>
                            <td>19-Aug</td>
                            <td>26-Aug</td>
                        </tr>
                        <tr>
                            <td style="background-color: blue;">28-Jul</td>
                            <td>7-Jul</td>
                            <td>15-Jul</td>
                            <td>19-Aug</td>
                            <td>26-Aug</td>
                        </tr>
                        <tr>
                            <td>3-Aug</td>
                            <td>14-Jul</td>
                            <td>22-Jul</td>
                            <td>26-Aug</td>
                            <td>2-Sep</td>
                        </tr>
                        <tr>
                            <td>17-Aug</td>
                            <td>21-Jul</td>
                            <td>29-Jul</td>
                            <td>2-Sep</td>
                            <td>9-Sep</td>
                        </tr>
                        <tr>
                            <td style="background-color: blue;">18-Aug</td>
                            <td>21-Jul</td>
                            <td>29-Jul</td>
                            <td>2-Sep</td>
                            <td>9-Sep</td>
                        </tr>
                        <tr>
                            <td style="background-color: #FFBF00;">24-Aug</td>
                            <td>4-Aug</td>
                            <td>12-Aug</td>
                            <td>16-Sep</td>
                            <td>23-Sep</td>
                        </tr>
                        <tr>
                            <td>31-Aug</td>
                            <td>11-Aug</td>
                            <td>19-Aug</td>
                            <td>23-Sep</td>
                            <td>30-Sep</td>
                        </tr>
                        <tr>
                            <td>7-Sep</td>
                            <td>18-Aug</td>
                            <td>26-Aug</td>
                            <td>30-Sep</td>
                            <td>7-Oct</td>
                        </tr>
                        <tr>
                            <td>14-Sep</td>
                            <td>25-Aug</td>
                            <td>2-Sep</td>
                            <td>7-Oct</td>
                            <td>14-Oct</td>
                        </tr>
                        <tr>
                            <td>28-Sep</td>
                            <td>8-Sep</td>
                            <td>16-Sep</td>
                            <td>21-Oct</td>
                            <td>28-Oct</td>
                        </tr>
                        <tr>
                            <td style="background-color: #FFBF00;">5-Oct</td>
                            <td>15-Sep</td>
                            <td>23-Sep</td>
                            <td>28-Oct</td>
                            <td>4-Nov</td>
                        </tr>
                        <tr>
                            <td>12-Oct</td>
                            <td>22-Sep</td>
                            <td>30-Sep</td>
                            <td>4-Nov</td>
                            <td>11-Nov</td>
                        </tr>
                        <tr>
                            <td>30-Nov</td>
                            <td>10-Nov</td>
                            <td>18-Nov</td>
                            <td>12-Dec</td>
                            <td>12-Dec</td>
                        </tr>
                        <tr>
                            <td>11-Jan</td>
                            <td>8-Dec</td>
                            <td>15-Dec</td>
                            <td>3-Feb</td>
                            <td>10-Feb</td>
                        </tr>
                    </table><br>
                    <div class="container">
  <ul class="squares">
    <li class="square yellow"></li>
    <li class="square blue"></li>
    <li class="square green"></li>
  </ul>
  <ul class="labels">
    <li>Sunday test session ( Pencil and Paper - 20 )</li>
    <li>Online test session  - 5</li>
    <li>National test session (Pencil and Paper)</li>
  </ul>
</div>

                    <br>
                    <label for="R175">AQL: R175 / AQL & MAT: R350 / RE-MARK: R250</label><br>
                </div>
                <div class="section">
                
<h2><b>WHICH TESTS
MUST I WRITE?</b>
</h2>
<p>The <b>faculty or department</b> will determine which test/s you must write and 
the <b>deadlines</b> for receiving your results.</p>
<hr>
<h2><b>WHERE</b> CAN I 
WRITE THE TESTS?</h2>
<p>A complete <b>schedule</b> with all the test 
locations is on the <b>NBT website - 
www.nbt.ac.za.</b> If there are no tests 
near to where you live, and the online test is 
not a viable option for you, send an <span style="color:red;"> e-mail to nbt@uct.
ac.za</span> or  <span style="color:red;">phone us on 021 650 3523</span></p>
<hr>
<h2><b>WHEN</b> SHOULD I WRITE?</h2>
<p>
You should write the NBT <b>before</b>
the closing date of your university 
application, in order to meet all 
deadlines and requirements for the 
programme(s) to which you are applying. You must 
<b>write both</b> AQL and MAT tests on the same day.<br><br>
You are allowed to write the NBT for a <b>second time</b>, 
but you will need to <b>confirm</b> with your faculty fi rst that a 
second score will be accepted.
</p>
<hr>
<h2><b>APPLY</b> AS A 
REMOTE WRITER</h2>
<p>A remote writer/student is someone 
who is <b>unable to write at an institution</b> 
that generally facilitates the national 
benchmark test sessions.
A remote writer is a writer who is <b>in another country</b> 
or in an area too remote to be able to make a trip of a 
reasonable distance to the nearest testing centre.
Send an email to <b>nbtremote@uct.ac.za</b>to register 
as a remote writer.</p>
<hr>
<h2><b>TEST</b> RESULTS</h2>
<p>
<ol>
  <li><b>Login</b> to your NBT account 
approximately <b>four weeks after 
writing</b>, to access your results</li>
<li>You will only be able to access them <b>if you 
have paid</b> for any tests for which you were registered</li>
</ol>
</p>
            </div>
        </div>
    </div> <!-- Closing container here -->

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

