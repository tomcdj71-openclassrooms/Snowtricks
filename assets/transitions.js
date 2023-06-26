document.querySelector("#scroll-button").addEventListener("click", function (e) {
    e.preventDefault();
    document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
});
