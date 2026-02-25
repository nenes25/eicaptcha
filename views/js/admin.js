document.addEventListener('DOMContentLoaded', function () {
    //Force click to display debug tab when parameter is defined in url
    //This file is only loaded in this case
    var advancedTab = document.querySelector('a[href="#advanced"]');
    if (advancedTab) {
        advancedTab.click();
    }
});
