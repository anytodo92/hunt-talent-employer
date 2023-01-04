window.addEventListener('load', function load() {
    const loader = document.getElementById('loader');
    setTimeout(function () {
        loader && loader.classList.add('fadeOut');
    }, 300);
});
