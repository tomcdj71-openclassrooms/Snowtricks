document.addEventListener('DOMContentLoaded', function () {
    const loadMoreTricksButton = document.getElementById('load-more-tricks');
    const loadMoreCommentsButton = document.getElementById('load-more-comments');

    if (loadMoreTricksButton) {
        let trickPage = 2;
        loadMoreTricksButton.addEventListener('click', function () {
            fetch(`/load_more/tricks/${trickPage}`)
                .then(response => response.text())
                .then(data => {
                    // Append the new tricks to the existing list
                    document.getElementById('tricks-container').innerHTML += data;

                    // Increment page for next load
                    trickPage++;
                })
                .catch(error => console.error('Error:', error));
        });
    }

    if (loadMoreCommentsButton) {
        let commentPage = 2;
        let trick = document.querySelector('[data-trick]').getAttribute('data-trick');
        loadMoreCommentsButton.addEventListener('click', function () {
            fetch(`/load_more/comments/${trick}/${commentPage}`)
                .then(response => response.text())
                .then(data => {
                    // Append the new comments to the existing list
                    document.getElementById('comments-container').innerHTML += data;

                    // Increment page for next load
                    commentPage++;
                })
                .catch(error => console.error('Error:', error));
        });
    }
});
