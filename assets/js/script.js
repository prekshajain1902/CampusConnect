document.addEventListener("DOMContentLoaded", function () {
    let images = document.querySelectorAll(".slideshow img");
    let index = 0;

    function showNextImage() {
        images.forEach((img, i) => img.style.display = i === index ? "block" : "none");
        index = (index + 1) % images.length;
    }

    setInterval(showNextImage, 3000);
});


let slideIndex = 0;
const slides = document.getElementsByClassName("slide");
const dots = document.getElementsByClassName("dot");

// Function to show slides
function showSlides() {
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    slideIndex++;
    if (slideIndex > slides.length) { slideIndex = 1; }

    slides[slideIndex - 1].style.display = "block";

    updateDots();
}

// Function to update active dot
function updateDots() {
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    dots[slideIndex - 1].classList.add("active");
}

// Function to change slides manually
function currentSlide(n) {
    slideIndex = n;
    showSlides();
}

// Auto slide change without delay
setInterval(showSlides, 1000); // Fast interval for continuous effect



function joinClub(clubId) {
    let currentUrl = window.location.href;
    window.location.href = `views/auth/login.php?redirect=${encodeURIComponent(currentUrl)}&club_id=${clubId}`;
}

function registerEvent(eventId) {
    let currentUrl = window.location.href;
    window.location.href = `views/auth/login.php?redirect=${encodeURIComponent(currentUrl)}&event_id=${eventId}`;
}
