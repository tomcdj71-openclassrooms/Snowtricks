document.addEventListener('DOMContentLoaded', function () {
    const loadMoreTricksButton = document.getElementById('load-more-tricks');
    const loadMoreCommentsButton = document.getElementById('load-more-comments');

    const fetchData = (url, containerId, pageCounter) => {
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById(containerId).innerHTML += data;
                pageCounter++;
            })
            .catch(error => console.error('Error:', error));
    };

    if (loadMoreTricksButton) {
        let trickPage = 2;
        loadMoreTricksButton.addEventListener('click', function () {
            fetchData(`/load_more/tricks/${trickPage}`, 'tricks-container', trickPage);
        });
    }

    if (loadMoreCommentsButton) {
        let commentPage = 2;
        let trick = document.querySelector('[data-trick]').getAttribute('data-trick');
        loadMoreCommentsButton.addEventListener('click', function () {
            fetchData(`/load_more/comments/${trick}/${commentPage}`, 'comments-container', commentPage);
        });
    }
});
