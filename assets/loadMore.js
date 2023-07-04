document.addEventListener('DOMContentLoaded', function () {
    const loadMoreTricksButton = document.getElementById('load-more-tricks');
    const loadMoreCommentsButton = document.getElementById('load-more-comments');

    const fetchData = (url, containerId, pageCounter, button, limit, totalItems) => {
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById(containerId).innerHTML += data;
                pageCounter.page++;
                if (pageCounter.page * limit > totalItems) {
                    button.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    };

    if (loadMoreTricksButton) {
        let trickPage = { page: 2 };
        let limit = parseInt(document.querySelector('[data-limit]').getAttribute('data-limit'));
        let totalTricks = parseInt(document.querySelector('#tricks-container').getAttribute('data-total-tricks'));
        loadMoreTricksButton.addEventListener('click', function () {
            fetchData(`/load_more/tricks/${trickPage.page}`, 'tricks-container', trickPage, loadMoreTricksButton, limit, totalTricks);
        });
    }

    if (loadMoreCommentsButton) {
        let commentPage = { page: 2 };
        let trick = document.querySelector('[data-trick]').getAttribute('data-trick');
        let limit = parseInt(document.querySelector('[data-limit]').getAttribute('data-limit'));
        let totalComments = parseInt(document.querySelector('#comments-container').getAttribute('data-total-comments'));
        loadMoreCommentsButton.addEventListener('click', function () {
            fetchData(`/load_more/comments/${trick}/${commentPage.page}`, 'comments-container', commentPage, loadMoreCommentsButton, limit, totalComments);
        });
    }
});
