// loop through all data-image. If an image is clicked, display it in the modal (#modal-image) and nothing more

let images = document.querySelectorAll('[data-image]');
let modalImage = document.querySelector('#modal-image');

images.forEach(image => {
    image.addEventListener('click', function () {
        let imageSrc = this.getAttribute('data-image');
        modalImage.setAttribute('src', imageSrc);
    });
});
