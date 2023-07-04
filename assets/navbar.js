const toggleDropdown = (dropdownButtonId, dropdownMenuId) => {
    const dropdownButton = document.getElementById(dropdownButtonId);
    const dropdownMenu = document.getElementById(dropdownMenuId);

    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });
    }
};

['dropdownButton', 'dropdownButtonMobile'].forEach((dropdownButtonId) => {
    const dropdownMenuId = `${dropdownButtonId.replace('Button', 'Menu')}`;
    toggleDropdown(dropdownButtonId, dropdownMenuId);
});
