<!DOCTYPE html>
<html lang="en">
<head>
<meta content="width=device-width, initial-scale=1.0" name="viewport">
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
    <title>Portfolio</title>
    <style>
        /* Add your custom CSS styles here */
        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
        }
        .gallery img {
            width: calc(25% - 10px); /* 25% width with 10px margin between images */
            margin-bottom: 10px;
        }
        .filter-buttons {
            margin-bottom: 20px;
        }
        .filter-buttons button {
            margin-right: 10px;
            cursor: pointer;
            padding: 5px 10px;
            border: none;
            background-color: #21610c;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .filter-buttons button:hover {
            background-color: #0056b3;
        }
        .category3 a {
    display: block; /* Makes the links block-level elements */
    margin-bottom: 10px; /* Adds spacing between each link */
    color: #21610c; /* Sets the text color to blue */
    text-decoration: none; /* Removes underline */
    font-weight: bold; /* Makes the text bold */
}

.category3 a:hover {
    color: #21610c; /* Changes text color on hover */
}

    </style>
</head>
<body>

 
<div class="container">
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
                            <a href="about" class="nav-item nav-link active">About</a>
                            <a href="service" class="nav-item nav-link">Services</a>
                            <div class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" id="learnerPortalDropdown" role="button" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false" onclick="toggleSubheadings()">Learner Portal</a>
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
          <div class="container-fluid page-header py-5" style="background-image: url('img/DSC_6719.jpg'); background-size: 50%;">
    <div class="container text-center py-5">
        <h1 class="display-2 text-BLACK mb-4 animated slideInDown">GALLERY</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
            </ol>
        </nav>
    </div>
</div>

<br><br>
        <!-- Filter buttons -->
        <div class="filter-buttons">
            <button onclick="filter('all')">All</button>
            <button onclick="filter('category1')">MDI CENTER</button>
            <button onclick="filter('category2')">MDI CAREER DAY</button>
            <button onclick="filter('category3')">YOUTUBE LINKS</button>
            <button onclick="filter('category4')">MDI AT WORK</button>
            <!-- Add more buttons for additional categories -->
        </div>

        <!-- Image gallery -->
        <div class="gallery">
    <!-- Images grouped by categories -->
    <div class="category1">
        <img src="img/mdi1.jpg" alt="Category 1 Image 1">
        <img src="img/mdi2.jpg" alt="Category 1 Image 2">
        <img src="img/mdi3.jpg" alt="Category 1 Image 3">
        <img src="img/mdi4.jpg" alt="Category 1 Image 4">
        <img src="img/mdi5.jpg" alt="Category 1 Image 5">
        <img src="img/mdi6.jpg" alt="Category 1 Image 6">
        <img src="img/mdi7.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_6928.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_6933.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_6939.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_7005.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_7023.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_7044.jpg" alt="Category 1 Image 7">
        <img src="img/DSC_7257.jpg" alt="Category 1 Image 7">
        <!-- Add more images for Category 1 -->
    </div>
    <div class="category2">
        <img src="img/mdi8.jpg" alt="Category 2 Image 1">
        <img src="img/mdi9.jpg" alt="Category 2 Image 2">
        <img src="img/mdi10.jpg" alt="Category 2 Image 2">
        <img src="img/mdi11.jpg" alt="Category 2 Image 2">
        <img src="img/mdi12.jpg" alt="Category 2 Image 2">
        <img src="img/mdi13.jpg" alt="Category 2 Image 2">
        <img src="img/mdi14.jpg" alt="Category 2 Image 2">
        <img src="img/DSC_7291.jpg" alt="Category 2 Image 2">
        <!-- Add more images for Category 2 -->
    </div>
    <div class="category3">
    
        <iframe width="130" height="130" src="https://www.youtube.com/embed/XaHq6Ao0Ew0" frameborder="0" allowfullscreen></iframe>
    <!-- Add more video links for Category 3 -->
</div>
<div class="category4">
        <img src="img/IMG_7905.jpg" alt="Category 2 Image 1">
        <img src="img/IMG_7906.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7912.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7915.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7919.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7965.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7967.jpg" alt="Category 2 Image 1">
        <img src="img/IMG_7972.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7975.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7978.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_7979.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_8004.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_8026.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_8060.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_8109.jpg" alt="Category 2 Image 2">
        <img src="img/IMG_8131.jpg" alt="Category 2 Image 2">
        <!-- Add more images for Category 2 -->
    </div>

    <!-- Add more divs for additional categories -->
</div>

<!-- JavaScript for filtering images -->
<script>
    // Function to handle image click event for zooming
    function zoomImage(event) {
        // Create a modal for displaying the zoomed image
        var modal = document.createElement('div');
        modal.classList.add('modal');
        
        // Create an image element for the zoomed image
        var img = document.createElement('img');
        img.src = event.target.src;
        img.alt = event.target.alt;

        // Append the image to the modal
        modal.appendChild(img);

        // Add event listener to close the modal when clicked outside the image
        modal.addEventListener('click', function() {
            modal.remove();
        });

        // Append the modal to the body
        document.body.appendChild(modal);
    }

    // Add click event listener to all images
    document.querySelectorAll('.gallery img').forEach(img => {
        img.addEventListener('click', zoomImage);
    });

    // Function to handle image filtering
    function filter(category) {
        // Hide all images
        document.querySelectorAll('.gallery > div').forEach(categoryDiv => {
            categoryDiv.style.display = 'none';
        });

        // Show images of the selected category
        document.querySelectorAll('.' + category).forEach(image => {
            image.style.display = 'block';
        });

        // Show all images if "All" category is selected
        if (category === 'all') {
            document.querySelectorAll('.gallery > div').forEach(categoryDiv => {
                categoryDiv.style.display = 'block';
            });
        }
    }
</script>

    <?php include 'pwa-footer.php'; ?>
</body>
</html>

