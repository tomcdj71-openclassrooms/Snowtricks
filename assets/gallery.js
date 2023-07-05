document.addEventListener('DOMContentLoaded', () => {
    setupGallery();
    setupAccordion();
});

function setupGallery() {
    const images = Array.from(document.querySelectorAll('[data-image]'));
    let currentImageIndex = 0;
    const modalImage = document.querySelector('#modal-image');
    images.forEach((image, index) => {
        image.addEventListener('click', () => {
            const imageSrc = image.getAttribute('data-image');
            modalImage.setAttribute('src', imageSrc);
            currentImageIndex = index;
        });
    });
    const handleNavClick = (offset) => {
        currentImageIndex = (currentImageIndex + offset + images.length) % images.length;
        modalImage.setAttribute('src', images[currentImageIndex].getAttribute('data-image'));
    };
    document.getElementById('prev-image').addEventListener('click', () => handleNavClick(-1));
    document.getElementById('next-image').addEventListener('click', () => handleNavClick(1));
}

function setupAccordion() {
    document.querySelectorAll('[data-accordion-target]').forEach(button => {
        const accordionBody = document.querySelector(button.getAttribute('data-accordion-target'));
        button.addEventListener('click', () => {
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isOpen);
            accordionBody.classList.toggle('hidden', isOpen);
        });
    });
}
