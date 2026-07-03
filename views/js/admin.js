document.addEventListener('DOMContentLoaded', function () {
    //Force click to display debug tab when parameter is defined in url
    //This file is only loaded in this case
    var advancedLink = document.querySelector('a[href="#advanced"]');
    if (advancedLink) {
        advancedLink.click();
    }
});