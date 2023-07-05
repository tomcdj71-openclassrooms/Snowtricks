let links = document.querySelectorAll('[data-delete]');
links.forEach(link => {
    link.addEventListener('click', handleDeleteClick.bind(link));
});

function handleDeleteClick(e) {
    e.preventDefault();
    confirmAndDelete(this);
}

function confirmAndDelete(item) {
    let confirmationMessage = item.dataset.confirm;
    if (confirm(confirmationMessage)) {
        deleteItem(item);
    };
}

function deleteItem(item) {
    fetch(item.getAttribute('href'), {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ '_csrf': item.dataset.token })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                item.parentElement.remove();
            } else {
                alert(data.error);
            };
        }).catch(error => console.error('Error:', error));
}


window.addEventListener('DOMContentLoaded', setup);

function setup() {
    setupVideos();
    setupDeleteButtons();
}

function setupVideos() {
    const collectionHolder = document.getElementById('videos-container');
    if (!collectionHolder) {
        return;
    }
    let index = collectionHolder.dataset.index;
    document.getElementById('add-video').addEventListener('click', addVideo.bind(null, collectionHolder, index));
}

function setupDeleteButtons() {
    document.querySelectorAll('.delete-video').forEach(button => {
        button.addEventListener('click', handleDeleteVideoClick);
    });
}

function addVideo(collectionHolder, index) {
    let prototype = collectionHolder.dataset.prototype;
    let newForm = prototype.replace(/__name__/g, index);
    let div = createNewVideoDiv(newForm);
    collectionHolder.appendChild(div);
}

function createNewVideoDiv(newForm) {
    let div = document.createElement('div');
    div.innerHTML = newForm;
    div.classList.add('video');
    addDeleteButton(div);
    return div;
}

function addDeleteButton(div) {
    let deleteButton = document.createElement('button');
    deleteButton.textContent = 'Delete';
    deleteButton.type = 'button';
    deleteButton.classList.add('delete-video');
    deleteButton.addEventListener('click', function () {
        div.remove();
    });
    div.appendChild(deleteButton);
}

function handleDeleteVideoClick() {
    this.parentElement.remove();
}
