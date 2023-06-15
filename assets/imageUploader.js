/**
 * This is our video uploader script.
 * It allows us to upload multiple videos and delete them directly from the form.
 * 
 * @param {link} link 
 * @returns {void}
 */
async function handleDelete(link) {
    try {
        let response = await fetch(link.getAttribute("href"), {
            method: "DELETE",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ "_token": link.dataset.token })
        });
        let data = await response.json();
        if (data.success) {
            link.parentElement.remove();
        } else {
            alert(data.error);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}


window.addEventListener('DOMContentLoaded', (event) => {
    // Get the container that holds the collection of videos
    let collectionHolder = document.getElementById('videos-container');

    // Define an index that will keep track of the number of video inputs
    let index = collectionHolder.dataset.index;

    // Add a click event listener to the Add Video button
    document.getElementById('add-video').addEventListener('click', function () {
        // Get the data-prototype explained earlier
        let prototype = collectionHolder.dataset.prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on the current collection's length
        let newForm = prototype.replace(/__name__/g, index);

        // Increase the index with one for the next video
        index++;

        // Display the form in the page in an li, before the "Add a video" link
        let div = document.createElement('div');
        div.innerHTML = newForm;
        div.classList.add('video');

        // Add a delete button for this video
        let deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.type = 'button';
        deleteButton.classList.add('delete-video');
        deleteButton.addEventListener('click', function () {
            div.remove();
        });

        div.appendChild(deleteButton);
        collectionHolder.appendChild(div);
    });

    // Add a click event listener to all Delete Video buttons
    document.querySelectorAll('.delete-video').forEach(function (button) {
        button.addEventListener('click', function () {
            button.parentElement.remove();
        });
    });

    // Add a click event listener to all image delete links
    document.querySelectorAll('[data-delete]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            handleDelete(link);
        });
    });
});


