$(document).ready(function () {
    // Reload the page with new provider as GET param so the correct config fields are shown
    $('#CAPTCHA_PROVIDER').on('change', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('CAPTCHA_PROVIDER', $(this).val());
        // Remove any previous provider param to avoid conflicts
        window.location.href = url.toString();
    });

    // Open debug tab when parameter is defined in url
    if (window.location.search.indexOf('display_debug=1') !== -1) {
        $('a[href="#advanced"]').click();
    }
});
