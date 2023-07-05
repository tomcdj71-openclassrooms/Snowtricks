const dropdownClickHandler = (dropdownMenu) => {
    dropdownMenu.classList.toggle('hidden');
};

const toggleDropdown = (dropdownButtonId, dropdownMenuId) => {
    const dropdownButton = document.getElementById(dropdownButtonId);
    const dropdownMenu = document.getElementById(dropdownMenuId);

    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', () => dropdownClickHandler(dropdownMenu));
    }
};

['dropdownButton', 'dropdownButtonMobile'].forEach((dropdownButtonId) => {
    const dropdownMenuId = `${dropdownButtonId.replace('Button', 'Menu')}`;
    toggleDropdown(dropdownButtonId, dropdownMenuId);
});
