window.addEventListener('DOMContentLoaded', () => {
    setupDeleteActions();
    setupVideos();
});

function setupDeleteActions() {
    document.querySelectorAll('[data-delete]').forEach(item => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            const confirmationMessage = item.dataset.confirm;
            if (!confirm(confirmationMessage)) return;
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
                    }
                })
                .catch(console.error);
        });
    });
}

function setupVideos() {
    const collectionHolder = document.getElementById('videos-container');
    if (!collectionHolder) {
        return;
    }
    let index = collectionHolder.dataset.index;
    document.getElementById('add-video').addEventListener('click', () => {
        let prototype = collectionHolder.dataset.prototype;
        let newForm = prototype.replace(/__name__/g, index);
        let div = document.createElement('div');
        div.innerHTML = newForm;
        div.classList.add('video');
        let deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.type = 'button';
        deleteButton.classList.add('delete-video');
        deleteButton.addEventListener('click', () => {
            div.remove();
        });
        div.appendChild(deleteButton);
        collectionHolder.appendChild(div);
        index++;
    });
}
