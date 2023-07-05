const fetchData = (url, containerId, pageCounter, button, limit, totalItems) => {
    fetch(url)
        .then(response => response.text())
        .then(data => appendFetchedDataToContainer(containerId, data, pageCounter, button, limit, totalItems))
        .catch(error => console.error('Error:', error));
};

function appendFetchedDataToContainer(containerId, data, pageCounter, button, limit, totalItems) {
    const container = document.getElementById(containerId);
    container.innerHTML += data;
    handleContainerUpdate(pageCounter, button, limit, totalItems);
}

function handleContainerUpdate(pageCounter, button, limit, totalItems) {
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

document.addEventListener('DOMContentLoaded', setup);

function setup() {
    const loadMoreTricksButton = document.getElementById('load-more-tricks');
    const loadMoreCommentsButton = document.getElementById('load-more-comments');
    const dataLimitElement = document.querySelector('[data-limit]');

    if (loadMoreTricksButton && dataLimitElement) {
        setupTricks(loadMoreTricksButton, dataLimitElement);
    }

    if (loadMoreCommentsButton && dataLimitElement) {
        setupComments(loadMoreCommentsButton, dataLimitElement);
    }
}

function setupTricks(loadMoreTricksButton, dataLimitElement) {
    const trickPage = { page: 2 };
    const limit = getLimit(dataLimitElement);
    const totalTricks = getTotalTricks();

    addClickEventToTricksButton(loadMoreTricksButton, trickPage, limit, totalTricks);
}

function getLimit(dataLimitElement) {
    return parseInt(dataLimitElement.getAttribute('data-limit'), 10);
}

function getTotalTricks() {
    return parseInt(document.querySelector('#tricks-container').getAttribute('data-total-tricks'), 10);
}

function addClickEventToTricksButton(loadMoreTricksButton, trickPage, limit, totalTricks) {
    loadMoreTricksButton.addEventListener('click', function () {
        fetchData(`/load_more/tricks/${trickPage.page}`, 'tricks-container', trickPage, loadMoreTricksButton, limit, totalTricks);
    });
}

function setupComments(loadMoreCommentsButton, dataLimitElement) {
    const commentPage = { page: 2 };
    const trick = getTrick();
    const limit = getLimit(dataLimitElement);
    const totalComments = getTotalComments();

    addClickEventToCommentsButton(loadMoreCommentsButton, trick, commentPage, limit, totalComments);
}

function getTrick() {
    return document.querySelector('[data-trick]').getAttribute('data-trick');
}

function getTotalComments() {
    return parseInt(document.querySelector('#comments-container').getAttribute('data-total-comments'), 10);
}

function addClickEventToCommentsButton(loadMoreCommentsButton, trick, commentPage, limit, totalComments) {
    loadMoreCommentsButton.addEventListener('click', function () {
        fetchData(`/load_more/comments/${trick}/${commentPage.page}`, 'comments-container', commentPage, loadMoreCommentsButton, limit, totalComments);
    });
}
