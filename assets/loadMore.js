const fetchData = (url, containerId, pageCounter, button, limit, totalItems) => {
    fetch(url)
        .then(response => response.text())
        .then(data => updateContainer(containerId, data, pageCounter, button, limit, totalItems))
        .catch(error => console.error('Error:', error));
};

function updateContainer(containerId, data, pageCounter, button, limit, totalItems) {
    const container = document.getElementById(containerId);
    container.innerHTML += data;
    incrementPageCounter(pageCounter);
    hideButtonIfNoMorePages(pageCounter, button, limit, totalItems);
}

function incrementPageCounter(pageCounter) {
    pageCounter.page += 1;
}

function hideButtonIfNoMorePages(pageCounter, button, limit, totalItems) {
    if (pageCounter.page * limit > totalItems) {
        button.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const loadMoreTricksButton = document.getElementById('load-more-tricks');
    const loadMoreCommentsButton = document.getElementById('load-more-comments');
    const dataLimitElement = document.querySelector('[data-limit]');

    if (loadMoreTricksButton && dataLimitElement) {
        initTricks(loadMoreTricksButton, dataLimitElement);
    }

    if (loadMoreCommentsButton && dataLimitElement) {
        initComments(loadMoreCommentsButton, dataLimitElement);
    }
});

function initTricks(loadMoreTricksButton, dataLimitElement) {
    const trickPage = { page: 2 };
    const limit = parseInt(dataLimitElement.getAttribute('data-limit'), 10);
    const totalTricks = parseInt(document.querySelector('#tricks-container').getAttribute('data-total-tricks'), 10);

    loadMoreTricksButton.addEventListener('click', function () {
        fetchData(`/load_more/tricks/${trickPage.page}`, 'tricks-container', trickPage, loadMoreTricksButton, limit, totalTricks);
    });
}

function initComments(loadMoreCommentsButton, dataLimitElement) {
    const commentPage = { page: 2 };
    const trick = document.querySelector('[data-trick]').getAttribute('data-trick');
    const limit = parseInt(dataLimitElement.getAttribute('data-limit'), 10);
    const totalComments = parseInt(document.querySelector('#comments-container').getAttribute('data-total-comments'), 10);

    loadMoreCommentsButton.addEventListener('click', function () {
        fetchData(`/load_more/comments/${trick}/${commentPage.page}`, 'comments-container', commentPage, loadMoreCommentsButton, limit, totalComments);
    });
}
