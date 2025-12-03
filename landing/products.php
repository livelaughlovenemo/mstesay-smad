<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Castoro:ital@0;1&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Urbanist:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/products-styles.css">
    <link rel="icon" type="image/png" href="../assets/img/mainlogo.png">
</head>
<body>

<header class="navbar">
    <div class="logo">
        <img src="../assets/img/mainlogo.png" alt="Logo">
        <h2 style="font-family: Pacifico, cursive;">Ms. Tesay Chicken</h2>
    </div>

    <nav>
    <a href="../index.php">Home</a>
    <a href="about.php" >About</a>
    <a href="products.php">Products</a>
    <a href="contact.php">Contact</a>
    <a href="../includes/login.php class="user-icon">
            <svg width="35px" height="35px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.5" stroke="#000000" stroke-width="0.8" d="M12 22.01C17.5228 22.01 22 17.5329 22 12.01C22 6.48716 17.5228 2.01001 12 2.01001C6.47715 2.01001 2 6.48716 2 12.01C2 17.5329 6.47715 22.01 12 22.01Z" fill="none"/>
                <path d="M12 6.93994C9.93 6.93994 8.25 8.61994 8.25 10.6899C8.25 12.7199 9.84 14.3699 11.95 14.4299C11.98 14.4299 12.02 14.4299 12.04 14.4299C12.06 14.4299 12.09 14.4299 12.11 14.4299C12.12 14.4299 12.13 14.4299 12.13 14.4299C14.15 14.3599 15.74 12.7199 15.75 10.6899C15.75 8.61994 14.07 6.93994 12 6.93994Z" fill="#333"/>
                <path d="M18.7807 19.36C17.0007 21 14.6207 22.01 12.0007 22.01C9.3807 22.01 7.0007 21 5.2207 19.36C5.4607 18.45 6.1107 17.62 7.0607 16.98C9.7907 15.16 14.2307 15.16 16.9407 16.98C17.9007 17.62 18.5407 18.45 18.7807 19.36Z" fill="#333"/>
            </svg>
        </a>
    </nav>
</header>

<section class="product-section">

    <h1 class="page-title">Ms. Tesay Chicken Products</h1>

    <div class="category-labels">
        <span data-category="chicken" class="active">Dressed Chicken Products</span>
        <span data-category="frozen">Frozen Products</span>
    </div>

    <div class="product-grid">
    <!-- DRESSED CHICKEN PRODUCTS -->
    <div class="product-card" data-category="chicken">
        <img src="../assets/img/whole-chicken.png" alt="">
        <h3>Whole Chicken</h3>
        <p>Weight: 1.2 kg – 1.4 kg</p>
        <p class="price">₱170.00</p>
    </div>

    <div class="product-card" data-category="chicken">
        <img src="../assets/img/backbones.png" alt="">
        <h3>Chicken BackBones</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱180.00</p>
    </div>

    <div class="product-card" data-category="chicken">
        <img src="../assets/img/chicneck.png" alt="">
        <h3>Chicken Neck</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱190.00</p>
    </div>

        <div class="product-card" data-category="chicken">
        <img src="../assets/img/fillet.png" alt="">
        <h3>Chicken Fillet</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱170.00</p>
    </div>

    <div class="product-card" data-category="chicken">
        <img src="../assets/img/liver.png" alt="">
        <h3>Chicken Liver</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱180.00</p>
    </div>

    <div class="product-card" data-category="chicken">
        <img src="../assets/img/feet.jpg" alt="">
        <h3>Chicken Feet</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱190.00</p>
    </div>

        <div class="product-card" data-category="chicken">
        <img src="../assets/img/intestine.jpeg" alt="">
        <h3>Chicken Intestine</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱180.00</p>
    </div>

    <div class="product-card" data-category="chicken">
        <img src="../assets/img/gizzard.jpg" alt="">
        <h3>Chicken Gizzard Fats</h3>
        <p>Weight: 1 kg</p>
        <p class="price">₱190.00</p>
    </div>

    <!-- FROZEN PRODUCTS -->
        <div class="product-card" data-category="frozen">
            <img src="../assets/img/CHK.PNG" alt="">
            <h3>Champion Hotdog Jumbo</h3>
            <p>Quantity: 1 kg</p>
            <p class="price">₱130.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/CHK.PNG" alt="">
            <h3>Champion Hotdog Jumbo</h3>
            <p>Quantity: 250 g</p>
            <p class="price">₱185.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/BHK.PNG" alt="">
            <h3>Booster Hotdog Jumbo</h3>
            <p>Quantity: 1 kg</p>
            <p class="price">₱95.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/BHJ.PNG" alt="">
            <h3>Booster Hotdog Jumbo </h3>
            <p>Quantity: 240 g</p>
            <p class="price">₱130.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/WCH.PNG" alt="">
            <h3>Winner Cooked Ham</h3>
            <p>Quantity: 250 g</p>
            <p class="price">₱185.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/ELCB.PNG" alt="">
            <h3>EL RANCHO Corned Beef</h3>
            <p>Quantity: 200 g</p>
            <p class="price">₱95.00</p>
        </div>


        <div class="product-card" data-category="frozen">
            <img src="../assets/img/VPT.PNG" alt="">
            <h3>Virginia Pork Tocino</h3>
            <p>Quantity: 200 g</p>
            <p class="price">₱185.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/CCL.jpg" alt="">
            <h3>Champion Chicken Loaf</h3>
            <p>Quantity: 200 g</p>
            <p class="price">₱95.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/VCH.jpg" alt="">
            <h3>Virginia Chicken Hotdog</h3>
            <p>Quantity: 240 g</p>
            <p class="price">₱130.00</p>
        </div>

        <div class="product-card" data-category="frozen">
            <img src="../assets/img/KLONG.jpg" alt="">
            <h3>Kings Longganiza</h3>
            <p>Quantity: 1 kg </p>
            <p class="price">₱185.00</p>
        </div>
    </div>

</section>

<script>
    const categories = document.querySelectorAll('.category-labels span');
    const products = document.querySelectorAll('.product-card');

    categories.forEach(category => {
        category.addEventListener('click', () => {

            categories.forEach(c => c.classList.remove('active'));
            category.classList.add('active');

            const selected = category.getAttribute('data-category');

            products.forEach(product => {
                if (product.getAttribute('data-category') === selected) {
                    product.classList.remove('hidden');
                } else {
                    product.classList.add('hidden');
                }
            });
        });
    });
</script>
</body>
</html>

<?php include "../includes/footer.php"; ?>