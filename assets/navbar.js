function toggleDropdown(dropdownButtonId, dropdownMenuId) {
    document.getElementById(dropdownButtonId).addEventListener('click', function () {
        var dropdownMenu = document.getElementById(dropdownMenuId);
        if (dropdownMenu.classList.contains('hidden')) {
            dropdownMenu.classList.remove('hidden');
        } else {
            dropdownMenu.classList.add('hidden');
        }
    });
}

toggleDropdown('dropdownButton', 'dropdownMenu');
toggleDropdown('dropdownButtonMobile', 'dropdownMenuMobile');
