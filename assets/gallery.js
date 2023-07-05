(function () {
    const images = Array.from(document.querySelectorAll('[data-image]'));
    let currentImageIndex = 0;

    function updateModalImage(src) {
        const modalImage = document.querySelector('#modal-image');
        modalImage.setAttribute('src', src);
    }

    function handleImageClick(index) {
        return function () {
            const imageSrc = this.getAttribute('data-image');
            updateModalImage(imageSrc);
            currentImageIndex = index;
        }
    }

    function handleNavClick(offset) {
        return function () {
            currentImageIndex = (currentImageIndex + offset + images.length) % images.length;
            updateModalImage(images[currentImageIndex].getAttribute('data-image'));
        }
    }

    function handleAccordionClick(button, accordionBody) {
        const isOpen = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', !isOpen);

        if (isOpen) {
            accordionBody.classList.add('hidden');
        } else {
            accordionBody.classList.remove('hidden');
        }
    }

    function initGallery() {
        images.forEach((image, index) => {
            image.addEventListener('click', handleImageClick(index));
        });

        document.getElementById('prev-image').addEventListener('click', handleNavClick(-1));
        document.getElementById('next-image').addEventListener('click', handleNavClick(1));
    }

    function initAccordion() {
        document.querySelectorAll("[data-accordion-target]").forEach(button => {
            const accordionId = button.getAttribute('data-accordion-target');
            const accordionBody = document.querySelector(accordionId);

            button.addEventListener('click', () => handleAccordionClick(button, accordionBody));
        });
    }

    initGallery();
    initAccordion();
})();
