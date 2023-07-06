const dropdownClickHandler = (dropdownMenu) => {
    dropdownMenu.classList.toggle('hidden');
};

const getDOMElements = (dropdownButtonId, dropdownMenuId) => {
    const dropdownButton = document.getElementById(dropdownButtonId);
    const dropdownMenu = document.getElementById(dropdownMenuId);

    return { dropdownButton, dropdownMenu };
};

const toggleDropdown = (dropdownButtonId, dropdownMenuId) => {
    const { dropdownButton, dropdownMenu } = getDOMElements(dropdownButtonId, dropdownMenuId);

    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', () => dropdownClickHandler(dropdownMenu));
    }
};

['dropdownButton', 'dropdownButtonMobile'].forEach((dropdownButtonId) => {
    const dropdownMenuId = `${dropdownButtonId.replace('Button', 'Menu')}`;
    toggleDropdown(dropdownButtonId, dropdownMenuId);
});
