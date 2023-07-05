document.addEventListener('DOMContentLoaded', () => {
    const limit = parseInt(document.querySelector('[data-limit]').getAttribute('data-limit'), 10);
    setupButton('load-more-tricks', 'tricks-container', limit, '/load_more/tricks');
    setupButton('load-more-comments', 'comments-container', limit, '/load_more/comments');
});

function setupButton(buttonId, containerId, limit, urlPattern) {
    const button = document.getElementById(buttonId);
    const container = document.getElementById(containerId);
    if (!button || !container) return;
    let page = 2;
    const totalItems = parseInt(container.getAttribute(`data-total-${containerId.split('-')[0]}`), 10);
    button.addEventListener('click', () => {
        fetch(`${urlPattern}/${page}`)
            .then(response => response.text())
            .then(data => {
                container.innerHTML += data;
                if (++page * limit > totalItems) {
                    button.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    });
}
