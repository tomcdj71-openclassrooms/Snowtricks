const scrollButton = document.querySelector("#scroll-button");
if (scrollButton) {
    scrollButton.addEventListener("click", function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
    });
}
